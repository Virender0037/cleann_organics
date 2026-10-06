<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Rules\VariantMediaFile;
use App\Support\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regression coverage for the October 2026 commerce QA pass: cart/variant links, the mobile cart drawer's empty
 * state, inclusive coupon end dates, empty-cart shipping, marketplace listings without a destination, marketplace
 * prices never reaching the cart, tampering, duplicate Place Order, upload type checks and blog HTML sanitising.
 */
class CommerceQaRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Home Care', 'slug' => 'home-care', 'status' => 'active']);
    }

    private function product(string $name = 'Floor Cleaner'): Product
    {
        return Product::create([
            'category_id' => $this->category->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product, string $name, float $price, int $stock = 10, bool $default = false, int $sort = 0): ProductVariant
    {
        return $product->variants()->create([
            'variant_name' => $name,
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => $price,
            'stock_quantity' => $stock,
            'low_stock_quantity' => 1,
            'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
            'is_default' => $default,
            'status' => 'active',
            'sort_order' => $sort,
        ]);
    }

    private function customerWithAddress(): array
    {
        $user = User::factory()->create(['role' => 'customer']);
        $address = Address::create([
            'user_id' => $user->id, 'type' => 'shipping', 'name' => 'Test', 'phone' => '9876543210',
            'address_line_1' => '1 Street', 'city' => 'Pune', 'state' => 'Maharashtra', 'country' => 'India',
            'pincode' => '411001', 'is_default' => true,
        ]);

        return [$user, $address];
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'CODE'.uniqid(), 'type' => 'fixed', 'value' => 20, 'minimum_order_amount' => 0,
            'start_date' => now()->subDays(5)->startOfDay(), 'end_date' => now()->addDays(5)->startOfDay(), 'status' => 'active',
        ], $overrides));
    }

    // ------------------------------------------------------------ Cart links / variant

    public function test_cart_lines_link_to_the_product_page_on_their_own_variant_on_desktop_and_mobile(): void
    {
        $product = $this->product();
        $this->variant($product, 'Pack of 1', 200, 10, true);
        $pack4 = $this->variant($product, 'Pack of 4', 450, 10, false, 2);

        $this->post(route('cart.items.store'), ['product_variant_id' => $pack4->id, 'quantity' => 1]);

        $url = route('products.show', ['slug' => $product->slug, 'variant' => $pack4->id]);
        $html = $this->get(route('shopping-cart'))->assertOk()->getContent();

        // Desktop table (image+name link), mobile card image link and mobile card name link.
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.e($url).'"'));
        $this->assertStringContainsString('Pack of 4', $html);
    }

    public function test_product_page_preselects_the_variant_from_the_query_string(): void
    {
        $product = $this->product();
        $this->variant($product, 'Pack of 1', 200, 10, true);
        $pack4 = $this->variant($product, 'Pack of 4', 450, 10, false, 2);

        $this->get(route('products.show', ['slug' => $product->slug, 'variant' => $pack4->id]))
            ->assertOk()
            ->assertViewHas('defaultVariant', fn ($v) => $v->id === $pack4->id)
            ->assertSee('id="add-to-cart-variant-id" value="'.$pack4->id.'"', false)
            ->assertSee('₹450.00');
    }

    public function test_a_variant_from_another_product_in_the_query_string_is_ignored(): void
    {
        $product = $this->product();
        $default = $this->variant($product, 'Pack of 1', 200, 10, true);
        $foreign = $this->variant($this->product('Other'), 'Other pack', 999, 10, true);

        $this->get(route('products.show', ['slug' => $product->slug, 'variant' => $foreign->id]))
            ->assertOk()
            ->assertViewHas('defaultVariant', fn ($v) => $v->id === $default->id);
    }

    public function test_same_variant_merges_and_different_variants_stay_separate_lines(): void
    {
        $product = $this->product();
        $pack2 = $this->variant($product, 'Pack of 2', 380, 10, true);
        $pack4 = $this->variant($product, 'Pack of 4', 450, 10);

        $this->postJson(route('cart.items.store'), ['product_variant_id' => $pack2->id, 'quantity' => 1]);
        $this->postJson(route('cart.items.store'), ['product_variant_id' => $pack2->id, 'quantity' => 1]);
        $last = $this->postJson(route('cart.items.store'), ['product_variant_id' => $pack4->id, 'quantity' => 1])->assertOk();

        $this->assertSame([$pack2->id => 2, $pack4->id => 1], session('cart'));
        $this->assertSame(3, $last->json('itemCount'));
        $this->assertEquals(380 * 2 + 450, $last->json('subtotal'));
    }

    // ------------------------------------------------------------ Mini-cart drawer / empty cart

    public function test_empty_mini_cart_offers_continue_shopping_and_no_dead_checkout_button(): void
    {
        $product = $this->product();
        $variant = $this->variant($product, 'Pack of 1', 200, 10, true);
        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->deleteJson(route('cart.items.destroy', $variant->id))->assertOk();
        $html = $response->json('miniCartHtml');

        $this->assertSame(0, $response->json('itemCount'));
        $this->assertStringContainsString('Your cart is empty.', $html);
        $this->assertStringContainsString('href="'.route('shop').'"', $html);
        $this->assertStringContainsString('Continue Shopping', $html);
        $this->assertStringNotContainsString('>Checkout<', $html);
        // The X is still rendered — main.js closes it through a delegated listener, so the re-rendered button works.
        $this->assertStringContainsString('class="close"', $html);
    }

    public function test_main_js_closes_the_cart_drawer_through_a_delegated_listener(): void
    {
        $js = file_get_contents(public_path('js/main.js'));

        $this->assertStringNotContainsString("closeBtn.addEventListener('click'", $js);
        $this->assertStringContainsString("event.target.closest('.shopping-cart .close')", $js);
        $this->assertStringContainsString("event.key === 'Escape'", $js);
    }

    public function test_empty_cart_page_shows_no_shipping_charge_even_with_a_flat_charge_configured(): void
    {
        Setting::setMany('storefront', ['free_shipping_threshold' => '399', 'flat_shipping_charge' => '60']);

        $this->get(route('shopping-cart'))
            ->assertOk()
            ->assertViewHas('shippingEstimate', 0.0)
            ->assertSee('Continue Shopping');
    }

    public function test_flat_shipping_applies_below_the_threshold_and_is_free_at_it(): void
    {
        Setting::setMany('storefront', ['free_shipping_threshold' => '399', 'flat_shipping_charge' => '60']);
        $product = $this->product();
        $variant = $this->variant($product, 'Pack of 1', 200, 10, true);

        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->get(route('shopping-cart'))->assertViewHas('shippingEstimate', 60.0);

        $this->patch(route('cart.items.update', $variant->id), ['quantity' => 2]); // ₹400 ≥ ₹399
        $this->get(route('shopping-cart'))->assertViewHas('shippingEstimate', 0.0);
    }

    // ------------------------------------------------------------ Coupons: inclusive end date

    public function test_a_coupon_stays_valid_for_the_whole_of_its_end_date(): void
    {
        $this->travelTo(now()->setTime(15, 0));
        $coupon = $this->coupon(['code' => 'LASTDAY', 'end_date' => now()->startOfDay()]); // as saved by the date input
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 200, 10, true);
        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post(route('checkout.coupon.apply'), ['code' => 'LASTDAY'])->assertSessionHas('success');
        $this->assertTrue($coupon->isWithinValidity());

        $this->travelTo(now()->addDay()->setTime(0, 0, 1));
        $this->assertTrue($coupon->fresh()->isExpired());
    }

    public function test_a_single_day_coupon_works_on_that_day(): void
    {
        $this->travelTo(now()->setTime(11, 30));
        $this->coupon(['code' => 'ONEDAY', 'start_date' => now()->startOfDay(), 'end_date' => now()->startOfDay()]);
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 200, 10, true);
        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post(route('checkout.coupon.apply'), ['code' => 'ONEDAY'])->assertSessionHas('success');
    }

    public function test_coupon_ended_yesterday_is_rejected_and_listed_as_expired_by_admin(): void
    {
        $this->coupon(['code' => 'ENDED', 'end_date' => now()->subDay()->startOfDay()]);
        $today = $this->coupon(['code' => 'ENDSTODAY', 'end_date' => now()->startOfDay()]);
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 200, 10, true);
        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post(route('checkout.coupon.apply'), ['code' => 'ENDED'])->assertSessionHas('error');

        $expired = Coupon::query()->expired()->pluck('code')->all();
        $this->assertSame(['ENDED'], $expired);
        $this->assertFalse($today->isExpired());
    }

    public function test_coupon_discount_recalculates_when_quantity_changes(): void
    {
        $this->coupon(['code' => 'TENPC', 'type' => 'percentage', 'value' => 10]);
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 200, 10, true);

        $this->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post(route('checkout.coupon.apply'), ['code' => 'TENPC']);
        $this->get(route('shopping-cart'))->assertViewHas('discountAmount', 20.0);

        $this->patch(route('cart.items.update', $variant->id), ['quantity' => 3]);
        $this->get(route('shopping-cart'))->assertViewHas('discountAmount', 60.0)->assertViewHas('subtotal', 600.0);
    }

    // ------------------------------------------------------------ Marketplace

    public function test_marketplace_prices_never_become_the_cart_price(): void
    {
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 450, 10, true);
        foreach (['amazon' => 474, 'flipkart' => 430, 'meesho' => 420] as $key => $price) {
            $product->marketplacePrices()->create(['marketplace' => $key, 'selling_price' => $price, 'product_url' => "https://www.{$key}.com/p/1", 'is_active' => true]);
        }

        $response = $this->postJson(route('cart.items.store'), [
            'product_variant_id' => $variant->id, 'quantity' => 1,
            // Tampered extras: none of these may be read.
            'price' => 1, 'unit_price' => 1, 'marketplace' => 'meesho', 'subtotal' => 1,
        ])->assertOk();

        $this->assertEquals(450, $response->json('cartTotal'));
        $this->get(route('shopping-cart'))->assertViewHas('subtotal', 450.0);
    }

    public function test_an_active_listing_needs_a_destination_url_and_a_positive_price(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $product = $this->product();
        $base = ['marketplace' => 'amazon', 'product_variant_id' => '', 'selling_price' => '474', 'mrp' => '599', 'product_url' => '', 'affiliate_url' => '', 'is_active' => '1', 'display_order' => '0'];

        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), $base)
            ->assertSessionHasErrorsIn('marketplace', ['product_url']);
        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), ['selling_price' => '0', 'product_url' => 'https://www.amazon.in/dp/X'] + $base)
            ->assertSessionHasErrorsIn('marketplace', ['selling_price']);
        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), ['selling_price' => '-5', 'product_url' => 'https://www.amazon.in/dp/X'] + $base)
            ->assertSessionHasErrorsIn('marketplace', ['selling_price']);
        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), ['product_url' => 'https://evil.example/amazon'] + $base)
            ->assertSessionHasErrorsIn('marketplace', ['product_url']);
        $this->assertSame(0, ProductMarketplacePrice::count());

        // An inactive draft without a URL is still allowed, but cannot be enabled until it has one.
        $this->actingAs($admin)->post(route('admin.catalog.products.marketplace-prices.store', $product), ['is_active' => '0'] + $base)
            ->assertSessionHasNoErrors();
        $draft = ProductMarketplacePrice::firstOrFail();
        $this->actingAs($admin)->patch(route('admin.catalog.products.marketplace-prices.toggle', [$product, $draft]))->assertSessionHas('error');
        $this->assertFalse($draft->fresh()->is_active);
    }

    public function test_a_legacy_active_listing_without_a_url_is_not_shown_to_customers(): void
    {
        $product = $this->product();
        $this->variant($product, 'Pack', 450, 10, true);
        $product->marketplacePrices()->create(['marketplace' => 'flipkart', 'selling_price' => 430, 'product_url' => null, 'is_active' => true]);

        $this->get(route('products.show', $product->slug))->assertOk()->assertDontSee('id="compare-prices"', false);
    }

    public function test_marketplace_redirect_goes_to_the_stored_url_for_each_marketplace(): void
    {
        $product = $this->product();
        $this->variant($product, 'Pack', 450, 10, true);
        $urls = ['amazon' => 'https://www.amazon.in/dp/QA1', 'flipkart' => 'https://www.flipkart.com/qa/p/itm1', 'meesho' => 'https://www.meesho.com/qa/p/1'];

        foreach ($urls as $key => $url) {
            $row = $product->marketplacePrices()->create(['marketplace' => $key, 'selling_price' => 400, 'product_url' => $url, 'is_active' => true]);
            $this->get(route('marketplace.out', ['marketplacePrice' => $row->id, 'src' => 'product_detail']).'&url=https://evil.example')
                ->assertRedirect($url);
        }
    }

    // ------------------------------------------------------------ Tampering / stock

    public function test_quantity_tampering_is_rejected_or_clamped_server_side(): void
    {
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 200, 3, true);

        foreach ([-1, 0, 999999, 'abc'] as $bad) {
            $this->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => $bad])->assertStatus(422);
        }

        $ok = $this->postJson(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 4])->assertOk();
        $this->assertSame(3, $ok->json('itemCount'));
        $this->assertStringContainsString('Only 3 in stock', $ok->json('message'));

        $this->patchJson(route('cart.items.update', $variant->id), ['quantity' => 0])->assertStatus(422);
        $this->patchJson(route('cart.items.update', $variant->id), ['quantity' => 50])->assertOk();
        $this->assertSame([$variant->id => 3], session('cart'));
    }

    public function test_order_totals_come_from_the_server_not_the_request(): void
    {
        Setting::setMany('storefront', ['free_shipping_threshold' => '399', 'flat_shipping_charge' => '60']);
        [$user, $address] = $this->customerWithAddress();
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 150, 10, true);

        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 2]);
        $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id, 'payment_method' => 'cod',
            'grand_total' => 1, 'subtotal' => 1, 'shipping_amount' => 0, 'discount_amount' => 999, 'unit_price' => 1,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertEquals(300, $order->subtotal);
        $this->assertEquals(60, $order->shipping_amount);
        $this->assertEquals(0, $order->discount_amount);
        $this->assertEquals(360, $order->grand_total);
        $this->assertEquals(150, $order->items()->first()->unit_price);
    }

    public function test_a_repeated_place_order_submit_lands_on_the_first_order_and_creates_no_second_one(): void
    {
        [$user, $address] = $this->customerWithAddress();
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 450, 10, true);
        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $first = $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::firstOrFail();
        $first->assertRedirect(route('orders.show', $order));

        // The double-click's second request arrives after the first one consumed the cart.
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(1, Order::count());
        $this->assertSame(9, $variant->fresh()->stock_quantity);
    }

    public function test_checkout_page_disables_place_order_after_the_first_submit(): void
    {
        [$user] = $this->customerWithAddress();
        $product = $this->product();
        $variant = $this->variant($product, 'Pack', 450, 10, true);
        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->actingAs($user)->get(route('checkout'))->assertOk()->assertSee("form.dataset.submitting === '1'", false);
    }

    public function test_injection_looking_search_input_is_safe(): void
    {
        $product = $this->product('Dish Wash');
        $this->variant($product, 'Pack', 150, 10, true);

        foreach (["'", '"', "' OR '1'='1", '1 OR 1=1', "admin'--", "%' OR '1'='1", '<script>alert(1)</script>', '<img src=x onerror=alert(1)>', str_repeat('a', 2000)] as $term) {
            $html = $this->get(route('shop', ['search' => $term]))->assertOk()->getContent();
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
            $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
            // No condition was bypassed: an always-true payload must not list unrelated products.
            if (str_contains($term, 'OR')) {
                $this->assertStringNotContainsString('Dish Wash</h6>', $html);
            }
        }
    }

    // ------------------------------------------------------------ Uploads / XSS

    public function test_variant_media_rejects_an_svg_disguised_as_a_jpeg(): void
    {
        // A real file (fake uploads report a MIME type from the extension, not from the bytes).
        $path = tempnam(sys_get_temp_dir(), 'svg');
        file_put_contents($path, '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect width="10" height="10"/></svg>');
        $svg = new UploadedFile($path, 'photo.jpg', null, null, true);
        $this->assertSame('image/svg+xml', $svg->getMimeType());
        $png = UploadedFile::fake()->image('photo.png', 20, 20);

        $this->assertTrue(Validator::make(['f' => $svg], ['f' => [new VariantMediaFile]])->fails());
        $this->assertFalse(Validator::make(['f' => $png], ['f' => [new VariantMediaFile]])->fails());
    }

    public function test_safe_html_keeps_formatting_and_strips_scripts_handlers_and_unsafe_urls(): void
    {
        $this->assertSame('<p>Hello <strong>world</strong></p>', SafeHtml::clean('<p>Hello <strong>world</strong></p>'));

        $clean = SafeHtml::clean('<script>alert(1)</script><p onclick="x()" style="c">t</p><img src=x onerror=alert(1)>'
            .'<a href="javascript:alert(1)">a</a><a href="java&#9;script:alert(1)">b</a><a href="https://ok.example/x">c</a>'
            .'<iframe src="https://e.example"></iframe><svg onload=alert(1)></svg><a href="/blog">d</a>');

        foreach (['<script', 'onclick', 'onerror', 'javascript:', 'script:', '<iframe', '<svg', 'style='] as $needle) {
            $this->assertStringNotContainsStringIgnoringCase($needle, $clean);
        }
        $this->assertStringContainsString('href="https://ok.example/x"', $clean);
        $this->assertStringContainsString('href="/blog"', $clean);
        $this->assertStringContainsString('<p>t</p>', $clean);
    }

    public function test_blog_body_is_rendered_without_executable_markup(): void
    {
        $category = BlogCategory::create(['name' => 'News', 'slug' => 'news', 'status' => 'active']);
        $blog = Blog::create([
            'blog_category_id' => $category->id,
            'title' => 'Safe post', 'slug' => 'safe-post', 'excerpt' => 'x', 'status' => 'published', 'published_at' => now()->subDay(),
            'content' => '<p>Real paragraph</p><script>alert("xss")</script><img src=x onerror=alert(1)>',
        ]);

        $this->get(route('singleblog', $blog->slug))
            ->assertOk()
            ->assertSee('<p>Real paragraph</p>', false)
            ->assertDontSee('alert("xss")', false)
            ->assertDontSee('onerror', false);
    }
}
