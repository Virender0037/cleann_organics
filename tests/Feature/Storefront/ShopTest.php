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
        Storage::disk('public')->put('variants/potato-primary.jpg', 'fake-image-content');
        $product = $this->product($this->category(), 'Thumbnail Potato');
        $variant = $this->variant($product);
        $variant->images()->create(['image' => 'variants/potato-primary.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $this->get('/shop')->assertOk()->assertSee(Storage::url('variants/potato-primary.jpg'), false);
    }

    /**
     * A database row can reference an image path whose file was never
     * actually uploaded (or was deleted afterwards) — a DB restored/copied
     * without its media is exactly this shape. The card must fall back to
     * the bundled placeholder image rather than pointing the browser at a
     * URL that 404s and renders as a broken-image icon.
     */
    public function test_thumbnail_falls_back_when_the_referenced_file_does_not_exist_on_disk(): void
    {
        Storage::fake('public');
        // Deliberately never written to the fake disk.
        $product = $this->product($this->category(), 'Ghost File Turnip');
        $variant = $this->variant($product);
        $variant->images()->create(['image' => 'variants/missing-file.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $this->get('/shop')
            ->assertOk()
            ->assertDontSee(Storage::url('variants/missing-file.jpg'), false)
            ->assertSee(asset('images/products/img-01.png'), false);
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
    // ------------------------------------------------------------------
    // ONLY_FULL_GROUP_BY regression (production MariaDB, error 1055)
    // ------------------------------------------------------------------
    //
    // The suite runs on SQLite, which tolerates a bare GROUP BY products.id,
    // so the bug cannot be caught by looking at results. It is caught by the
    // SHAPE of the SQL instead: the product listing must never use GROUP BY or
    // HAVING (filters live in WHERE on correlated subqueries), which is valid
    // under every MySQL/MariaDB sql_mode, including ONLY_FULL_GROUP_BY.

    /** @return array<int, string> the product-listing SQL run while $callback executes */
    private function listingQueriesDuring(callable $callback): array
    {
        $queries = [];
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries) {
            $sql = strtolower($query->sql);

            // The listing SELECT and its pagination COUNT both embed the variant
            // price subquery; the sidebar's legitimate GROUP BY category_id count does not.
            if (str_contains($sql, 'effective_min_price') || preg_match('/product_variants[`"]?\s+pv/', $sql)) {
                $queries[] = $sql;
            }
        });

        $callback();

        return $queries;
    }

    public function test_price_and_rating_filters_never_group_or_use_having(): void
    {
        $category = $this->category(['slug' => 'cleaners']);
        $product = $this->product($category, 'Floor Cleaner');
        $this->variant($product, ['single_price' => 40.00]);
        $this->approveReview($product, 5);

        $urls = [
            '/shop?max_price=50',
            '/shop?min_price=10&max_price=99',
            '/shop?rating=3',
            '/shop?rating=3&max_price=99&search=floor&sort=price-asc',
            '/shop?category=cleaners&min_price=5&max_price=500&rating=1&sort=price-desc&page=1',
            '/category/cleaners?max_price=99',
        ];

        $queries = $this->listingQueriesDuring(function () use ($urls) {
            foreach ($urls as $url) {
                $this->get($url)->assertOk();
            }
        });

        $this->assertNotEmpty($queries, 'The listing queries were not captured.');

        foreach ($queries as $sql) {
            $this->assertStringNotContainsString(' group by ', $sql, 'Product listing must not GROUP BY (breaks ONLY_FULL_GROUP_BY).');
            $this->assertStringNotContainsString(' having ', $sql, 'Product listing must not use HAVING.');
        }
    }

    public function test_combined_filters_return_the_exact_matching_products_and_paginate(): void
    {
        $category = $this->category(['name' => 'Cleaners', 'slug' => 'cleaners']);
        $other = $this->category(['name' => 'Bottles', 'slug' => 'bottles']);

        $match = $this->product($category, 'Lemon Floor Cleaner');
        $this->variant($match, ['single_price' => 45.00]);
        $this->approveReview($match, 5);

        $tooPricey = $this->product($category, 'Lemon Deluxe Cleaner');
        $this->variant($tooPricey, ['single_price' => 400.00]);
        $this->approveReview($tooPricey, 5);

        $lowRated = $this->product($category, 'Lemon Basic Cleaner');
        $this->variant($lowRated, ['single_price' => 30.00]);
        $this->approveReview($lowRated, 1);

        $wrongCategory = $this->product($other, 'Lemon Bottle');
        $this->variant($wrongCategory, ['single_price' => 20.00]);
        $this->approveReview($wrongCategory, 5);

        $grid = $this->gridContentOf($this->get('/shop?category=cleaners&max_price=99&rating=4&search=lemon&sort=price-asc')->assertOk());

        $this->assertStringContainsString('Lemon Floor Cleaner', $grid);
        $this->assertStringNotContainsString('Lemon Deluxe Cleaner', $grid);
        $this->assertStringNotContainsString('Lemon Basic Cleaner', $grid);
        $this->assertStringNotContainsString('Lemon Bottle', $grid);
    }

    public function test_price_filtered_listing_counts_and_paginates_correctly(): void
    {
        $category = $this->category(['slug' => 'cleaners']);

        // 15 cheap products (2 pages of 12) + 3 expensive ones that must not be counted.
        foreach (range(1, 15) as $i) {
            $this->variant($this->product($category, "Cheap Item {$i}"), ['single_price' => 20.00]);
        }
        foreach (range(1, 3) as $i) {
            $this->variant($this->product($category, "Pricey Item {$i}"), ['single_price' => 900.00]);
        }

        $first = $this->get('/shop?max_price=50')->assertOk();
        $second = $this->get('/shop?max_price=50&page=2')->assertOk();

        // Assert on the paginator itself: the count query must see exactly the 15
        // matches (not 18), split 12 + 3 across two pages.
        $firstPage = $first->viewData('products');
        $secondPage = $second->viewData('products');

        $this->assertSame(15, $firstPage->total());
        $this->assertSame(2, $firstPage->lastPage());
        $this->assertCount(12, $firstPage->items());
        $this->assertCount(3, $secondPage->items());
        $this->assertTrue($firstPage->getCollection()->concat($secondPage->getCollection())->every(fn ($p) => str_starts_with($p->name, 'Cheap Item')));
    }

    public function test_homepage_explore_our_range_links_load_without_error(): void
    {
        $category = $this->category();
        $this->variant($this->product($category, 'Tiny Item'), ['single_price' => 9.00]);

        foreach ([10, 50, 99] as $ceiling) {
            $this->get("/shop?max_price={$ceiling}")->assertOk()->assertSee('Tiny Item');
        }
    }


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
