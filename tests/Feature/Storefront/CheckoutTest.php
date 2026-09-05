<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
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
            'is_default' => true,
            'status' => 'active',
            'sort_order' => 0,
            'weight' => 1.00,
        ], $overrides));
    }

    private function address(User $user, array $overrides = []): Address
    {
        return Address::create(array_merge([
            'user_id' => $user->id,
            'type' => 'shipping',
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'address_line_1' => '221B Baker Street',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'pincode' => '110001',
            'is_default' => true,
        ], $overrides));
    }

    private function addToCart(ProductVariant $variant, int $quantity = 1): void
    {
        // postJson (not withHeaders()->post()) so the Accept: application/json
        // header does NOT leak into the later full-page checkout POSTs in the
        // same test — those must behave like a real browser form submit
        // (redirect + flashed errors), not return a JSON 422.
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => $quantity]);
    }

    // ------------------------------------------------------------------
    // Access
    // ------------------------------------------------------------------

    public function test_guest_cannot_view_checkout(): void
    {
        $this->get('/checkout')->assertRedirect(route('sign-in'));
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $response = $this->post('/checkout', ['address_id' => 1, 'payment_method' => 'cod']);

        $response->assertRedirect(route('sign-in'));
        $this->assertDatabaseCount('orders', 0);
    }

    // ------------------------------------------------------------------
    // Page content
    // ------------------------------------------------------------------

    public function test_checkout_page_shows_real_cart_lines(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 2);

        $response = $this->get('/checkout');

        $response->assertOk()->assertSee('Green Apple')->assertSee('₹200.00');
    }

    public function test_empty_cart_shows_empty_state_and_no_place_order_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertOk();
        $response->assertSee('Your cart is empty.');
        $response->assertDontSee('id="place-order-form"', false);
    }

    public function test_unavailable_line_blocks_checkout_with_a_review_cart_message(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant);
        $variant->update(['status' => 'inactive']);

        $response = $this->get('/checkout');

        $response->assertOk();
        $response->assertSee('no longer available');
        $response->assertDontSee('id="place-order-form"', false);
    }

    public function test_checkout_page_shows_saved_addresses_and_selects_the_default(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant);
        $this->address($user, ['name' => 'Default Home', 'is_default' => true]);
        $this->address($user, ['name' => 'Work Address', 'is_default' => false]);

        $response = $this->get('/checkout');

        $response->assertOk()->assertSee('Default Home')->assertSee('Work Address');
    }

    // ------------------------------------------------------------------
    // Tax
    // ------------------------------------------------------------------

    public function test_tax_is_computed_per_product_tax_rate(): void
    {
        $taxRate = TaxRate::create(['name' => 'GST 5%', 'percentage' => 5, 'status' => 'active']);
        $product = $this->product($this->category(), 'Taxed Apple', ['tax_rate_id' => $taxRate->id]);
        $variant = $this->variant($product, ['single_price' => 200]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->addToCart($variant, 1);

        $response = $this->get('/checkout');

        // 5% of 200 = 10
        $response->assertOk()->assertSee('₹10.00');
    }

    public function test_no_tax_when_product_has_no_tax_rate(): void
    {
        $variant = $this->variant($this->product($this->category(), 'Untaxed Apple'), ['single_price' => 200]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->addToCart($variant, 1);

        $response = $this->get('/checkout');

        $response->assertOk()->assertDontSee('Tax:');
    }

    // ------------------------------------------------------------------
    // Shipping
    // ------------------------------------------------------------------

    public function test_shipping_matches_by_exact_pincode_over_city_and_state(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['weight' => 1]);
        $this->actingAs($user);
        $this->addToCart($variant);
        $address = $this->address($user, ['pincode' => '110001', 'city' => 'Delhi', 'state' => 'Delhi']);

        $stateZone = ShippingZone::create(['name' => 'Delhi State', 'state' => 'Delhi', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $stateZone->id, 'min_weight' => 0, 'shipping_charge' => 50, 'status' => 'active']);

        $pincodeZone = ShippingZone::create(['name' => 'Central Delhi', 'pincode' => '110001', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $pincodeZone->id, 'min_weight' => 0, 'shipping_charge' => 15, 'status' => 'active']);

        $response = $this->get('/checkout?address_id='.$address->id);

        $response->assertOk()->assertSee('₹15.00');
    }

    public function test_shipping_falls_back_to_catch_all_zone_when_nothing_more_specific_matches(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['weight' => 1]);
        $this->actingAs($user);
        $this->addToCart($variant);
        $address = $this->address($user, ['pincode' => '999999', 'city' => 'Nowhere', 'state' => 'Nowhere State']);

        $catchAll = ShippingZone::create(['name' => 'Rest of India', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $catchAll->id, 'min_weight' => 0, 'shipping_charge' => 99, 'status' => 'active']);

        $response = $this->get('/checkout?address_id='.$address->id);

        $response->assertOk()->assertSee('₹99.00');
    }

    public function test_shipping_is_free_when_no_zone_is_configured_at_all(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant);
        $address = $this->address($user);

        $response = $this->get('/checkout?address_id='.$address->id);

        $response->assertOk()->assertSee('Free');
    }

    public function test_shipping_selects_the_correct_weight_bracket(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Heavy Apple'), ['weight' => 8]);
        $this->actingAs($user);
        $this->addToCart($variant, 1); // total weight = 8
        $address = $this->address($user);

        $zone = ShippingZone::create(['name' => 'All India', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $zone->id, 'min_weight' => 0, 'max_weight' => 5, 'shipping_charge' => 40, 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $zone->id, 'min_weight' => 5.01, 'max_weight' => 20, 'shipping_charge' => 80, 'status' => 'active']);

        $response = $this->get('/checkout?address_id='.$address->id);

        $response->assertOk()->assertSee('₹80.00')->assertDontSee('₹40.00');
    }

    public function test_shipping_is_free_above_the_configured_threshold(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 1000]);
        $this->actingAs($user);
        $this->addToCart($variant, 1); // subtotal 1000
        $address = $this->address($user);

        $zone = ShippingZone::create(['name' => 'All India', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $zone->id, 'min_weight' => 0, 'shipping_charge' => 50, 'free_shipping_above' => 500, 'status' => 'active']);

        $response = $this->get('/checkout?address_id='.$address->id);

        $response->assertOk()->assertSee('Free');
    }

    // ------------------------------------------------------------------
    // Coupons
    // ------------------------------------------------------------------

    public function test_valid_percentage_coupon_is_applied(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 200]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'minimum_order_amount' => 0, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);

        $response = $this->post('/checkout/coupon', ['code' => 'SAVE10']);

        $response->assertSessionHas('success');
        $this->get('/checkout')->assertSee('you save ₹20.00', false);
    }

    public function test_valid_fixed_coupon_is_applied(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 200]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'FLAT30', 'type' => 'fixed', 'value' => 30, 'minimum_order_amount' => 0, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);

        $this->post('/checkout/coupon', ['code' => 'FLAT30']);

        $this->get('/checkout')->assertSee('you save ₹30.00', false);
    }

    public function test_percentage_coupon_respects_maximum_discount_cap(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 1000]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'BIG50', 'type' => 'percentage', 'value' => 50, 'maximum_discount_amount' => 100, 'minimum_order_amount' => 0, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);

        $this->post('/checkout/coupon', ['code' => 'BIG50']);

        // 50% of 1000 = 500, capped to 100.
        $this->get('/checkout')->assertSee('you save ₹100.00', false);
    }

    public function test_coupon_below_minimum_order_amount_is_rejected(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 50]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'BIGORDER', 'type' => 'fixed', 'value' => 10, 'minimum_order_amount' => 500, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);

        $response = $this->post('/checkout/coupon', ['code' => 'BIGORDER']);

        $response->assertSessionHas('error');
        $this->get('/checkout')->assertDontSee('you save');
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'EXPIRED', 'type' => 'fixed', 'value' => 10, 'start_date' => now()->subMonth(), 'end_date' => now()->subDay(), 'status' => 'active']);

        $response = $this->post('/checkout/coupon', ['code' => 'EXPIRED']);

        $response->assertSessionHas('error');
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'OFFCOUPON', 'type' => 'fixed', 'value' => 10, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'inactive']);

        $response = $this->post('/checkout/coupon', ['code' => 'OFFCOUPON']);

        $response->assertSessionHas('error');
    }

    public function test_coupon_at_usage_limit_is_rejected(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'MAXEDOUT', 'type' => 'fixed', 'value' => 10, 'usage_limit' => 5, 'used_count' => 5, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);

        $response = $this->post('/checkout/coupon', ['code' => 'MAXEDOUT']);

        $response->assertSessionHas('error');
    }

    public function test_unknown_coupon_code_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/checkout/coupon', ['code' => 'DOESNOTEXIST']);

        $response->assertSessionHas('error');
    }

    public function test_coupon_can_be_removed(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 200]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);
        $this->post('/checkout/coupon', ['code' => 'SAVE10']);

        $this->delete('/checkout/coupon');

        $this->get('/checkout')->assertDontSee('you save');
    }

    // ------------------------------------------------------------------
    // Placing an order
    // ------------------------------------------------------------------

    public function test_placing_an_order_creates_order_and_items_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), 'Green Apple');
        $variant = $this->variant($product, ['single_price' => 100, 'stock_quantity' => 10]);
        $this->actingAs($user);
        $this->addToCart($variant, 3);
        $address = $this->address($user);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'subtotal' => 300,
            'grand_total' => 300,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => 100,
            'total_price' => 300,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cod',
            'amount' => 300,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock_quantity' => 7]);
    }

    public function test_placing_an_order_clears_the_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user);

        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $this->get('/checkout')->assertSee('Your cart is empty.');
    }

    public function test_placing_an_order_with_a_coupon_increments_used_count_and_records_discount(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 200]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $coupon = Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'used_count' => 0, 'start_date' => now()->subDay(), 'end_date' => now()->addDay(), 'status' => 'active']);
        $this->post('/checkout/coupon', ['code' => 'SAVE10']);
        $address = $this->address($user);

        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'coupon_id' => $coupon->id, 'discount_amount' => 20]);
        // Applied coupon is cleared from the session after a successful order.
        $this->assertNull(session('checkout_coupon_code'));
    }

    public function test_cannot_place_order_with_empty_cart(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);

        $response = $this->actingAs($user)->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_place_order_with_an_unavailable_line(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $variant->update(['status' => 'inactive']);
        $address = $this->address($user);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_place_order_when_stock_runs_out_between_add_and_checkout(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['stock_quantity' => 5]);
        $this->actingAs($user);
        $this->addToCart($variant, 5);
        // Sells out completely after being added to the cart but before checkout.
        $variant->update(['stock_quantity' => 0, 'stock_status' => 'out_of_stock']);
        $address = $this->address($user);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_place_order_using_another_customers_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $othersAddress = $this->address($otherUser);

        $response = $this->post('/checkout', ['address_id' => $othersAddress->id, 'payment_method' => 'cod']);

        $response->assertSessionHasErrors('address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_place_order_with_an_invalid_payment_method(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'bitcoin']);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_number_is_unique_and_correctly_formatted(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user);

        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $order = Order::first();
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', $order->order_number);
    }

    // ------------------------------------------------------------------
    // Order confirmation page
    // ------------------------------------------------------------------

    public function test_order_confirmation_page_shows_real_order_data(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 150]);
        $this->actingAs($user);
        $this->addToCart($variant, 2);
        $address = $this->address($user);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        $response = $this->get('/orders/'.$order->id);

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Green Apple');
        $response->assertSee('₹300.00');
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($owner);
        $this->addToCart($variant, 1);
        $address = $this->address($owner);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        $response = $this->actingAs($stranger)->get('/orders/'.$order->id);

        $response->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Immutable delivery snapshot (DB Gap Planning)
    // ------------------------------------------------------------------

    public function test_order_freezes_a_copy_of_the_shipping_address_at_placement(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, [
            'name' => 'Original Name',
            'phone' => '1112223333',
            'address_line_1' => 'Original Line 1',
            'address_line_2' => 'Original Line 2',
            'city' => 'Original City',
            'state' => 'Original State',
            'country' => 'India',
            'pincode' => '111111',
        ]);

        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'shipping_name' => 'Original Name',
            'shipping_phone' => '1112223333',
            'shipping_address_line_1' => 'Original Line 1',
            'shipping_address_line_2' => 'Original Line 2',
            'shipping_city' => 'Original City',
            'shipping_state' => 'Original State',
            'shipping_country' => 'India',
            'shipping_pincode' => '111111',
            'billing_same_as_shipping' => true,
        ]);
    }

    public function test_editing_the_address_afterwards_does_not_change_the_historical_order(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['single_price' => 100]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, ['name' => 'Before Edit', 'city' => 'Delhi']);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        // Customer later edits that same address to something completely different.
        $address->update(['name' => 'After Edit', 'city' => 'Mumbai', 'address_line_1' => 'Totally New Street', 'pincode' => '400001']);

        $order->refresh();
        $this->assertSame('Before Edit', $order->shipping_name);
        $this->assertSame('Delhi', $order->shipping_city);

        $response = $this->actingAs($user)->get('/orders/'.$order->id);
        $response->assertOk();
        $response->assertSee('Before Edit');
        $response->assertSee('Delhi');
        $response->assertDontSee('After Edit');
        $response->assertDontSee('Totally New Street');
    }

    public function test_deleting_the_address_afterwards_does_not_break_or_change_the_historical_order(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, ['name' => 'Doomed Address', 'city' => 'Chennai']);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        // Customer deletes the address entirely; orders.address_id is
        // nullOnDelete, so the live reference is gone.
        $this->actingAs($user)->delete('/addresses/'.$address->id);

        $order->refresh();
        $this->assertNull($order->address_id);
        $this->assertSame('Doomed Address', $order->shipping_name);

        $response = $this->actingAs($user)->get('/orders/'.$order->id);
        $response->assertOk();
        $response->assertSee('Doomed Address');
        $response->assertSee('Chennai');
    }

    public function test_order_confirmation_page_renders_the_snapshot_not_the_live_address(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, ['name' => 'Snapshot Name', 'city' => 'Kolkata']);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        $address->update(['name' => 'Live Name Changed', 'city' => 'Somewhere Else']);

        $response = $this->actingAs($user)->get('/orders/'.$order->id);

        $response->assertOk()->assertSee('Snapshot Name')->assertSee('Kolkata')->assertDontSee('Live Name Changed');
    }

    public function test_shipping_zone_name_is_frozen_onto_the_order(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'), ['weight' => 1]);
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, ['pincode' => '110001']);

        $zone = ShippingZone::create(['name' => 'Central Delhi Zone', 'pincode' => '110001', 'status' => 'active']);
        ShippingRate::create(['shipping_zone_id' => $zone->id, 'min_weight' => 0, 'shipping_charge' => 25, 'status' => 'active']);

        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::first();

        $this->assertSame('Central Delhi Zone', $order->shipping_zone_name);

        // Admin later renames the zone — the order keeps the original name.
        $zone->update(['name' => 'Renamed Zone']);
        $this->assertSame('Central Delhi Zone', $order->fresh()->shipping_zone_name);

        $this->actingAs($user)->get('/orders/'.$order->id)->assertOk()->assertSee('Central Delhi Zone');
    }

    public function test_billing_snapshot_falls_back_to_shipping_when_same(): void
    {
        $user = User::factory()->create();
        $variant = $this->variant($this->product($this->category(), 'Green Apple'));
        $this->actingAs($user);
        $this->addToCart($variant, 1);
        $address = $this->address($user, ['name' => 'One Address', 'city' => 'Pune']);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $order = Order::first();

        $this->assertTrue($order->billing_same_as_shipping);
        $this->assertSame($order->shippingSnapshot(), $order->billingSnapshot());
        $this->assertSame('One Address', $order->billingSnapshot()['name']);
    }
}
