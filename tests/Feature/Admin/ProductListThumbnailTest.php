<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class ProductListThumbnailTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Test Category', 'slug' => 'test-category-'.uniqid(), 'status' => 'active']);
    }

    private function product(Category $category, string $name): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product, array $overrides = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'variant_name' => 'Variant',
            'enable_tiered_pricing' => false,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_product_with_default_variant_primary_image_shows_that_image(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $product = $this->product($category, 'Has Primary');

        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/not-primary.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);
        $variant->images()->create(['image' => 'variants/primary.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $response = $this->actingAs($admin)->get('/admin/catalog/products');

        $response->assertOk();
        $response->assertSee(Storage::url('variants/primary.jpg'), false);
        $response->assertDontSee(Storage::url('variants/not-primary.jpg'), false);
    }

    public function test_product_with_default_variant_image_but_no_primary_shows_first_by_sort_order(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $product = $this->product($category, 'No Primary');

        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/second.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);
        $variant->images()->create(['image' => 'variants/first.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 1]);

        $response = $this->actingAs($admin)->get('/admin/catalog/products');

        $response->assertOk();
        $response->assertSee(Storage::url('variants/first.jpg'), false);
    }

    public function test_product_falls_back_to_another_active_variant_when_default_has_no_image(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $product = $this->product($category, 'Fallback Variant');

        $this->variant($product, ['variant_name' => 'Default, no image', 'is_default' => true, 'sort_order' => 0]);
        $fallback = $this->variant($product, ['variant_name' => 'Has the image', 'is_default' => false, 'sort_order' => 1]);
        $fallback->images()->create(['image' => 'variants/fallback.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $response = $this->actingAs($admin)->get('/admin/catalog/products');

        $response->assertOk();
        $response->assertSee(Storage::url('variants/fallback.jpg'), false);
    }

    public function test_product_with_only_video_media_shows_placeholder(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $product = $this->product($category, 'Video Only');

        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/clip.mp4', 'media_type' => 'video', 'is_primary' => false, 'sort_order' => 1]);

        $response = $this->actingAs($admin)->get('/admin/catalog/products');

        $response->assertOk();
        $response->assertSee('https://placehold.co/50x50', false);
        $response->assertDontSee(Storage::url('variants/clip.mp4'), false);
    }

    public function test_product_with_no_media_shows_placeholder(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $product = $this->product($category, 'No Media At All');

        $this->variant($product, ['is_default' => true]);

        $response = $this->actingAs($admin)->get('/admin/catalog/products');

        $response->assertOk();
        $response->assertSee('https://placehold.co/50x50', false);
    }

    public function test_thumbnail_resolution_does_not_introduce_n_plus_one_queries(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();

        foreach (range(1, 5) as $i) {
            $product = $this->product($category, "Bulk Product {$i}");
            $variant = $this->variant($product, ['is_default' => true]);
            $variant->images()->create(['image' => "variants/bulk{$i}.jpg", 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        }

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/catalog/products')->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // A handful of fixed queries (products, category, counts/aggregates,
        // variants, images, auth/session) regardless of product count — not
        // one extra query per product.
        $this->assertLessThan(20, $queryCount, "Expected a small fixed query count, got {$queryCount} — possible N+1.");
    }

    public function test_search_and_pagination_still_work(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $category = $this->category();
        $this->product($category, 'Findable Product Xyz');
        $this->product($category, 'Other Product');

        $response = $this->actingAs($admin)->get('/admin/catalog/products?search=Findable');

        $response->assertOk();
        $response->assertSee('Findable Product Xyz');
        $response->assertDontSee('Other Product');
    }
}
