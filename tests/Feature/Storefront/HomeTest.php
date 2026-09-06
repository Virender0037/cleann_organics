<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Literal relative "/" URL (not route()) — see ShopTest for why: APP_URL
 * points at a XAMPP subdirectory in this env.
 */
class HomeTest extends TestCase
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

    /**
     * A variant with a genuine same-quantity discount per
     * ProductVariant::pricingTiers()'s own rule (two tiers sharing one
     * quantity, different prices) — not a fabricated discount check.
     */
    private function discountedVariant(Product $product, array $overrides = []): ProductVariant
    {
        return $this->variant($product, array_merge([
            'enable_tiered_pricing' => true,
            'single_quantity' => null,
            'single_price' => null,
            'standard_quantity' => 1,
            'standard_price' => 200.00,
            'discount_quantity' => 1,
            'discount_price' => 150.00,
        ], $overrides));
    }

    public function test_homepage_returns_200(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_homepage_does_not_crash_with_no_products_or_categories(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_real_active_category_appears_in_popular_categories(): void
    {
        $category = $this->category(['name' => 'Real Fresh Herbs']);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="popular-categories section');
        $this->assertStringContainsString('Real Fresh Herbs', $section);
        $this->assertStringContainsString(route('category.show', $category->slug), $section);
    }

    public function test_inactive_category_does_not_appear_in_popular_categories(): void
    {
        $this->category(['name' => 'Hidden Category', 'status' => 'inactive']);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="popular-categories section');
        $this->assertStringNotContainsString('Hidden Category', $section);
    }

    public function test_real_featured_product_appears(): void
    {
        $product = $this->product($this->category(), 'Real Featured Mango', ['is_featured' => true]);
        $this->variant($product);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="section section--lg featured');
        $this->assertStringContainsString('Real Featured Mango', $section);
        $this->assertStringContainsString(route('products.show', $product->slug), $section);
    }

    public function test_non_featured_product_does_not_appear_in_featured_section(): void
    {
        $this->product($this->category(), 'Not Featured Kiwi', ['is_featured' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="section section--lg featured');
        $this->assertStringNotContainsString('Not Featured Kiwi', $section);
    }

    public function test_inactive_product_does_not_appear_anywhere_on_homepage(): void
    {
        $product = $this->product($this->category(), 'Inactive Guava', ['status' => 'inactive', 'is_featured' => true, 'is_best_seller' => true]);
        $this->variant($product);

        $this->get('/')->assertOk()->assertDontSee('Inactive Guava');
    }

    public function test_product_in_inactive_category_does_not_appear(): void
    {
        $category = $this->category(['status' => 'inactive']);
        $product = $this->product($category, 'Orphaned Papaya', ['is_featured' => true]);
        $this->variant($product);

        $this->get('/')->assertOk()->assertDontSee('Orphaned Papaya');
    }

    public function test_popular_products_section_uses_real_products(): void
    {
        $product = $this->product($this->category(), 'Real Popular Pumpkin', ['is_best_seller' => true]);
        $this->variant($product);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="popular-products section');
        $this->assertStringContainsString('Real Popular Pumpkin', $section);
        $this->assertStringContainsString(route('products.show', $product->slug), $section);
    }

    public function test_hot_deals_uses_genuinely_discounted_products_only(): void
    {
        $discounted = $this->product($this->category(), 'Discounted Coconut');
        $this->discountedVariant($discounted);

        $fullPrice = $this->product($this->category(), 'Full Price Turnip');
        $this->variant($fullPrice);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="deals section--gray');
        $this->assertStringContainsString('Discounted Coconut', $section);
        $this->assertStringNotContainsString('Full Price Turnip', $section);
    }

    public function test_real_category_route_appears_in_footer(): void
    {
        $category = $this->category(['name' => 'Footer Real Category']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Footer Real Category');
        $response->assertSee(route('category.show', $category->slug), false);
    }

    public function test_inactive_category_does_not_appear_in_footer(): void
    {
        $this->category(['name' => 'Footer Hidden Category', 'status' => 'inactive']);

        $this->get('/')->assertOk()->assertDontSee('Footer Hidden Category');
    }

    public function test_hardcoded_demo_footer_categories_are_gone(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Bread &amp; Bakery', false);
        $response->assertDontSeeText('Bread & Bakery');
    }

    public function test_stale_static_demo_markup_is_absent(): void
    {
        $product = $this->product($this->category(), 'Anchor Product');
        $this->variant($product);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('product-details.html', false);
        $response->assertDontSee('shop-01.html', false);
        $response->assertDontSeeText('Orange');
        $response->assertDontSeeText('Green Apple');
        $response->assertDontSeeText('Green Chili');
        $response->assertDontSeeText('Chinese Cabbage');
        $response->assertDontSee('524 feedback', false);
        $response->assertDontSee('$14.99', false);
    }

    public function test_same_collection_feeds_desktop_and_mobile_popular_products(): void
    {
        $product = $this->product($this->category(), 'Duplicate-Safe Radish', ['is_best_seller' => true]);
        $this->variant($product);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="popular-products section');
        [$desktop, $mobile] = $this->splitDesktopMobile($section);
        // Desktop grid + mobile swiper duplicate both render from the same
        // $popularProducts collection, so the one real product appears in
        // both halves — not padded with any fake card either side.
        $this->assertStringContainsString('Duplicate-Safe Radish', $desktop);
        $this->assertStringContainsString('Duplicate-Safe Radish', $mobile);
    }

    public function test_same_collection_feeds_desktop_and_mobile_hot_deals(): void
    {
        $discounted = $this->product($this->category(), 'Duplicate-Safe Deal Yam');
        $this->discountedVariant($discounted);

        $response = $this->get('/');

        $response->assertOk();
        $section = $this->sectionContent($response, 'class="deals section--gray');
        [$desktop, $mobile] = $this->splitDesktopMobile($section);
        $this->assertStringContainsString('Duplicate-Safe Deal Yam', $desktop);
        $this->assertStringContainsString('Duplicate-Safe Deal Yam', $mobile);
    }

    /**
     * A section's HTML from its opening <section ...> tag up to the next
     * <section — mirrors ShopTest::gridContentOf()'s "scope assertions to
     * one region" approach so a check for one section can't be satisfied by
     * unrelated markup elsewhere on the same page.
     */
    private function sectionContent(TestResponse $response, string $sectionMarker): string
    {
        $content = $response->getContent();
        $start = strpos($content, $sectionMarker);
        $this->assertNotFalse($start, "Could not locate section starting with [{$sectionMarker}].");

        $nextSection = strpos($content, '<section', $start + 1);
        $end = $nextSection !== false ? $nextSection : strlen($content);

        return substr($content, $start, $end - $start);
    }

    /**
     * Splits a section's HTML at its "Mobile Versions" marker comment, so a
     * test can assert the same product appears on both sides independently
     * — the real check for "one collection feeds both responsive copies" —
     * rather than counting raw substring occurrences, which also matches
     * every aria-label/alt attribute a card repeats the name into.
     *
     * @return array{0: string, 1: string} [desktopHtml, mobileHtml]
     */
    private function splitDesktopMobile(string $section): array
    {
        $marker = strpos($section, 'Mobile Versions');
        $this->assertNotFalse($marker, 'Could not locate the "Mobile Versions" marker in this section.');

        return [substr($section, 0, $marker), substr($section, $marker)];
    }
}
