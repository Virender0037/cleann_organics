<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the pricing bug: single/standard/discount_price
 * are independent quantity tiers, not an old-price/new-price pair. A
 * strikethrough compare price must never be shown across two different
 * quantities — only when two tiers share the exact same quantity.
 */
class PricingTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $category = Category::create(['name' => 'Pricing Category', 'slug' => 'pricing-category-'.uniqid(), 'status' => 'active']);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Pricing Product',
            'slug' => 'pricing-product-'.uniqid(),
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
            'stock_quantity' => 10,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_normal_single_base_price_has_no_compare_price(): void
    {
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => 100.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(1, $tiers);
        $this->assertSame(1, $tiers[0]['quantity']);
        $this->assertSame(100.00, $tiers[0]['price']);
        $this->assertNull($tiers[0]['compare_price']);
        $this->assertSame(100.00, $variant->headlinePrice());
        $this->assertFalse($variant->hasMultipleTiers());
    }

    public function test_tiered_pricing_lists_each_quantity_tier_separately(): void
    {
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 299.00,
            'standard_quantity' => 10,
            'standard_price' => 279.00,
            'discount_quantity' => 30,
            'discount_price' => 249.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(3, $tiers);
        $this->assertSame([1, 10, 30], array_column($tiers, 'quantity'));
        $this->assertSame([299.00, 279.00, 249.00], array_column($tiers, 'price'));
        // The headline (what a 1-unit buyer pays) is the lowest-quantity
        // tier's price, never the deepest bulk price.
        $this->assertSame(299.00, $variant->headlinePrice());
        $this->assertTrue($variant->hasMultipleTiers());
    }

    public function test_no_false_discount_when_tiers_are_simply_different_quantities(): void
    {
        // This is the exact shape of the bug: a much lower price at a much
        // higher quantity must never surface as a compare_price against a
        // lower-quantity tier — that would claim a 1-unit buyer gets the
        // bulk rate, which they don't.
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'standard_quantity' => 5,
            'standard_price' => 180.00,
            'discount_quantity' => 10,
            'discount_price' => 160.00,
        ]);

        $tiers = $variant->pricingTiers();

        foreach ($tiers as $tier) {
            $this->assertNull($tier['compare_price'], 'A tier at one quantity must never carry a compare_price from a different quantity.');
        }

        $this->assertSame(180.00, $variant->headlinePrice());
    }

    public function test_legitimate_compare_price_shown_only_for_same_quantity_tiers(): void
    {
        // standard and discount both apply to the same quantity (10) — a
        // genuine same-quantity price cut, so a strikethrough is honest here.
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'standard_quantity' => 10,
            'standard_price' => 300.00,
            'discount_quantity' => 10,
            'discount_price' => 250.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(1, $tiers, 'Same-quantity tiers must collapse into a single row.');
        $this->assertSame(10, $tiers[0]['quantity']);
        $this->assertSame(250.00, $tiers[0]['price']);
        $this->assertSame(300.00, $tiers[0]['compare_price']);
        $this->assertGreaterThan($tiers[0]['price'], $tiers[0]['compare_price']);
    }

    public function test_same_quantity_tiers_produce_no_compare_price_when_not_actually_cheaper(): void
    {
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'standard_quantity' => 10,
            'standard_price' => 250.00,
            'discount_quantity' => 10,
            'discount_price' => 250.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(1, $tiers);
        $this->assertNull($tiers[0]['compare_price'], 'Equal prices at the same quantity are not a discount.');
    }

    public function test_missing_tier_values_are_dropped_safely(): void
    {
        // enable_tiered_pricing on, but only standard is fully populated;
        // discount_quantity is set with no discount_price (incomplete).
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'standard_quantity' => 10,
            'standard_price' => 279.00,
            'discount_quantity' => 30,
            'discount_price' => null,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(1, $tiers);
        $this->assertSame(10, $tiers[0]['quantity']);
        $this->assertSame(279.00, $tiers[0]['price']);
    }

    public function test_no_price_at_all_returns_no_tiers_and_null_headline(): void
    {
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => false,
            'single_quantity' => null,
            'single_price' => null,
        ]);

        $this->assertSame([], $variant->pricingTiers());
        $this->assertNull($variant->headlinePrice());
        $this->assertFalse($variant->hasMultipleTiers());
    }

    public function test_misordered_quantities_are_sorted_ascending_without_crashing(): void
    {
        // discount_quantity (30) is the smallest configured quantity here —
        // an admin data-entry quirk, not something the accessor should choke
        // on. It must still come out sorted ascending by quantity.
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'single_quantity' => 50,
            'single_price' => 200.00,
            'standard_quantity' => 40,
            'standard_price' => 220.00,
            'discount_quantity' => 30,
            'discount_price' => 240.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertSame([30, 40, 50], array_column($tiers, 'quantity'));
        $this->assertSame(240.00, $variant->headlinePrice(), 'Headline must be the lowest-quantity tier regardless of field order.');

        foreach ($tiers as $tier) {
            $this->assertNull($tier['compare_price']);
        }
    }

    public function test_zero_or_negative_quantity_tiers_are_excluded(): void
    {
        $variant = $this->variant($this->product(), [
            'enable_tiered_pricing' => true,
            'standard_quantity' => 0,
            'standard_price' => 100.00,
            'discount_quantity' => 10,
            'discount_price' => 90.00,
        ]);

        $tiers = $variant->pricingTiers();

        $this->assertCount(1, $tiers);
        $this->assertSame(10, $tiers[0]['quantity']);
    }

    public function test_product_card_partial_renders_headline_price_without_false_discount(): void
    {
        $product = $this->product();
        $this->variant($product, [
            'is_default' => true,
            'enable_tiered_pricing' => true,
            'standard_quantity' => 5,
            'standard_price' => 180.00,
            'discount_quantity' => 10,
            'discount_price' => 160.00,
        ]);
        $product->load('variants.images');
        $product->approved_average_rating = 0;
        $product->approved_review_count = 0;

        $html = view('components.frontend.product-card', ['product' => $product])->render();

        $this->assertStringContainsString('From', $html);
        $this->assertStringContainsString('₹180.00', $html);
        $this->assertStringNotContainsString('₹160.00', $html, 'The deeper bulk tier must not appear as the card headline price.');
        $this->assertStringNotContainsString('<del', $html, 'No strikethrough is legitimate here — the two prices are for different quantities.');
    }

    public function test_product_card_shows_legitimate_strikethrough_for_same_quantity_cut(): void
    {
        $product = $this->product();
        $this->variant($product, [
            'is_default' => true,
            'enable_tiered_pricing' => true,
            'standard_quantity' => 10,
            'standard_price' => 300.00,
            'discount_quantity' => 10,
            'discount_price' => 250.00,
        ]);
        $product->load('variants.images');
        $product->approved_average_rating = 0;
        $product->approved_review_count = 0;

        $html = view('components.frontend.product-card', ['product' => $product])->render();

        $this->assertStringContainsString('₹250.00', $html);
        $this->assertStringContainsString('<del', $html);
        $this->assertStringContainsString('₹300.00', $html);
    }
}
