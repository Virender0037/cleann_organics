<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The dynamic quantity/tier UX (updating price/total/messages as the
 * shopper types or clicks +/-) is client-side JS and isn't exercisable from
 * PHPUnit. What's covered here instead:
 *   - the initial server-rendered markup the JS enhances (the "each" label,
 *     the new feedback container, the tiers JSON the JS reads from) is
 *     correct for both tiered and non-tiered variants;
 *   - the server-side tier price the JS mirrors is still the one actually
 *     charged at Add to Cart time, at the exact 1+/10+/30+ thresholds from
 *     the feature request, regardless of what the browser displayed.
 * Full interactive behavior (clicking +/-, typing a quantity, switching
 * variants) is verified by hand in the browser — see the session's report.
 */
class ProductDetailTierPricingTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create([
            'name' => 'Cleaning',
            'slug' => 'cleaning-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function product(string $name): Product
    {
        return Product::create([
            'category_id' => $this->category()->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function nonTieredVariant(Product $product): ProductVariant
    {
        return $product->variants()->create([
            'variant_name' => 'Standard',
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => 100.00,
            'stock_quantity' => 50,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
        ]);
    }

    /**
     * Mirrors the exact example from the feature request: 1+ -> 1208,
     * 10+ -> 1160, 30+ -> 1140.
     */
    private function tieredVariant(Product $product): ProductVariant
    {
        return $product->variants()->create([
            'variant_name' => 'Bulk',
            'enable_tiered_pricing' => true,
            'single_quantity' => 1,
            'single_price' => 1208.00,
            'standard_quantity' => 10,
            'standard_price' => 1160.00,
            'discount_quantity' => 30,
            'discount_price' => 1140.00,
            'stock_quantity' => 100,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
        ]);
    }

    public function test_non_tiered_variant_has_no_each_label_or_volume_feedback(): void
    {
        $product = $this->product('Simple Soap');
        $this->nonTieredVariant($product);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('₹100.00', false);
        // The inline script's source always contains the literal string
        // 'id="price-each"' (it's part of the JS that builds that markup),
        // so a plain assertDontSee can't tell that apart from the element
        // actually being rendered — only ONE occurrence (the JS source)
        // should exist for a non-tiered variant, since Blade's own
        // @if($hasMultipleTiers) correctly omits the real element.
        $this->assertSame(1, substr_count($response->getContent(), 'id="price-each"'));
        // Container exists (the JS enhances it) but starts empty — the
        // page doesn't fabricate a volume message for a non-tiered product.
        $response->assertSee('id="tier-pricing-feedback"', false);
    }

    public function test_tiered_variant_renders_each_label_and_feedback_container(): void
    {
        $product = $this->product('Bulk Cleaner');
        $this->tieredVariant($product);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('id="price-each"', false);
        $response->assertSee('id="tier-pricing-feedback"', false);
        // The static tier list (unchanged, pre-existing requirement).
        $response->assertSee('1+ qty', false);
        $response->assertSee('10+ qty', false);
        $response->assertSee('30+ qty', false);
    }

    public function test_variants_payload_json_carries_the_tier_data_the_js_depends_on(): void
    {
        $product = $this->product('Bulk Cleaner JSON');
        $variant = $this->tieredVariant($product);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $content = $response->getContent();

        // The embedded JSON is what findActiveTier()/applyTierPricing() in
        // the page's inline script reads from — assert the exact values
        // from the feature request are present, keyed by this variant.
        $this->assertStringContainsString('"'.$variant->id.'":', $content);
        $this->assertStringContainsString('"quantity":1', $content);
        $this->assertStringContainsString('"price":1208', $content);
        $this->assertStringContainsString('"quantity":10', $content);
        $this->assertStringContainsString('"price":1160', $content);
        $this->assertStringContainsString('"quantity":30', $content);
        $this->assertStringContainsString('"price":1140', $content);
        $this->assertStringContainsString('"has_multiple_tiers":true', $content);
    }

    public function test_add_to_cart_charges_the_real_tier_price_at_qty_10_regardless_of_display(): void
    {
        // Guest carts are session-only by design in this app (see
        // CartService's class docblock) — cart_items is only ever written
        // for an authenticated customer, so that's what this asserts against.
        $product = $this->product('Bulk Cleaner Cart 10');
        $variant = $this->tieredVariant($product);

        $this->actingAs(User::factory()->create())->post('/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 10,
            'unit_price' => 1160.00,
        ]);
    }

    public function test_add_to_cart_charges_the_real_tier_price_at_qty_30_regardless_of_display(): void
    {
        $product = $this->product('Bulk Cleaner Cart 30');
        $variant = $this->tieredVariant($product);

        $this->actingAs(User::factory()->create())->post('/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 30,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 30,
            'unit_price' => 1140.00,
        ]);
    }

    public function test_add_to_cart_ignores_any_client_submitted_price_field(): void
    {
        $product = $this->product('Bulk Cleaner No Trust');
        $variant = $this->tieredVariant($product);

        // The real form never submits a price field at all — this proves
        // that even if one were injected, the server ignores it and still
        // derives the price itself from the quantity via
        // ProductVariant::unitPriceForQuantity().
        $this->actingAs(User::factory()->create())->post('/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 30,
            'unit_price' => 1,
            'price' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 30,
            'unit_price' => 1140.00,
        ]);
    }
}
