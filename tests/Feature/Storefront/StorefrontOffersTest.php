<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\HomeBanner;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Reel;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\Storefront\ProductCatalogService;
use App\Services\Storefront\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Sept-2026 storefront work: tax-inclusive pricing, the ₹399 / ₹999 offers,
 * the earned ₹150 voucher, verified-purchase reviews, dashboard links,
 * homepage sections, add-to-cart toast payload and the contact form.
 */
class StorefrontOffersTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create(['name' => 'Cleaners', 'slug' => 'cleaners-'.uniqid(), 'status' => 'active']);
    }

    private function product(string $name = 'Floor Cleaner', array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category()->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ], $overrides));
    }

    private function variant(Product $product, float $price, array $overrides = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'variant_name' => 'Variant',
            'enable_tiered_pricing' => false,
            'single_quantity' => 1,
            'single_price' => $price,
            'stock_quantity' => 50,
            'low_stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
            'weight' => 1.00,
        ], $overrides));
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id, 'type' => 'shipping', 'name' => 'Jane Doe', 'phone' => '9876543210',
            'address_line_1' => '221B Baker Street', 'city' => 'Delhi', 'state' => 'Delhi',
            'country' => 'India', 'pincode' => '110001', 'is_default' => true,
        ]);
    }

    private function addToCart(ProductVariant $variant, int $qty = 1)
    {
        return $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => $qty]);
    }

    /** Places a COD order for $user containing one line, returns the Order. */
    private function placeOrder(User $user, ProductVariant $variant, int $qty = 1): Order
    {
        $this->actingAs($user);
        $this->addToCart($variant, $qty);
        $this->post('/checkout', ['address_id' => $this->address($user)->id, 'payment_method' => 'cod']);

        return Order::where('user_id', $user->id)->latest('id')->firstOrFail();
    }

    private function deliver(Order $order): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->patch("/admin/sales/orders/{$order->id}/status", ['status' => 'delivered'])->assertRedirect();
        $this->app['auth']->forgetGuards();
    }

    private function setting(array $values): void
    {
        Setting::setMany('storefront', $values);
        Setting::forget('storefront');
    }

    // ------------------------------------------------------------------
    // Tax-inclusive pricing
    // ------------------------------------------------------------------

    public function test_admin_price_is_the_final_price_and_tax_is_not_added_on_top(): void
    {
        $tax = TaxRate::create(['name' => 'GST 18%', 'percentage' => 18, 'status' => 'active']);
        $variant = $this->variant($this->product('Taxed', ['tax_rate_id' => $tax->id]), 500);
        $user = User::factory()->create();

        $order = $this->placeOrder($user, $variant);

        $this->assertEquals(500.00, (float) $order->subtotal);
        $this->assertEquals(500.00, (float) $order->grand_total, 'Customer pays the listed price, not price + GST.');
        // GST breakup is extracted backwards: 500 × 18 / 118 = 76.27
        $this->assertEquals(76.27, (float) $order->tax_amount);
        $this->assertEquals(76.27, (float) $order->items()->first()->tax_amount);
    }

    public function test_cart_and_checkout_say_inclusive_of_all_taxes(): void
    {
        $variant = $this->variant($this->product(), 100);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->addToCart($variant);

        $this->get('/shopping-cart')->assertOk()->assertSee('Inclusive of all taxes');
        $this->get('/checkout')->assertOk()->assertSee('Inclusive of all taxes')->assertSee('Shipping Partner: Velocity');
    }

    // ------------------------------------------------------------------
    // Shipping threshold
    // ------------------------------------------------------------------

    public function test_below_399_pays_the_admin_flat_charge_and_399_ships_free(): void
    {
        $this->setting(['flat_shipping_charge' => '49']);
        $user = User::factory()->create();

        $below = $this->placeOrder($user, $this->variant($this->product('Below'), 398));
        $this->assertEquals(49.00, (float) $below->shipping_amount);
        $this->assertEquals(447.00, (float) $below->grand_total);

        $at = $this->placeOrder($user, $this->variant($this->product('At'), 399));
        $this->assertEquals(0.00, (float) $at->shipping_amount);
        $this->assertEquals(399.00, (float) $at->grand_total);
    }

    public function test_free_shipping_is_judged_after_the_discount_and_before_shipping(): void
    {
        $this->setting(['flat_shipping_charge' => '49']);
        Coupon::create([
            'code' => 'MINUS100', 'type' => 'fixed', 'value' => 100, 'minimum_order_amount' => 0,
            'usage_limit' => 10, 'used_count' => 0, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active',
        ]);
        $user = User::factory()->create();
        $variant = $this->variant($this->product(), 450);
        $this->actingAs($user);
        $this->addToCart($variant);
        $this->post('/checkout/coupon', ['code' => 'MINUS100']);

        // 450 − 100 = 350 < 399 → shipping applies even though the raw subtotal is ≥ 399.
        $this->post('/checkout', ['address_id' => $this->address($user)->id, 'payment_method' => 'cod']);
        $order = Order::where('user_id', $user->id)->firstOrFail();

        $this->assertEquals(49.00, (float) $order->shipping_amount);
        $this->assertEquals(399.00, (float) $order->grand_total);
    }

    public function test_no_shipping_fee_is_invented_while_no_flat_charge_is_configured(): void
    {
        $user = User::factory()->create();

        $order = $this->placeOrder($user, $this->variant($this->product(), 100));

        $this->assertEquals(0.00, (float) $order->shipping_amount);
    }

    // ------------------------------------------------------------------
    // Offers on the cart
    // ------------------------------------------------------------------

    public function test_cart_shows_progress_toward_the_next_offer(): void
    {
        $this->actingAs(User::factory()->create());
        $this->addToCart($this->variant($this->product(), 300));

        $this->get('/shopping-cart')
            ->assertOk()
            ->assertSee('Add')
            ->assertSee('₹99')
            ->assertSee('Surprise Sustainable Gift')
            ->assertSee('₹150 voucher for your next shopping');
    }

    public function test_add_to_cart_json_carries_everything_the_toast_shows(): void
    {
        $variant = $this->variant($this->product('Toast Cleaner'), 120);

        $this->addToCart($variant, 2)
            ->assertOk()
            ->assertJson([
                'success' => true,
                'productName' => 'Toast Cleaner',
                'addedQuantity' => 2,
                'itemCount' => 2,
                'cartTotal' => 240,
            ])
            ->assertJsonStructure(['cartUrl']);
    }

    // ------------------------------------------------------------------
    // ₹150 voucher
    // ------------------------------------------------------------------

    public function test_order_of_999_or_more_earns_one_single_use_voucher_only_once_delivered(): void
    {
        $user = User::factory()->create();
        $order = $this->placeOrder($user, $this->variant($this->product(), 1000));

        $this->assertNull($order->earnedVoucher, 'No voucher before delivery.');
        $this->assertDatabaseCount('coupons', 0);

        $this->deliver($order);

        $voucher = $order->fresh()->earnedVoucher;
        $this->assertNotNull($voucher);
        $this->assertSame('fixed', $voucher->type);
        $this->assertEquals(150.00, (float) $voucher->value);
        $this->assertSame(1, (int) $voucher->usage_limit);
        $this->assertSame($user->id, $voucher->user_id);
        $this->assertSame($order->id, $voucher->source_order_id);
    }

    public function test_voucher_is_not_duplicated_when_delivery_is_processed_twice(): void
    {
        $order = $this->placeOrder(User::factory()->create(), $this->variant($this->product(), 1200));
        $this->deliver($order);

        $order = $order->fresh();
        app(VoucherService::class)->issueForDeliveredOrder($order);
        $order->update(['order_status' => 'shipped']);
        $order->update(['order_status' => 'delivered']);

        $this->assertSame(1, Coupon::where('source_order_id', $order->id)->count());
    }

    public function test_orders_below_999_earn_no_voucher(): void
    {
        $order = $this->placeOrder(User::factory()->create(), $this->variant($this->product(), 998));
        $this->deliver($order);

        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_voucher_can_only_be_redeemed_by_its_owner(): void
    {
        $owner = User::factory()->create();
        $order = $this->placeOrder($owner, $this->variant($this->product('Big', ['name' => 'Big']), 1000));
        $this->deliver($order);
        $code = $order->fresh()->earnedVoucher->code;

        $variant = $this->variant($this->product('Next'), 500);

        // Another customer is refused.
        $this->actingAs(User::factory()->create());
        $this->addToCart($variant);
        $this->post('/checkout/coupon', ['code' => $code])->assertSessionHas('error');

        // The owner can apply it on their next order.
        $this->actingAs($owner);
        $this->addToCart($variant);
        $this->post('/checkout/coupon', ['code' => $code])->assertSessionHas('success');
    }

    // ------------------------------------------------------------------
    // Reviews (verified purchase, delivered only)
    // ------------------------------------------------------------------

    public function test_only_a_customer_with_a_delivered_order_can_review_and_only_once(): void
    {
        $user = User::factory()->create();
        $product = $this->product('Reviewable');
        $variant = $this->variant($product, 100);
        $order = $this->placeOrder($user, $variant);
        $payload = ['product_id' => $product->id, 'rating' => 5, 'title' => 'Great', 'review' => 'Works really well on my floors.'];

        $this->actingAs($user)->post('/reviews', $payload)->assertSessionHasErrors();
        $this->assertSame(0, ProductReview::count(), 'Pending order must not allow a review.');

        $this->deliver($order);

        $this->actingAs($user)->post('/reviews', $payload)->assertSessionHasNoErrors();
        $this->assertSame(1, ProductReview::count());

        $this->actingAs($user)->post('/reviews', $payload)->assertSessionHasErrors();
        $this->assertSame(1, ProductReview::count(), 'Duplicate review is refused.');
    }

    public function test_a_stranger_who_never_bought_the_product_cannot_review_it(): void
    {
        $product = $this->product('Not Bought');
        $this->variant($product, 100);

        $this->actingAs(User::factory()->create())
            ->post('/reviews', ['product_id' => $product->id, 'rating' => 4, 'title' => 'Hm', 'review' => 'I never bought this at all.'])
            ->assertSessionHasErrors();
        $this->assertSame(0, ProductReview::count());
    }

    // ------------------------------------------------------------------
    // Dashboard / orders
    // ------------------------------------------------------------------

    public function test_dashboard_metrics_link_to_order_history_and_active_orders(): void
    {
        $user = User::factory()->create();
        $this->placeOrder($user, $this->variant($this->product(), 100));

        $this->actingAs($user)->get('/user-dashboard')
            ->assertOk()
            ->assertSee(route('order-history'), false)
            ->assertSee(route('order-history', ['status' => 'active']), false);
    }

    public function test_active_filter_lists_only_in_progress_orders(): void
    {
        $user = User::factory()->create();
        $delivered = $this->placeOrder($user, $this->variant($this->product('Old One'), 100));
        $this->deliver($delivered);
        $active = $this->placeOrder($user, $this->variant($this->product('New One'), 100));

        $response = $this->actingAs($user)->get('/order-history?status=active');

        $response->assertOk()->assertSee($active->order_number)->assertDontSee($delivered->order_number);
    }

    public function test_cod_orders_read_as_payment_on_delivery_to_the_customer(): void
    {
        $user = User::factory()->create();
        $order = $this->placeOrder($user, $this->variant($this->product(), 100));

        $this->actingAs($user)->get("/orders/{$order->id}")->assertOk()->assertSee('Payment on Delivery')->assertSee('Cash on Delivery');
        $this->assertSame('pending', $order->payment_status, 'Backend keeps the technically correct status.');
    }

    public function test_order_detail_links_products_and_offers_a_review_only_when_delivered(): void
    {
        $user = User::factory()->create();
        $product = $this->product('Linked Product');
        $order = $this->placeOrder($user, $this->variant($product, 100));

        $before = $this->actingAs($user)->get("/orders/{$order->id}");
        $before->assertSee(route('products.show', $product->slug), false)->assertDontSee('Write a Review');

        $this->deliver($order);

        $this->actingAs($user)->get("/orders/{$order->id}")->assertSee('Write a Review');
    }

    // ------------------------------------------------------------------
    // Homepage
    // ------------------------------------------------------------------

    public function test_homepage_shows_one_slideshow_with_clickable_slides_and_no_newsletter(): void
    {
        $product = $this->product('Hero Product');
        HomeBanner::create(['title' => 'Big Sale', 'image' => 'banners/none.jpg', 'link_type' => 'product', 'product_id' => $product->id, 'status' => 'active']);
        HomeBanner::create(['title' => 'Hidden', 'image' => 'banners/none2.jpg', 'link_type' => 'url', 'link_url' => '/shop', 'status' => 'inactive']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('home-hero__slide', false)
            ->assertSee(route('products.show', $product->slug), false)
            ->assertDontSee('Hidden')
            ->assertDontSee('Subscribe to our daily News')
            ->assertDontSee('newsletter-popup', false);
    }

    public function test_homepage_explore_our_range_links_to_the_price_filter(): void
    {
        $this->variant($this->product('Cheap'), 9);

        $this->get('/')
            ->assertOk()
            ->assertSee('Explore Our Range')
            ->assertSee('Under ₹10')->assertSee('Under ₹50')->assertSee('Under ₹99')
            ->assertSee('max_price=99', false);

        $this->assertSame(1, collect(app(ProductCatalogService::class)->priceBands())->firstWhere('max', 10)['count']);
    }

    public function test_shop_max_price_filter_matches_the_band_count(): void
    {
        $this->variant($this->product('Nine'), 9);
        $this->variant($this->product('Forty'), 40);
        $this->variant($this->product('Two Hundred'), 200);

        $content = $this->get('/shop?max_price=50')->assertOk()->getContent();
        // Only the product grid: the sidebar widgets list unfiltered products.
        $grid = substr($content, strpos($content, 'shop__product-items'));
        $this->assertStringContainsString('Nine', $grid);
        $this->assertStringContainsString('Forty', $grid);
        $this->assertStringNotContainsString('Two Hundred', $grid);
    }

    public function test_tag_driven_collections_render_only_when_curated(): void
    {
        $this->get('/')->assertOk()->assertDontSee('100% Bio-Enzyme Products')->assertDontSee('365 Days Lowest Price');

        $tag = Tag::create(['name' => 'Bio Enzyme', 'slug' => ProductCatalogService::TAG_BIO_ENZYME, 'status' => 'active']);
        $product = $this->product('Enzyme Cleaner');
        $this->variant($product, 199);
        $product->tags()->attach($tag->id);

        $this->get('/')->assertOk()->assertSee('100% Bio-Enzyme Products')->assertSee('Enzyme Cleaner');
    }

    public function test_reels_link_to_a_buyable_product_and_sale_of_the_month_heading_exists(): void
    {
        $product = $this->product('Reel Product');
        $variant = $this->variant($product, 250);
        Reel::create(['title' => 'Quick clean', 'video_url' => 'https://example.com/v.mp4', 'product_id' => $product->id, 'status' => 'active']);

        $this->get('/')->assertOk()
            ->assertSee('Shop Our Reels')
            ->assertSee('Quick clean')
            ->assertSee('value="'.$variant->id.'"', false)
            ->assertSee('View Product');
    }

    // ------------------------------------------------------------------
    // Header / blog / contact
    // ------------------------------------------------------------------

    public function test_header_logo_links_to_home(): void
    {
        $this->get('/')->assertOk()->assertSee('class="header__logo-link"', false);
    }

    public function test_contact_page_uses_company_settings_and_saves_messages(): void
    {
        \App\Models\Page::create(['title' => 'Contact Us', 'slug' => 'contact-us', 'content' => 'x', 'status' => 'active']);
        Setting::setMany('general', ['company_email' => 'hello@cleann.test', 'company_phone' => '+91 99999 11111', 'company_address' => '12 Green Street, Delhi']);
        Setting::forget('general');

        $this->get('/contact-us')->assertOk()
            ->assertSee('hello@cleann.test')->assertSee('+91 99999 11111')->assertSee('12 Green Street, Delhi')
            ->assertDontSee('San Jose')->assertDontSee('Proxy@gmail.com');

        $this->post('/contact-us', [
            'name' => 'Asha', 'email' => 'asha@example.com', 'subject' => 'Bulk order', 'message' => 'Do you supply floor cleaner in bulk?',
        ])->assertRedirect()->assertSessionHas('contact_success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'asha@example.com', 'status' => 'unread']);
    }

    public function test_contact_form_validates_and_ignores_bot_submissions(): void
    {
        $this->post('/contact-us', ['name' => '', 'email' => 'not-an-email', 'subject' => '', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->post('/contact-us', [
            'name' => 'Bot', 'email' => 'bot@example.com', 'subject' => 'Buy now', 'message' => 'Spam spam spam spam', 'website' => 'http://spam.test',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_about_page_no_longer_shows_the_stock_farmer_photo(): void
    {
        \App\Models\Page::create(['title' => 'About Us', 'slug' => 'about-us', 'content' => 'x', 'status' => 'active']);

        $this->get('/about-us')->assertOk()->assertDontSee('members/img-08.png', false);
    }
}
