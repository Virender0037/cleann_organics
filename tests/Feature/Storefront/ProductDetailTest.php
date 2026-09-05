<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Fruits',
            'slug' => 'fruits-'.uniqid(),
            'status' => 'active',
        ], $overrides));
    }

    private function product(Category $category, string $name, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ], $overrides));
    }

    private function variant(Product $product, array $overrides = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'variant_name' => 'Variant',
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'stock_quantity' => 10,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_active_product_resolves_by_slug(): void
    {
        $product = $this->product($this->category(), 'Ripe Mango');
        $this->variant($product, ['is_default' => true]);

        $this->get('/products/'.$product->slug)->assertOk()->assertSee('Ripe Mango');
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = $this->product($this->category(), 'Hidden Mango', ['status' => 'inactive']);
        $this->variant($product, ['is_default' => true]);

        $this->get('/products/'.$product->slug)->assertNotFound();
    }

    public function test_product_in_inactive_category_returns_404(): void
    {
        $category = $this->category(['status' => 'inactive']);
        $product = $this->product($category, 'Orphan Mango');
        $this->variant($product, ['is_default' => true]);

        $this->get('/products/'.$product->slug)->assertNotFound();
    }

    public function test_unknown_product_slug_returns_404(): void
    {
        $this->get('/products/does-not-exist')->assertNotFound();
    }

    public function test_default_variant_is_selected_initially(): void
    {
        $product = $this->product($this->category(), 'Two Variant Product');
        $this->variant($product, ['variant_name' => 'Small', 'is_default' => false, 'sort_order' => 0]);
        $default = $this->variant($product, ['variant_name' => 'Large', 'is_default' => true, 'sort_order' => 1]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('data-variant-id="'.$default->id.'"', false);
        $response->assertSee('class="tag-btn variant-option active"', false);
    }

    public function test_fallback_to_first_active_variant_by_sort_order_when_no_default(): void
    {
        $product = $this->product($this->category(), 'No Default Product');
        $first = $this->variant($product, ['variant_name' => 'First', 'is_default' => false, 'sort_order' => 0]);
        $this->variant($product, ['variant_name' => 'Second', 'is_default' => false, 'sort_order' => 1]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk()->assertSee('₹100.00');
        // The first-by-sort_order variant's SKU should be the one shown.
        $response->assertSee($first->sku ?? 'Variant', false);
    }

    public function test_product_with_no_active_variant_is_not_purchasable(): void
    {
        $product = $this->product($this->category(), 'No Active Variant Product');
        $this->variant($product, ['is_default' => true, 'status' => 'inactive']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Price unavailable');
        $this->assertAddToCartIsDisabled($response);
    }

    public function test_variant_media_primary_image_takes_priority_in_gallery(): void
    {
        Storage::fake('public');
        $product = $this->product($this->category(), 'Gallery Priority Product');
        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/not-primary.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 1]);
        $variant->images()->create(['image' => 'variants/primary.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 2]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        // Primary image must be the initial main-viewer image regardless of sort_order.
        $response->assertSee('id="main-viewer-image" src="'.Storage::url('variants/primary.jpg').'"', false);
    }

    public function test_gallery_respects_media_sort_order_and_supports_video(): void
    {
        Storage::fake('public');
        $product = $this->product($this->category(), 'Video Gallery Product');
        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/second.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);
        $variant->images()->create(['image' => 'variants/first.mp4', 'media_type' => 'video', 'is_primary' => false, 'sort_order' => 1]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('data-type="video" data-url="'.Storage::url('variants/first.mp4').'"', false);
        $response->assertSee('<video src="'.Storage::url('variants/first.mp4').'"', false);
    }

    public function test_only_approved_reviews_are_shown(): void
    {
        $product = $this->product($this->category(), 'Reviewed Product');
        $this->variant($product, ['is_default' => true]);
        $user = User::factory()->create();

        $product->reviews()->create(['user_id' => $user->id, 'rating' => 5, 'review' => 'Approved review text', 'status' => 'approved']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'review' => 'Pending review text', 'status' => 'pending']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'review' => 'Rejected review text', 'status' => 'rejected']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Approved review text');
        $response->assertDontSee('Pending review text');
        $response->assertDontSee('Rejected review text');
    }

    public function test_review_aggregate_is_computed_live_not_from_stale_product_columns(): void
    {
        $product = $this->product($this->category(), 'Aggregate Product', [
            // Deliberately wrong stored values, matching how the admin
            // review-approval flow never updates these columns.
            'average_rating' => 1.0,
            'review_count' => 99,
        ]);
        $this->variant($product, ['is_default' => true]);

        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 5, 'review' => 'Great', 'status' => 'approved']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 3, 'review' => 'Okay', 'status' => 'approved']);
        $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'review' => 'Ignored', 'status' => 'pending']);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        // Average of the two approved ratings (5 and 3) is 4.0, and count is 2 — not the stale 1.0/99.
        $response->assertSee('2 Reviews');
        $response->assertDontSee('99 Review');
    }

    public function test_related_products_exclude_current_and_inactive_products(): void
    {
        $category = $this->category();
        $product = $this->product($category, 'Main Product');
        $this->variant($product, ['is_default' => true]);

        $sibling = $this->product($category, 'Related Sibling');
        $this->variant($sibling, ['is_default' => true]);

        $inactiveSibling = $this->product($category, 'Inactive Sibling', ['status' => 'inactive']);
        $this->variant($inactiveSibling, ['is_default' => true]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Related Sibling');
        $response->assertDontSee('Inactive Sibling');

        // The product itself must not list itself as "related" — scoped to
        // the related-products section, since "Main Product" legitimately
        // appears elsewhere on its own page (<title>, <h2>, breadcrumb).
        $content = $response->getContent();
        $relatedStart = strpos($content, 'Related Products');
        $this->assertNotFalse($relatedStart);
        $this->assertStringNotContainsString('Main Product', substr($content, $relatedStart));
    }

    public function test_stock_state_reflects_variant_stock(): void
    {
        $product = $this->product($this->category(), 'Out Of Stock Product');
        $this->variant($product, ['is_default' => true, 'stock_status' => 'out_of_stock', 'stock_quantity' => 0]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Out of Stock');
        $this->assertAddToCartIsDisabled($response);
    }

    public function test_low_stock_shows_remaining_quantity(): void
    {
        $product = $this->product($this->category(), 'Low Stock Product');
        $this->variant($product, ['is_default' => true, 'stock_quantity' => 3, 'low_stock_quantity' => 5, 'stock_status' => 'in_stock']);

        $this->get('/products/'.$product->slug)->assertOk()->assertSee('Only 3 left');
    }

    public function test_price_and_compare_price_behavior_on_detail_page(): void
    {
        $product = $this->product($this->category(), 'Detail Price Product');
        $this->variant($product, [
            'is_default' => true,
            'enable_tiered_pricing' => true,
            'standard_quantity' => 10,
            'standard_price' => 300.00,
            'discount_quantity' => 10,
            'discount_price' => 250.00,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('₹250.00');
        $response->assertSee('<del', false);
    }

    public function test_seo_title_canonical_and_og_fallback(): void
    {
        $product = $this->product($this->category(), 'SEO Product', [
            'meta_title' => null,
            'meta_description' => null,
            'canonical_url' => null,
        ]);
        $this->variant($product, ['is_default' => true]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        // Falls back to the product name when meta_title is not set.
        $response->assertSee('<title>SEO Product</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('products.show', $product->slug).'"', false);
        $response->assertSee('property="og:title" content="SEO Product"', false);
    }

    /**
     * Regression test: og:image silently never rendered for any product
     * because App\View\Components\layouts\Header's constructor didn't
     * declare $ogImage — a class-based Blade component only exposes
     * attributes it explicitly declares as constructor parameters, so the
     * value was dropped before it ever reached header.blade.php, with no
     * error or warning anywhere.
     */
    public function test_og_image_uses_the_default_variants_primary_image(): void
    {
        Storage::fake('public');
        $product = $this->product($this->category(), 'OG Image Product');
        $variant = $this->variant($product, ['is_default' => true]);
        $variant->images()->create(['image' => 'variants/not-primary.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 1]);
        $variant->images()->create(['image' => 'variants/og-primary.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 2]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('property="og:image" content="'.Storage::url('variants/og-primary.jpg').'"', false);
    }

    public function test_seo_uses_explicit_meta_fields_when_set(): void
    {
        $product = $this->product($this->category(), 'Explicit SEO Product', [
            'meta_title' => 'Custom Meta Title',
            'canonical_url' => 'https://example.com/custom-canonical',
        ]);
        $this->variant($product, ['is_default' => true]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('<title>Custom Meta Title</title>', false);
        $response->assertSee('<link rel="canonical" href="https://example.com/custom-canonical"', false);
    }

    /**
     * Whitespace-tolerant check that the Add to Cart button carries a real
     * disabled attribute within its own tag — a plain assertSee() on an
     * exact string is brittle against Blade's @unless()-directive spacing.
     */
    private function assertAddToCartIsDisabled($response): void
    {
        $this->assertMatchesRegularExpression(
            '/id="add-to-cart-btn"[^>]*\bdisabled\b/',
            $response->getContent(),
            'Expected the Add to Cart button to carry a disabled attribute.'
        );
    }
}
