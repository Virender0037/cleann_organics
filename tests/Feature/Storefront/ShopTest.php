<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Literal relative paths (not route()) for request URLs — APP_URL points at
 * a XAMPP subdirectory; see ProductListThumbnailTest for the full reasoning.
 */
class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Vegetables',
            'slug' => 'vegetables-'.uniqid(),
            'status' => 'active',
        ], $overrides));
    }

    private function product(Category $category, string $name, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name).'-'.uniqid(),
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
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_active_product_appears_on_shop_page(): void
    {
        $product = $this->product($this->category(), 'Visible Carrot');
        $this->variant($product);

        $this->get('/shop')->assertOk()->assertSee('Visible Carrot');
    }

    public function test_inactive_product_is_hidden(): void
    {
        $product = $this->product($this->category(), 'Hidden Broccoli', ['status' => 'inactive']);
        $this->variant($product);

        $this->get('/shop')->assertOk()->assertDontSee('Hidden Broccoli');
    }

    public function test_product_in_inactive_category_is_hidden(): void
    {
        $category = $this->category(['status' => 'inactive']);
        $product = $this->product($category, 'Orphaned Spinach');
        $this->variant($product);

        $this->get('/shop')->assertOk()->assertDontSee('Orphaned Spinach');
    }

    public function test_real_thumbnail_image_is_used_on_the_card(): void
    {
        Storage::fake('public');
        $product = $this->product($this->category(), 'Thumbnail Potato');
        $variant = $this->variant($product);
        $variant->images()->create(['image' => 'variants/potato-primary.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $this->get('/shop')->assertOk()->assertSee(Storage::url('variants/potato-primary.jpg'), false);
    }

    public function test_search_filters_by_product_name(): void
    {
        $category = $this->category();
        $this->variant($this->product($category, 'Findable Zucchini'));
        $this->variant($this->product($category, 'Other Item'));

        $response = $this->get('/shop?search=Findable');

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Findable Zucchini', $grid);
        $this->assertStringNotContainsString('Other Item', $grid);
    }

    public function test_category_filter_via_query_param_still_works(): void
    {
        $categoryA = $this->category(['name' => 'Fruits']);
        $categoryB = $this->category(['name' => 'Grains']);
        $this->variant($this->product($categoryA, 'Apple'));
        $this->variant($this->product($categoryB, 'Rice'));

        $response = $this->get('/shop?category='.$categoryA->slug);

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Apple', $grid);
        $this->assertStringNotContainsString('Rice', $grid);
    }

    public function test_tag_filter_works(): void
    {
        $category = $this->category();
        $tag = Tag::create(['name' => 'Organic', 'slug' => 'organic-'.uniqid(), 'status' => 'active']);
        $tagged = $this->product($category, 'Tagged Lettuce');
        $tagged->tags()->attach($tag->id);
        $this->variant($tagged);
        $this->variant($this->product($category, 'Untagged Cabbage'));

        $response = $this->get('/shop?tag='.$tag->slug);

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Tagged Lettuce', $grid);
        $this->assertStringNotContainsString('Untagged Cabbage', $grid);
    }

    public function test_rating_filter_only_shows_products_meeting_the_minimum(): void
    {
        $category = $this->category();
        $highRated = $this->product($category, 'Five Star Mango');
        $this->variant($highRated);
        $this->approveReview($highRated, 5);

        $lowRated = $this->product($category, 'Two Star Melon');
        $this->variant($lowRated);
        $this->approveReview($lowRated, 2);

        $response = $this->get('/shop?rating=4');

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Five Star Mango', $grid);
        $this->assertStringNotContainsString('Two Star Melon', $grid);
    }

    public function test_price_filter_uses_real_variant_pricing(): void
    {
        $category = $this->category();
        $cheap = $this->product($category, 'Cheap Onion');
        $this->variant($cheap, ['single_price' => 20.00]);
        $expensive = $this->product($category, 'Expensive Saffron');
        $this->variant($expensive, ['single_price' => 500.00]);

        $response = $this->get('/shop?min_price=0&max_price=100');

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Cheap Onion', $grid);
        $this->assertStringNotContainsString('Expensive Saffron', $grid);
    }

    public function test_sorting_by_price_low_to_high_orders_products(): void
    {
        $category = $this->category();
        $expensive = $this->product($category, 'Pricey Cashew');
        $this->variant($expensive, ['single_price' => 900.00]);
        $cheap = $this->product($category, 'Budget Peanut');
        $this->variant($cheap, ['single_price' => 50.00]);

        $response = $this->get('/shop?sort=price-asc');

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertLessThan(
            strpos($grid, 'Pricey Cashew'),
            strpos($grid, 'Budget Peanut'),
            'Cheaper product should appear before the pricier one when sorted price-asc.'
        );
    }

    public function test_pagination_splits_results_across_pages(): void
    {
        $category = $this->category();
        foreach (range(1, 14) as $i) {
            $this->variant($this->product($category, "Paginated Product {$i}"));
        }

        $pageOne = $this->get('/shop');
        $pageOne->assertOk()->assertSee('Paginated Product 1');

        $pageTwo = $this->get('/shop?page=2');
        $pageTwo->assertOk();
        // 14 products at 12 per page means page 2 exists and has content.
        $this->assertStringContainsString('Paginated Product', $pageTwo->getContent());
    }

    /**
     * The product grid's HTML, excluding the sidebar "Sale Products"
     * widget — which lists best-sellers regardless of the shopper's active
     * filters by design (see ProductCatalogService::bestSellers()) and
     * would otherwise produce false failures for assertDontSee() checks in
     * a tiny test catalog where every product qualifies as a "best seller".
     */
    private function gridContentOf(\Illuminate\Testing\TestResponse $response): string
    {
        $content = $response->getContent();
        $gridStart = strpos($content, 'shop__product-items');
        $this->assertNotFalse($gridStart, 'Could not locate the product grid in the response.');

        return substr($content, $gridStart);
    }

    private function approveReview(Product $product, int $rating): void
    {
        $user = \App\Models\User::factory()->create();
        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $rating,
            'review' => 'Review text',
            'status' => 'approved',
        ]);
    }
}
