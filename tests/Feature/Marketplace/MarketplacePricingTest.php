<?php

namespace Tests\Feature\Marketplace;

use App\Models\Category;
use App\Models\MarketplaceClick;
use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Storefront\MarketplaceComparison;
use App\Support\Marketplaces;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarketplacePricingTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create(['name' => 'Bottles', 'slug' => 'bottles-'.uniqid(), 'status' => 'active']);
    }

    private function product(string $name = 'Bamboo Water Bottle', array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category()->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ], $overrides));
    }

    private function variant(Product $product, float $price = 449.00, array $overrides = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'variant_name' => 'Variant '.uniqid(),
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => $price,
            'stock_quantity' => 10,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    private function offer(Product $product, string $marketplace = 'amazon', array $overrides = []): ProductMarketplacePrice
    {
        return $product->marketplacePrices()->create(array_merge([
            'marketplace' => $marketplace,
            'selling_price' => 474,
            'mrp' => 799,
            'product_url' => "https://www.{$marketplace}.com/dp/ABC",
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'marketplace' => 'amazon',
            'product_variant_id' => '',
            'marketplace_product_name' => 'Bamboo Bottle 500ml',
            'selling_price' => '474',
            'mrp' => '799',
            'product_url' => 'https://www.amazon.in/dp/B0TEST',
            'affiliate_url' => '',
            'is_active' => '1',
            'display_order' => '0',
            'last_checked_at' => '',
        ], $override);
    }

    // ------------------------------------------------------------------ Model

    public function test_product_has_many_marketplace_prices_and_scopes_and_ordering_work(): void
    {
        $product = $this->product();
        $second = $this->offer($product, 'flipkart', ['sort_order' => 2]);
        $first = $this->offer($product, 'amazon', ['sort_order' => 1]);
        $inactive = $this->offer($product, 'meesho', ['is_active' => false, 'sort_order' => 0]);
        $unpriced = $this->offer($product, 'zepto', ['selling_price' => null, 'sort_order' => 0]);

        $this->assertSame(['meesho', 'zepto', 'amazon', 'flipkart'], $product->marketplacePrices->pluck('marketplace')->all());
        $this->assertSame(['amazon', 'flipkart', 'zepto'], ProductMarketplacePrice::active()->ordered()->pluck('marketplace')->sortBy(fn ($m) => ['amazon' => 1, 'flipkart' => 2, 'zepto' => 3][$m])->values()->all());
        $this->assertSame(['amazon', 'flipkart'], $product->visibleMarketplacePrices->pluck('marketplace')->all());
        $this->assertTrue($first->product->is($product));
        $this->assertFalse($inactive->isDisplayable());
        $this->assertFalse($unpriced->isDisplayable());
        $this->assertTrue($second->isDisplayable());
    }

    public function test_discount_percent_is_calculated_and_only_when_it_is_real(): void
    {
        $product = $this->product();

        $this->assertSame(41, $this->offer($product, 'amazon', ['mrp' => 799, 'selling_price' => 474])->discountPercent());
        $this->assertSame(43, $this->offer($product, 'flipkart', ['mrp' => 799, 'selling_price' => 459])->discountPercent());
        $this->assertNull($this->offer($product, 'meesho', ['mrp' => null, 'selling_price' => 439])->discountPercent());
        $this->assertNull($this->offer($product, 'zepto', ['mrp' => 400, 'selling_price' => 400])->discountPercent());
        $this->assertNull($this->offer($product, 'blinkit', ['mrp' => 300, 'selling_price' => 400])->discountPercent());
        $this->assertNull($this->offer($product, 'jiomart', ['mrp' => 0, 'selling_price' => 10])->discountPercent());
        $this->assertTrue($product->marketplacePrices()->where('marketplace', 'blinkit')->first()->sellingPriceExceedsMrp());
        $this->assertArrayNotHasKey('discount_percent', (new ProductMarketplacePrice)->getAttributes());
        $this->assertFalse(Schema::hasColumn('product_marketplace_prices', 'discount_percent'));
    }

    public function test_display_url_prefers_the_affiliate_url_and_refuses_unsafe_schemes(): void
    {
        $product = $this->product();
        $both = $this->offer($product, 'amazon', ['product_url' => 'https://www.amazon.in/dp/X', 'affiliate_url' => 'https://amzn.to/abc']);
        $plain = $this->offer($product, 'flipkart', ['product_url' => 'https://www.flipkart.com/p/x', 'affiliate_url' => null]);
        $unsafe = $this->offer($product, 'meesho', ['product_url' => 'javascript:alert(1)', 'affiliate_url' => 'data:text/html;base64,AAAA']);

        $this->assertSame('https://amzn.to/abc', $both->displayUrl());
        $this->assertSame('https://www.flipkart.com/p/x', $plain->displayUrl());
        $this->assertNull($unsafe->displayUrl());
        $this->assertNull(ProductMarketplacePrice::safeUrl('file:///etc/passwd'));
        $this->assertNull(ProductMarketplacePrice::safeUrl('//evil.example'));
    }

    public function test_one_listing_per_product_variant_and_marketplace_is_enforced_by_the_database(): void
    {
        $product = $this->product();
        $variant = $this->variant($product);

        $this->offer($product, 'amazon');
        $this->offer($product, 'amazon', ['product_variant_id' => $variant->id]); // variant-level is a different scope: allowed
        $this->assertSame(0, ProductMarketplacePrice::where('product_variant_id', null)->first()->variant_scope);
        $this->assertSame($variant->id, ProductMarketplacePrice::where('product_variant_id', $variant->id)->first()->variant_scope);

        $this->expectException(QueryException::class);
        $this->offer($product, 'amazon'); // second product-level Amazon row
    }

    // ------------------------------------------------------- Service / variants

    public function test_variant_specific_offer_wins_and_product_level_is_the_fallback(): void
    {
        $product = $this->product('Rose Floor Cleaner');
        $small = $this->variant($product, 199, ['variant_name' => '500ml', 'is_default' => true]);
        $large = $this->variant($product, 349, ['variant_name' => '1L']);
        $this->offer($product, 'amazon', ['selling_price' => 250]);                                   // product-level
        $this->offer($product, 'amazon', ['selling_price' => 399, 'product_variant_id' => $large->id]); // 1L only
        $this->offer($product, 'flipkart', ['selling_price' => 260]);                                  // product-level only

        $service = app(MarketplaceComparison::class);
        $product->load('visibleMarketplacePrices');

        $forSmall = $service->offersFor($product, $small)->keyBy('marketplace');
        $forLarge = $service->offersFor($product, $large)->keyBy('marketplace');

        $this->assertEquals(250, $forSmall['amazon']->selling_price);   // fallback
        $this->assertEquals(399, $forLarge['amazon']->selling_price);   // variant-specific
        $this->assertEquals(260, $forLarge['flipkart']->selling_price); // fallback for a marketplace with no variant row
        $this->assertCount(2, $forSmall);
        $this->assertCount(2, $forLarge);
    }

    public function test_a_variant_specific_offer_never_leaks_onto_other_variants(): void
    {
        $product = $this->product();
        $one = $this->variant($product, 100, ['is_default' => true]);
        $two = $this->variant($product, 200);
        $this->offer($product, 'amazon', ['product_variant_id' => $two->id]);

        $product->load('visibleMarketplacePrices');
        $service = app(MarketplaceComparison::class);

        $this->assertCount(0, $service->offersFor($product, $one));
        $this->assertCount(1, $service->offersFor($product, $two));
        $this->assertNull($service->payload($product, $one, 'product_detail'));
    }

    public function test_best_price_is_claimed_only_when_strictly_lower_than_every_offer(): void
    {
        $product = $this->product();
        $variant = $this->variant($product, 449, ['is_default' => true]);
        $this->offer($product, 'amazon', ['selling_price' => 474]);
        $this->offer($product, 'flipkart', ['selling_price' => 459]);
        $product->load('visibleMarketplacePrices');
        $service = app(MarketplaceComparison::class);

        $this->assertTrue($service->payload($product, $variant, 'product_detail')['isBest']);

        $this->offer($product, 'meesho', ['selling_price' => 439]);              // cheaper elsewhere
        $product->load('visibleMarketplacePrices');
        $this->assertFalse($service->payload($product, $variant, 'product_detail')['isBest']);

        $tie = $this->product('Tie');
        $tieVariant = $this->variant($tie, 474, ['is_default' => true]);
        $this->offer($tie, 'amazon', ['selling_price' => 474]);
        $tie->load('visibleMarketplacePrices');
        $this->assertFalse($service->payload($tie, $tieVariant, 'product_detail')['isBest'], 'a tie must not be called "Best Price"');
    }

    // ------------------------------------------------------------------- Admin

    public function test_admin_can_create_edit_deactivate_reactivate_and_delete_a_listing(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $store = route('admin.catalog.products.marketplace-prices.store', $product);

        $this->actingAs($admin)->post($store, $this->payload())->assertRedirect()->assertSessionHas('success');
        $price = ProductMarketplacePrice::firstOrFail();
        $this->assertSame($product->id, $price->product_id);
        $this->assertEquals(474, $price->selling_price);
        $this->assertNotNull($price->last_checked_at, 'a new listing is stamped as just checked');

        $this->actingAs($admin)->put(route('admin.catalog.products.marketplace-prices.update', [$product, $price]), $this->payload(['selling_price' => '460']))->assertSessionHas('success');
        $this->assertEquals(460, $price->fresh()->selling_price);

        $this->actingAs($admin)->patch(route('admin.catalog.products.marketplace-prices.toggle', [$product, $price]))->assertSessionHas('success');
        $this->assertFalse($price->fresh()->is_active);
        $this->actingAs($admin)->patch(route('admin.catalog.products.marketplace-prices.toggle', [$product, $price]));
        $this->assertTrue($price->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.catalog.products.marketplace-prices.destroy', [$product, $price]))->assertSessionHas('success');
        $this->assertDatabaseMissing('product_marketplace_prices', ['id' => $price->id]);
    }

    public function test_editing_without_new_numbers_keeps_last_checked_and_a_price_change_restamps_it(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $price = $this->offer($product, 'amazon', ['last_checked_at' => now()->subDays(45)]);
        $url = route('admin.catalog.products.marketplace-prices.update', [$product, $price]);
        $stamp = $price->fresh()->last_checked_at;

        $this->actingAs($admin)->put($url, $this->payload(['marketplace_product_name' => 'Renamed', 'selling_price' => '474', 'mrp' => '799']));
        $this->assertTrue($price->fresh()->last_checked_at->equalTo($stamp), 'unchanged prices keep the old date');
        $this->assertTrue($price->fresh()->isDirty() === false);

        $this->actingAs($admin)->put($url, $this->payload(['selling_price' => '455']));
        $this->assertTrue($price->fresh()->last_checked_at->greaterThan($stamp));
        $this->assertFalse($price->fresh()->isStale());
    }

    public function test_stale_after_thirty_days(): void
    {
        $product = $this->product();
        $this->assertTrue($this->offer($product, 'amazon', ['last_checked_at' => now()->subDays(31)])->isStale());
        $this->assertFalse($this->offer($product, 'flipkart', ['last_checked_at' => now()->subDays(29)])->isStale());
        $this->assertTrue($this->offer($product, 'meesho', ['last_checked_at' => null])->isStale());
    }

    public function test_variant_specific_listing_can_be_created_and_duplicates_are_rejected(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $variant = $this->variant($product);
        $store = route('admin.catalog.products.marketplace-prices.store', $product);

        $this->actingAs($admin)->post($store, $this->payload(['product_variant_id' => $variant->id]))->assertSessionHas('success');
        $this->actingAs($admin)->post($store, $this->payload())->assertSessionHas('success'); // product-level Amazon alongside it
        $this->assertSame(2, ProductMarketplacePrice::count());

        $this->actingAs($admin)->post($store, $this->payload(['product_variant_id' => $variant->id]))->assertSessionHasErrors('marketplace', null, 'marketplace');
        $this->actingAs($admin)->post($store, $this->payload())->assertSessionHasErrors('marketplace', null, 'marketplace');
        $this->assertSame(2, ProductMarketplacePrice::count());
    }

    public function test_validation_rejects_unsafe_and_invalid_input(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $other = $this->product('Other');
        $foreignVariant = $this->variant($other);
        $store = route('admin.catalog.products.marketplace-prices.store', $product);

        $bad = [
            'javascript url' => ['product_url' => 'javascript:alert(1)'],
            'data url' => ['product_url' => 'data:text/html;base64,PHNjcmlwdD4='],
            'file url' => ['affiliate_url' => 'file:///etc/passwd'],
            'ftp url' => ['product_url' => 'ftp://amazon.in/x'],
            'javascript affiliate' => ['affiliate_url' => 'javascript:alert(1)'],
            'wrong marketplace domain' => ['product_url' => 'https://evil.example/amazon.in'],
            'lookalike domain' => ['product_url' => 'https://notamazon.in/dp/1'],
            'active without price' => ['selling_price' => ''],
            'negative price' => ['selling_price' => '-5'],
            'negative mrp' => ['mrp' => '-1'],
            'text price' => ['selling_price' => 'abc'],
            'unknown marketplace' => ['marketplace' => 'ebay'],
            'foreign variant' => ['product_variant_id' => $foreignVariant->id],
            'bad sort order' => ['display_order' => '-3'],
        ];

        foreach ($bad as $label => $override) {
            $response = $this->actingAs($admin)->post($store, $this->payload($override));
            $this->assertTrue(session('errors') && session('errors')->getBag('marketplace')->any(), "$label was accepted");
            $response->assertRedirect();
        }

        $this->assertSame(0, ProductMarketplacePrice::count());
    }

    public function test_an_affiliate_link_on_another_domain_and_an_inactive_draft_without_price_are_allowed(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $store = route('admin.catalog.products.marketplace-prices.store', $product);

        $this->actingAs($admin)->post($store, $this->payload(['affiliate_url' => 'https://tracker.affiliate-network.example/go?id=1']))->assertSessionHas('success');
        $this->actingAs($admin)->post($store, $this->payload(['marketplace' => 'meesho', 'is_active' => '0', 'selling_price' => '', 'mrp' => '', 'product_url' => 'https://www.meesho.com/x/p/1']))->assertSessionHas('success');
        $this->assertSame(2, ProductMarketplacePrice::count());

        // Enabling a priceless draft is refused until a price exists.
        $draft = ProductMarketplacePrice::where('marketplace', 'meesho')->first();
        $this->actingAs($admin)->patch(route('admin.catalog.products.marketplace-prices.toggle', [$product, $draft]))->assertSessionHas('error');
        $this->assertFalse($draft->fresh()->is_active);
    }

    public function test_selling_price_above_mrp_is_saved_with_a_warning_and_no_discount(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), $this->payload(['selling_price' => '900', 'mrp' => '799']))
            ->assertSessionHas('warning');

        $price = ProductMarketplacePrice::firstOrFail();
        $this->assertNull($price->discountPercent());
    }

    public function test_mass_assignment_cannot_move_a_listing_to_another_product_or_forge_the_scope(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $other = $this->product('Other');

        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), $this->payload([
            'product_id' => $other->id, 'variant_scope' => 99, 'id' => 12345,
        ]));

        $price = ProductMarketplacePrice::firstOrFail();
        $this->assertSame($product->id, $price->product_id);
        $this->assertSame(0, $price->variant_scope);
        $this->assertNotSame(12345, $price->id);
    }

    public function test_only_superadmins_can_manage_listings_and_a_listing_cannot_be_reached_through_another_product(): void
    {
        $product = $this->product();
        $other = $this->product('Other');
        $price = $this->offer($product);
        $store = route('admin.catalog.products.marketplace-prices.store', $product);
        $update = route('admin.catalog.products.marketplace-prices.update', [$product, $price]);
        $delete = route('admin.catalog.products.marketplace-prices.destroy', [$product, $price]);

        // guests and customers are turned away and nothing changes
        foreach ([null, User::factory()->create(['role' => 'customer'])] as $user) {
            $actor = $user ? $this->actingAs($user) : $this;
            $actor->post($store, $this->payload(['marketplace' => 'flipkart', 'product_url' => 'https://www.flipkart.com/x']))->assertRedirect();
            $actor->put($update, $this->payload(['selling_price' => '1']))->assertRedirect();
            $actor->delete($delete)->assertRedirect();
            $this->flushSession();
        }
        $this->assertSame(1, ProductMarketplacePrice::count());
        $this->assertEquals(474, $price->fresh()->selling_price);

        // an admin addressing the listing through the wrong product gets a 404
        $this->actingAs($this->admin())->delete(route('admin.catalog.products.marketplace-prices.destroy', [$other, $price]))->assertNotFound();
        $this->assertSame(1, ProductMarketplacePrice::count());
    }

    public function test_product_edit_page_shows_the_summary_cards_and_the_list_shows_a_compact_indicator(): void
    {
        $admin = $this->admin();
        $product = $this->product('Indicator Product');
        $this->variant($product, 100, ['is_default' => true]);
        $this->offer($product, 'amazon', ['last_checked_at' => now()->subDays(60)]);
        $this->offer($product, 'flipkart', ['is_active' => false]);
        MarketplaceClick::create(['product_id' => $product->id, 'product_marketplace_price_id' => ProductMarketplacePrice::first()->id, 'marketplace' => 'amazon', 'source' => 'shop']);

        $edit = $this->actingAs($admin)->get(route('admin.catalog.products.edit', $product))->assertOk();
        $edit->assertSee('Marketplace Pricing')->assertSee('Add Marketplace')->assertSee('Price may be outdated')->assertSee('1 click')->assertSee('Inactive');

        $this->actingAs($admin)->get(route('admin.catalog.products.index'))->assertOk()->assertSee('1 Active', false);

        $noOffers = $this->product('Plain');
        $this->actingAs($admin)->get(route('admin.catalog.products.index'))->assertDontSee('0 Active');
        $this->actingAs($admin)->get(route('admin.catalog.products.edit', $noOffers))->assertOk()->assertSee('No marketplace prices yet');
    }

    // ---------------------------------------------------------------- Storefront

    public function test_no_offers_means_no_comparison_section_button_or_scripts(): void
    {
        $product = $this->product('Plain Product');
        $this->variant($product, 449, ['is_default' => true]);

        $page = $this->get(route('products.show', $product->slug))->assertOk();
        $page->assertDontSee('Compare prices')->assertDontSee('id="compare-prices"', false)->assertDontSee('data-compare-variant', false);

        $this->get(route('shop'))->assertOk()->assertDontSee('data-compare-open', false)->assertDontSee('cards-md__compare"', false);
    }

    public function test_only_the_active_marketplaces_are_rendered_on_the_product_page(): void
    {
        $product = $this->product('Bamboo Water Bottle');
        $this->variant($product, 449, ['is_default' => true]);
        $amazon = $this->offer($product, 'amazon');

        $html = $this->get(route('products.show', $product->slug))->assertOk()->getContent();
        $this->assertStringContainsString('id="compare-prices"', $html);
        $this->assertStringContainsString('Amazon', $html);
        $this->assertStringNotContainsString('Flipkart', $html);
        $this->assertStringNotContainsString('Meesho', $html);

        $this->offer($product, 'flipkart', ['selling_price' => 459]);
        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('Flipkart', $html);
        $this->assertStringNotContainsString('Meesho', $html);

        $this->offer($product, 'meesho', ['selling_price' => 439, 'mrp' => 699]);
        $html = $this->get(route('products.show', $product->slug))->getContent();
        foreach (['Amazon', 'Flipkart', 'Meesho', '41% OFF', '43% OFF', '37% OFF', '₹474.00', '₹459.00', '₹439.00', '₹799.00'] as $expected) {
            $this->assertStringContainsString($expected, $html, "missing $expected");
        }

        // deactivate → hidden, reactivate → shown
        $amazon->update(['is_active' => false]);
        $this->assertStringNotContainsString('data-marketplace="amazon"', $this->get(route('products.show', $product->slug))->getContent());
        $amazon->update(['is_active' => true]);
        $this->assertStringContainsString('data-marketplace="amazon"', $this->get(route('products.show', $product->slug))->getContent());

        // delete → gone
        $amazon->delete();
        $this->assertStringNotContainsString('data-marketplace="amazon"', $this->get(route('products.show', $product->slug))->getContent());
    }

    public function test_the_page_never_prints_a_marketplace_destination_only_our_redirect_and_safe_link_attributes(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $offer = $this->offer($product, 'amazon', ['product_url' => 'https://www.amazon.in/dp/RAWPRODUCT', 'affiliate_url' => 'https://amzn.to/AFFILIATE']);

        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringNotContainsString('RAWPRODUCT', $html);
        $this->assertStringNotContainsString('AFFILIATE', $html);
        $this->assertStringContainsString('/out/marketplace/'.$offer->id.'?src=product_detail', str_replace('&amp;', '&', $html));
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer nofollow sponsored"', $html);
    }

    public function test_official_store_is_always_shown_and_best_price_only_when_true(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $this->offer($product, 'amazon', ['selling_price' => 474]);

        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('Official Store', $html);
        $this->assertStringContainsString('Best Price', $html);
        $this->assertStringContainsString('Add to Cart', $html);

        $this->offer($product, 'flipkart', ['selling_price' => 400]);
        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('Official Store', $html);
        $this->assertStringNotContainsString('compare__badge--best', $html);
    }

    public function test_disclaimer_is_present_and_marketplace_prices_are_not_called_tax_inclusive(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $this->offer($product, 'amazon');

        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('Marketplace prices and availability may change. Please verify the final price on the respective marketplace.', $html);
        $section = substr($html, (int) strpos($html, 'id="compare-prices"'));
        $section = substr($section, 0, (int) strpos($section, 'products__content-trust'));
        // "Inclusive of all taxes" is printed on our own card only — once per rendered block (in place + the variant <template>).
        $this->assertSame(substr_count($section, 'compare__card--own'), substr_count($section, 'Inclusive of all taxes'));
        $this->assertGreaterThan(0, substr_count($section, 'compare__card--own'));
    }

    public function test_variant_switching_data_ships_a_block_per_variant_with_the_right_prices(): void
    {
        $product = $this->product('Rose Floor Cleaner');
        $small = $this->variant($product, 199, ['variant_name' => '500ml', 'is_default' => true]);
        $large = $this->variant($product, 349, ['variant_name' => '1L']);
        $bare = $this->variant($product, 99, ['variant_name' => 'Sample']);
        $this->offer($product, 'amazon', ['selling_price' => 250]);
        $this->offer($product, 'amazon', ['selling_price' => 399, 'mrp' => 500, 'product_variant_id' => $large->id]);
        // make the product-level fallback not apply to "Sample" by using a variant-only listing for another marketplace
        $this->offer($product, 'flipkart', ['selling_price' => 410, 'product_variant_id' => $large->id]);

        $html = $this->get(route('products.show', $product->slug))->getContent();

        preg_match_all('~<template data-compare-variant="(\d+)">(.*?)</template>~s', $html, $m, PREG_SET_ORDER);
        $blocks = collect($m)->mapWithKeys(fn ($x) => [(int) $x[1] => $x[2]]);

        $this->assertEqualsCanonicalizing([$small->id, $large->id, $bare->id], $blocks->keys()->all());
        $this->assertStringContainsString('₹250.00', $blocks[$small->id]);
        $this->assertStringNotContainsString('Flipkart', $blocks[$small->id]);
        $this->assertStringContainsString('₹399.00', $blocks[$large->id]);
        $this->assertStringContainsString('₹410.00', $blocks[$large->id]);
        $this->assertStringNotContainsString('₹250.00', $blocks[$large->id]);
        // the default variant's block is also rendered in place, so it works without JS
        $this->assertStringContainsString('₹250.00', $html);
    }

    public function test_variants_without_any_offer_have_no_block_and_a_product_with_only_variant_offers_hides_the_default_block(): void
    {
        $product = $this->product();
        $default = $this->variant($product, 100, ['is_default' => true]);
        $other = $this->variant($product, 200);
        $this->offer($product, 'amazon', ['product_variant_id' => $other->id]);

        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('data-compare-variant="'.$other->id.'"', $html);
        $this->assertStringNotContainsString('data-compare-variant="'.$default->id.'"', $html);
        // the section exists (JS reveals it for the other variant) but is hidden for the default one
        $this->assertMatchesRegularExpression('~<section class="compare compare--pdp" id="compare-prices"[^>]*\shidden~', $html);
    }

    public function test_shop_cards_get_a_compact_trigger_only_when_they_have_offers_and_no_prices_inline(): void
    {
        $with = $this->product('Has Offers');
        $this->variant($with, 449, ['is_default' => true]);
        $this->offer($with, 'amazon', ['selling_price' => 474]);
        $this->offer($with, 'flipkart', ['selling_price' => 459]);
        $without = $this->product('No Offers');
        $this->variant($without, 100, ['is_default' => true]);

        $html = $this->get(route('shop'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-compare-open'));
        $this->assertStringContainsString('Compare prices', $html);
        $this->assertStringContainsString('2 stores', $html);
        $this->assertStringContainsString('src=shop', str_replace('&amp;', '&', $html));
        // only the trigger + a <template> carry the offers; there is no permanent dialog markup or duplicate ids
        $this->assertStringNotContainsString('<dialog', $html);
        $this->assertSame(1, substr_count($html, '<template data-compare-template'));
    }

    public function test_related_products_use_the_related_source(): void
    {
        $category = $this->category();
        $main = $this->product('Main', ['category_id' => $category->id]);
        $this->variant($main, 100, ['is_default' => true]);
        $related = $this->product('Related One', ['category_id' => $category->id]);
        $this->variant($related, 120, ['is_default' => true]);
        $this->offer($related, 'meesho', ['selling_price' => 130]);

        $html = str_replace('&amp;', '&', $this->get(route('products.show', $main->slug))->getContent());

        $this->assertStringContainsString('src=related_products', $html);
        $this->assertStringContainsString('data-compare-open', $html);
    }

    public function test_marketplace_offers_are_eager_loaded_so_queries_do_not_grow_with_the_product_count(): void
    {
        $category = $this->category();
        $make = function (int $i) use ($category) {
            $p = $this->product("Product $i", ['category_id' => $category->id]);
            $this->variant($p, 100 + $i, ['is_default' => true]);
            $this->offer($p, 'amazon', ['selling_price' => 200 + $i]);
        };
        $measure = function () {
            $count = 0;
            $listener = function ($query) use (&$count) {
                if (str_contains($query->sql, 'product_marketplace_prices')) {
                    $count++;
                }
            };
            DB::listen($listener);
            $this->get(route('shop'))->assertOk();

            return $count;
        };

        foreach ([1, 2] as $i) {
            $make($i);
        }
        $few = $measure();

        foreach ([3, 4, 5, 6, 7, 8] as $i) {
            $make($i);
        }
        $many = $measure(); // each measure() has its own counter, so this is the second page load only

        $this->assertGreaterThan(0, $few);
        $this->assertSame($few, $many, 'marketplace queries must not scale with the number of products (no N+1)');
    }

    public function test_the_logo_falls_back_to_a_wordmark_and_uses_an_uploaded_file_when_present(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $this->offer($product, 'amazon');

        Marketplaces::flushLogoCache();
        $html = $this->get(route('products.show', $product->slug))->getContent();
        $this->assertStringContainsString('compare__mark--text', $html);
        $this->assertStringNotContainsString('compare__mark-img', $html);

        $dir = public_path('images/marketplaces');
        $file = $dir.'/amazon.svg';
        $createdDir = ! is_dir($dir);
        if ($createdDir) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"/>');

        try {
            Marketplaces::flushLogoCache();
            $html = $this->get(route('products.show', $product->slug))->getContent();
            $this->assertStringContainsString('compare__mark--logo', $html);
            $this->assertStringContainsString('images/marketplaces/amazon.svg', $html);
        } finally {
            @unlink($file);
            if ($createdDir) {
                @rmdir($dir);
            }
            Marketplaces::flushLogoCache();
        }
    }

    // ------------------------------------------------------------- Redirect / clicks

    public function test_the_redirect_goes_only_to_the_stored_url_and_logs_a_click_without_personal_data(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $offer = $this->offer($product, 'amazon', ['product_url' => 'https://www.amazon.in/dp/PRODUCT', 'affiliate_url' => 'https://amzn.to/AFF']);

        $response = $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id, 'src' => 'shop', 'url' => 'https://evil.example']));

        $response->assertRedirect('https://amzn.to/AFF');
        $this->assertStringContainsString('noindex', (string) $response->headers->get('X-Robots-Tag'));
        $click = MarketplaceClick::firstOrFail();
        $this->assertSame([$product->id, $offer->id, 'amazon', 'shop'], [$click->product_id, $click->product_marketplace_price_id, $click->marketplace, $click->source]);
        $this->assertEqualsCanonicalizing(['id', 'product_id', 'product_marketplace_price_id', 'marketplace', 'source', 'created_at'], Schema::getColumnListing('marketplace_clicks'));

        $offer->update(['affiliate_url' => null]);
        $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id]))->assertRedirect('https://www.amazon.in/dp/PRODUCT');
        $this->assertSame('product_card', MarketplaceClick::latest('id')->first()->source, 'a missing src defaults safely');

        $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id, 'src' => '<script>']));
        $this->assertSame('product_card', MarketplaceClick::latest('id')->first()->source, 'an unknown src is never stored');
    }

    public function test_the_redirect_refuses_inactive_unsafe_unpriced_or_hidden_product_listings(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $inactive = $this->offer($product, 'amazon', ['is_active' => false]);
        $unsafe = $this->offer($product, 'flipkart', ['product_url' => 'javascript:alert(1)', 'affiliate_url' => null]);
        $unpriced = $this->offer($product, 'meesho', ['selling_price' => null]);

        foreach ([$inactive, $unsafe, $unpriced] as $offer) {
            $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id]))->assertNotFound();
        }
        $this->get('/out/marketplace/999999')->assertNotFound();
        $this->get('/out/marketplace/abc')->assertNotFound();

        $draftProduct = $this->product('Draft', ['status' => 'draft']);
        $draftOffer = $this->offer($draftProduct, 'amazon');
        $this->get(route('marketplace.out', ['marketplacePrice' => $draftOffer->id]))->assertNotFound();

        $this->assertSame(0, MarketplaceClick::count());
    }

    public function test_a_click_logging_failure_never_blocks_the_redirect(): void
    {
        $product = $this->product();
        $offer = $this->offer($product, 'amazon');
        Schema::drop('marketplace_clicks');

        $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id]))->assertRedirect($offer->product_url);
    }

    public function test_deleting_a_listing_keeps_its_click_history_but_detaches_it(): void
    {
        $product = $this->product();
        $offer = $this->offer($product, 'amazon');
        $this->get(route('marketplace.out', ['marketplacePrice' => $offer->id]));

        $offer->delete();

        $click = MarketplaceClick::firstOrFail();
        $this->assertNull($click->product_marketplace_price_id);
        $this->assertSame('amazon', $click->marketplace);
    }

    public function test_product_page_output_stays_escaped_for_hostile_marketplace_text(): void
    {
        $product = $this->product();
        $this->variant($product, 449, ['is_default' => true]);
        $this->offer($product, 'amazon', ['marketplace_product_name' => '<script>alert(1)</script>']);

        $this->get(route('products.show', $product->slug))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }
}
