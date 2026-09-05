<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Bakery',
            'slug' => 'bakery-'.uniqid(),
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
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_active_category_resolves_by_slug(): void
    {
        $category = $this->category(['name' => 'Dairy']);
        $this->variant($this->product($category, 'Cheddar Cheese'));

        $this->get('/category/'.$category->slug)
            ->assertOk()
            ->assertSee('Dairy')
            ->assertSee('Cheddar Cheese');
    }

    public function test_inactive_category_returns_404(): void
    {
        $category = $this->category(['status' => 'inactive']);

        $this->get('/category/'.$category->slug)->assertNotFound();
    }

    public function test_unknown_category_slug_returns_404(): void
    {
        $this->get('/category/does-not-exist')->assertNotFound();
    }

    public function test_only_products_belonging_to_the_category_appear(): void
    {
        $categoryA = $this->category(['name' => 'Snacks']);
        $categoryB = $this->category(['name' => 'Beverages']);
        $this->variant($this->product($categoryA, 'Potato Chips'));
        $this->variant($this->product($categoryB, 'Orange Juice'));

        $response = $this->get('/category/'.$categoryA->slug);

        $response->assertOk();
        $grid = $this->gridContentOf($response);
        $this->assertStringContainsString('Potato Chips', $grid);
        $this->assertStringNotContainsString('Orange Juice', $grid);
    }

    public function test_products_from_other_categories_are_excluded_even_when_active(): void
    {
        $categoryA = $this->category();
        $categoryB = $this->category();
        $this->variant($this->product($categoryA, 'In Category A'));
        $productB = $this->product($categoryB, 'In Category B');
        $this->variant($productB);

        $response = $this->get('/category/'.$categoryA->slug);

        $response->assertOk();
        $this->assertStringNotContainsString('In Category B', $this->gridContentOf($response));
    }

    public function test_category_page_canonical_link_points_at_category_url_not_shop_query_string(): void
    {
        $category = $this->category();
        $this->variant($this->product($category, 'Canonical Test Product'));

        $response = $this->get('/category/'.$category->slug);

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.route('category.show', $category->slug).'"', false);
        $response->assertDontSee('shop?category=', false);
    }

    public function test_category_sidebar_link_for_current_category_points_at_canonical_url(): void
    {
        $category = $this->category(['name' => 'Herbs']);
        $this->variant($this->product($category, 'Basil'));

        $response = $this->get('/shop');

        $response->assertOk();
        // The category link in the Shop sidebar must go straight to
        // /category/{slug}, never to /shop?category=slug (Phase F fix #1).
        $response->assertSee(route('category.show', $category->slug), false);
    }

    /**
     * The product grid's HTML, excluding the sidebar "Sale Products"
     * widget — which lists best-sellers regardless of category/filters by
     * design (see ProductCatalogService::bestSellers()) and would
     * otherwise produce false failures for assertDontSee()-style checks in
     * a tiny test catalog where every product qualifies as a "best seller".
     */
    private function gridContentOf(\Illuminate\Testing\TestResponse $response): string
    {
        $content = $response->getContent();
        $gridStart = strpos($content, 'shop__product-items');
        $this->assertNotFalse($gridStart, 'Could not locate the product grid in the response.');

        return substr($content, $gridStart);
    }
}
