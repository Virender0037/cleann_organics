<?php

namespace Tests\Feature\Shipping;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Shipping\Exceptions\NimbusPostAuthenticationFailed;
use App\Services\Shipping\Exceptions\NimbusPostMalformedResponse;
use App\Services\Shipping\Exceptions\NimbusPostNotConfigured;
use App\Services\Shipping\Exceptions\NimbusPostUnavailable;
use App\Services\Shipping\FulfilmentNotAllowed;
use App\Services\Shipping\FulfilmentService;
use App\Services\Shipping\NimbusPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * NimbusPost integration (request/response shapes from NimbusPost's published "Nimbuspost Partners API" Postman
 * collection). Every HTTP call is faked — nothing here ever reaches NimbusPost.
 */
class NimbusPostFulfilmentTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api.nimbuspost.com/v1/';

    private function configure(): void
    {
        config([
            'nimbuspost.enabled' => true,
            'nimbuspost.base_url' => 'https://api.nimbuspost.com/v1',
            'nimbuspost.email' => 'api-user@example.test',
            'nimbuspost.password' => 'not-a-real-password',
            'nimbuspost.pickup' => [
                'warehouse_name' => 'Main WH', 'name' => 'Clean Organics', 'address' => '1 Warehouse Road', 'address_2' => null,
                'city' => 'Gurgaon', 'state' => 'Haryana', 'pincode' => '122001', 'phone' => '9999999999', 'gst_number' => null,
            ],
        ]);
        Cache::forget('nimbuspost.token');
    }

    /** Documented login success + whatever other endpoints a test needs. */
    private function fakeApi(array $routes = []): void
    {
        Http::fake($routes + [
            self::API.'users/login' => Http::response(['status' => true, 'data' => 'test-token-123'], 200),
        ]);
    }

    private function booked(array $override = []): array
    {
        return ['status' => true, 'data' => array_merge([
            'order_id' => 3351555, 'shipment_id' => 1929242, 'awb_number' => '59632220664', 'courier_id' => '5',
            'courier_name' => 'Bluedart', 'status' => 'booked', 'additional_info' => 'BOM / TEC', 'payment_type' => 'cod',
            'label' => 'https://nimubs-assets.s3.amazonaws.com/labels/test.pdf',
        ], $override)];
    }

    private function order(string $method = 'cod', string $paymentStatus = 'pending', string $orderStatus = 'confirmed', ?User $user = null): Order
    {
        $user ??= User::factory()->create();
        $category = Category::create(['name' => 'Home', 'slug' => 'home-'.uniqid(), 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Floor Cleaner', 'slug' => 'fc-'.uniqid(), 'status' => 'active', 'is_returnable' => false, 'return_days' => 7]);
        $variant = ProductVariant::create(['product_id' => $product->id, 'variant_name' => '500ml', 'sku' => 'FC-500-'.uniqid(), 'weight' => 0.6, 'length_cm' => 10, 'width_cm' => 8, 'height_cm' => 20, 'enable_tiered_pricing' => false, 'single_quantity' => 1, 'single_price' => 450, 'stock_quantity' => 10, 'low_stock_quantity' => 1, 'stock_status' => 'in_stock', 'is_default' => true, 'status' => 'active', 'sort_order' => 0]);

        $order = Order::create([
            'user_id' => $user->id, 'order_number' => 'ORD-20261006-'.strtoupper(substr(uniqid(), -6)),
            'subtotal' => 450, 'shipping_amount' => 0, 'discount_amount' => 0, 'grand_total' => 450,
            'payment_method' => $method, 'payment_status' => $paymentStatus, 'order_status' => $orderStatus,
            'shipping_name' => 'Asha Rao', 'shipping_phone' => '+91 98765 43210', 'shipping_address_line_1' => '12 MG Road',
            'shipping_city' => 'Pune', 'shipping_state' => 'Maharashtra', 'shipping_country' => 'India', 'shipping_pincode' => '411001',
            'billing_same_as_shipping' => true,
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name' => 'Floor Cleaner', 'variant_sku' => 'FC-500', 'weight' => 0.6, 'quantity' => 1, 'unit_price' => 450, 'discount_amount' => 0, 'tax_amount' => 0, 'total_price' => 450]);
        Payment::create(['order_id' => $order->id, 'payment_method' => $method, 'amount' => 450, 'status' => $paymentStatus === 'paid' ? 'paid' : 'pending']);

        return $order->fresh();
    }

    private function package(): array
    {
        return ['weight_grams' => 600, 'length_cm' => 10.0, 'width_cm' => 8.0, 'height_cm' => 20.0];
    }

    private function fulfilment(): FulfilmentService
    {
        return app(FulfilmentService::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    // ------------------------------------------------------------------ configuration & auth

    public function test_blank_configuration_is_reported_by_name_and_never_calls_the_api(): void
    {
        Http::fake();
        config(['nimbuspost.enabled' => true, 'nimbuspost.email' => null, 'nimbuspost.password' => null]);
        $service = app(NimbusPostService::class);

        $this->assertFalse($service->isConfigured());
        $this->assertContains('NIMBUSPOST_EMAIL', $service->missingConfiguration());
        $this->expectException(NimbusPostNotConfigured::class);

        try {
            $service->track('123');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_disabled_by_default(): void
    {
        $this->assertFalse(app(NimbusPostService::class)->isEnabled());
    }

    public function test_login_token_is_sent_as_bearer_and_cached(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments/track/*' => Http::response(['status' => true, 'data' => ['status' => 'booked', 'history' => []]], 200)]);
        $service = app(NimbusPostService::class);

        $service->track('111');
        $service->track('222');

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'users/login' && $r['email'] === 'api-user@example.test');
        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'shipments/track/111') && $r->hasHeader('Authorization', 'Bearer test-token-123'));
        $this->assertSame(1, collect(Http::recorded())->filter(fn ($pair) => str_ends_with($pair[0]->url(), 'users/login'))->count());
        $this->assertNotSame('test-token-123', Cache::get('nimbuspost.token')); // cached encrypted, not in clear
    }

    public function test_invalid_credentials_raise_an_authentication_error(): void
    {
        $this->configure();
        Http::fake([self::API.'users/login' => Http::response(['status' => false, 'message' => 'Invalid email or password'], 401)]);

        $this->expectException(NimbusPostAuthenticationFailed::class);
        app(NimbusPostService::class)->track('111');
    }

    public function test_an_expired_token_is_refreshed_once(): void
    {
        $this->configure();
        Http::fake([
            self::API.'users/login' => Http::response(['status' => true, 'data' => 'fresh-token'], 200),
            self::API.'shipments/track/*' => Http::sequence()
                ->push(['message' => 'Unauthorized'], 401)
                ->push(['status' => true, 'data' => ['status' => 'booked', 'history' => []]], 200),
        ]);

        $data = app(NimbusPostService::class)->track('333');

        $this->assertSame('booked', $data['status']);
    }

    // ------------------------------------------------------------------ serviceability

    public function test_serviceability_success_not_serviceable_and_timeout_are_distinguished(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'courier/serviceability' => Http::sequence()
            ->push(['status' => true, 'data' => [['id' => '8', 'name' => 'DTDC Air', 'total_charges' => 101.48]]], 200)
            ->push(['status' => true, 'data' => []], 200)
            ->pushFailedConnection()]);
        $fulfilment = $this->fulfilment();

        $this->assertTrue($fulfilment->checkoutServiceability('411001', 'cod', 450, 600));
        $this->assertFalse($fulfilment->checkoutServiceability('999999', 'cod', 450, 600));
        $this->assertNull($fulfilment->checkoutServiceability('110001', 'prepaid', 450, 600)); // timeout ≠ not serviceable

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), 'courier/serviceability') && $r['origin'] === '122001' && $r['destination'] === '411001' && $r['payment_type'] === 'cod' && $r['weight'] === 600);
    }

    public function test_checkout_refuses_an_explicitly_unserviceable_pincode_but_not_an_unreachable_api(): void
    {
        $this->configure();
        $user = User::factory()->create();
        $address = Address::create(['user_id' => $user->id, 'type' => 'shipping', 'name' => 'A', 'phone' => '9876543210', 'address_line_1' => 'x', 'city' => 'Pune', 'state' => 'MH', 'country' => 'India', 'pincode' => '999999', 'is_default' => true]);
        $variant = $this->order()->items->first()->variant;

        $this->fakeApi([self::API.'courier/serviceability' => Http::response(['status' => true, 'data' => []], 200)]);
        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, "can't deliver to PIN code 999999"));
        $this->assertSame(0, Order::where('user_id', $user->id)->count());

        Cache::flush();
        $this->configure();
        Http::fake([self::API.'users/login' => fn () => throw new ConnectionException('timed out')]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])->assertRedirect();
        $this->assertSame(1, Order::where('user_id', $user->id)->count()); // order kept; serviceability unknown
    }

    public function test_checkout_makes_no_nimbuspost_call_while_disabled(): void
    {
        Http::fake();
        [$user, $address] = [User::factory()->create(), null];
        $address = Address::create(['user_id' => $user->id, 'type' => 'shipping', 'name' => 'A', 'phone' => '9876543210', 'address_line_1' => 'x', 'city' => 'Pune', 'state' => 'MH', 'country' => 'India', 'pincode' => '411001', 'is_default' => true]);
        $variant = $this->order()->items->first()->variant;

        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])->assertRedirect();

        Http::assertNothingSent();
        $this->assertSame(1, Order::where('user_id', $user->id)->count());
    }

    // ------------------------------------------------------------------ payment gate

    public function test_payment_gate_per_payment_method(): void
    {
        $this->configure();
        $f = $this->fulfilment();

        $this->assertSame([], $f->blockers($this->order('cod', 'pending', 'confirmed')));
        $this->assertContains('Confirm this Cash on Delivery order first.', $f->blockers($this->order('cod', 'pending', 'pending')));
        $this->assertSame([], $f->blockers($this->order('razorpay', 'paid', 'confirmed')));
        $this->assertNotSame([], $f->blockers($this->order('razorpay', 'pending', 'pending')));
        $this->assertStringContainsString('Manual UPI payment has not been verified', implode(' ', $f->blockers($this->order('manual_upi', 'pending', 'pending'))));
        $this->assertSame([], $f->blockers($this->order('manual_upi', 'paid', 'confirmed')));
        $this->assertStringContainsString('bank transfer has not been verified', implode(' ', $f->blockers($this->order('bank_transfer', 'pending', 'pending'))));
    }

    public function test_bank_transfer_can_be_verified_by_admin_and_then_shipped(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::response($this->booked(['payment_type' => 'prepaid']), 200)]);
        $order = $this->order('bank_transfer', 'pending', 'pending');

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), ['package_weight_grams' => 600])
            ->assertSessionHas('error');
        $this->assertSame(0, Shipment::count());

        $this->actingAs($this->admin())->patch(route('admin.sales.payments.verify', $order->payment), ['admin_note' => 'NEFT received'])->assertRedirect();
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), ['package_weight_grams' => 600])
            ->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/shipments') && $r['payment_type'] === 'prepaid');
    }

    public function test_admin_status_moves_are_blocked_for_unverified_prepaid_orders_but_not_cod(): void
    {
        $admin = $this->admin();

        foreach (['manual_upi', 'bank_transfer', 'razorpay'] as $method) {
            $order = $this->order($method, 'pending', 'pending');
            foreach (['confirmed', 'packed', 'shipped', 'delivered'] as $target) {
                $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $order), ['status' => $target])->assertSessionHas('error');
            }
            $this->assertSame('pending', $order->fresh()->order_status, $method);
        }

        $cod = $this->order('cod', 'pending', 'pending');
        $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $cod), ['status' => 'packed'])->assertSessionHas('success');

        $verified = $this->order('manual_upi', 'paid', 'confirmed');
        $this->actingAs($admin)->patch(route('admin.sales.orders.status.update', $verified), ['status' => 'shipped'])->assertSessionHas('success');
    }

    // ------------------------------------------------------------------ shipment creation

    public function test_cod_shipment_payload_and_stored_awb(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::response($this->booked(), 200)]);
        $order = $this->order('cod', 'pending', 'confirmed');

        $shipment = $this->fulfilment()->createShipment($order, $this->package());

        Http::assertSent(function (Request $r) use ($order) {
            return $r->url() === self::API.'shipments'
                && $r->hasHeader('Authorization', 'Bearer test-token-123')
                && $r['order_number'] === $order->order_number
                && $r['payment_type'] === 'cod'
                && $r['order_amount'] == 450
                && $r['package_weight'] === 600
                && $r['package_breadth'] == 8.0
                && $r['consignee']['phone'] === '9876543210'      // "+91 98765 43210" normalised
                && $r['consignee']['pincode'] === '411001'
                && $r['pickup']['warehouse_name'] === 'Main WH'
                && $r['order_items'][0] === ['name' => 'Floor Cleaner', 'qty' => '1', 'price' => '450', 'sku' => 'FC-500'];
        });

        $this->assertSame(Shipment::BOOKED, $shipment->status);
        $this->assertSame('59632220664', $shipment->awb_number);
        $this->assertSame('Bluedart', $shipment->courier_name);
        $this->assertSame('1929242', $shipment->provider_shipment_id);
        $this->assertSame($order->id, $shipment->active_order_id);
        $this->assertEquals(450, $shipment->cod_amount);
        $this->assertSame('pending', $order->fresh()->payment_status); // shipping never touches payment
    }

    public function test_razorpay_paid_order_ships_as_prepaid_with_zero_cod(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::response($this->booked(['payment_type' => 'prepaid']), 200)]);

        $shipment = $this->fulfilment()->createShipment($this->order('razorpay', 'paid', 'confirmed'), $this->package());

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'shipments' && $r['payment_type'] === 'prepaid');
        $this->assertSame('prepaid', $shipment->payment_type);
        $this->assertEquals(0, $shipment->cod_amount);
    }

    public function test_unverified_manual_upi_cannot_be_shipped_and_nothing_is_sent(): void
    {
        $this->configure();
        Http::fake();

        $this->expectException(FulfilmentNotAllowed::class);

        try {
            $this->fulfilment()->createShipment($this->order('manual_upi', 'pending', 'pending'), $this->package());
        } finally {
            Http::assertNothingSent();
            $this->assertSame(0, Shipment::count());
        }
    }

    public function test_second_create_is_refused_and_only_one_booking_is_sent(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::response($this->booked(), 200)]);
        $order = $this->order();

        $this->fulfilment()->createShipment($order, $this->package());

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), ['package_weight_grams' => 600])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'already has a live shipment'));

        $this->assertSame(1, Shipment::count());
        $this->assertSame(1, collect(Http::recorded())->filter(fn ($pair) => $pair[0]->url() === self::API.'shipments')->count());
    }

    public function test_the_database_guard_blocks_a_concurrent_second_booking(): void
    {
        $order = $this->order();
        Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::CREATING, 'payment_type' => 'cod', 'package_weight_grams' => 600]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::CREATING, 'payment_type' => 'cod', 'package_weight_grams' => 600]);
    }

    public function test_a_refused_booking_is_recorded_and_can_be_retried(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::sequence()
            ->push(['status' => false, 'message' => "Consignee name is required\n"], 404)
            ->push($this->booked(), 200)]);
        $order = $this->order();

        $failed = $this->fulfilment()->createShipment($order, $this->package());
        $this->assertSame(Shipment::FAILED, $failed->status);
        $this->assertNull($failed->active_order_id);
        $this->assertSame('Consignee name is required', $failed->failure_reason);

        $retry = $this->fulfilment()->createShipment($order->fresh(), $this->package());
        $this->assertSame(Shipment::BOOKED, $retry->status);
        $this->assertSame(2, Shipment::where('order_id', $order->id)->count());
    }

    public function test_a_timeout_keeps_the_order_locked_until_an_admin_releases_it(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::sequence()->pushFailedConnection()->push($this->booked(), 200)]);
        $order = $this->order();

        $unconfirmed = $this->fulfilment()->createShipment($order, $this->package());
        $this->assertSame(Shipment::CREATION_UNCONFIRMED, $unconfirmed->status);
        $this->assertSame($order->id, $unconfirmed->fresh()->active_order_id); // still claimed: may exist at NimbusPost

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), ['package_weight_grams' => 600])->assertSessionHas('error');

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.release', [$order, $unconfirmed]))->assertSessionHas('success');
        $this->assertSame(Shipment::BOOKED, $this->fulfilment()->createShipment($order->fresh(), $this->package())->status);
        $order->refresh();
        $this->assertSame('confirmed', $order->order_status); // a timeout never changes the order itself
        $this->assertSame('pending', $order->payment_status);
        $this->assertEquals(450, $order->grand_total);
    }

    public function test_a_malformed_booking_response_is_never_stored_as_booked(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments' => Http::response(['status' => true, 'data' => ['shipment_id' => 1]], 200)]);

        $shipment = $this->fulfilment()->createShipment($this->order(), $this->package());

        $this->assertSame(Shipment::FAILED, $shipment->status);
        $this->assertNull($shipment->awb_number);
    }

    public function test_non_json_response_is_malformed(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments/track/*' => Http::response('<html>gateway</html>', 200)]);

        $this->expectException(NimbusPostMalformedResponse::class);
        app(NimbusPostService::class)->track('1');
    }

    public function test_server_error_is_unavailable(): void
    {
        $this->configure();
        $this->fakeApi([self::API.'shipments/track/*' => Http::response('', 503)]);

        $this->expectException(NimbusPostUnavailable::class);
        app(NimbusPostService::class)->track('1');
    }

    // ------------------------------------------------------------------ tracking / NDR / cancel

    private function bookedShipment(): Shipment
    {
        $this->configure();
        $order = $this->order();

        return Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::BOOKED, 'payment_type' => 'cod', 'cod_amount' => 450, 'package_weight_grams' => 600, 'awb_number' => '59650109492', 'courier_name' => 'Bluedart', 'booked_at' => now()]);
    }

    public function test_tracking_maps_documented_codes_and_delivery_completes_the_order(): void
    {
        $shipment = $this->bookedShipment();
        $this->fakeApi([self::API.'shipments/track/59650109492' => Http::sequence()
            ->push(['status' => true, 'data' => ['status' => 'in transit', 'history' => [['status_code' => 'PP', 'location' => 'Pune', 'event_time' => '2026-10-06 10:00', 'message' => 'Pickup pending'], ['status_code' => 'EX', 'location' => 'Pune', 'event_time' => '2026-10-07 16:04', 'message' => 'CONSIGNEE REFUSED TO ACCEPT']]]], 200)
            ->push(['status' => true, 'data' => ['status' => 'delivered', 'history' => [['status_code' => 'DL', 'location' => 'Pune', 'event_time' => '2026-10-08 12:00', 'message' => 'Delivered']]]], 200)]);

        $exception = $this->fulfilment()->refreshTracking($shipment);
        $this->assertSame(Shipment::EXCEPTION, $exception->status);
        $this->assertSame('CONSIGNEE REFUSED TO ACCEPT', $exception->ndr_reason);
        $this->assertCount(2, $exception->tracking_history);

        $delivered = $this->fulfilment()->refreshTracking($exception);
        $this->assertSame(Shipment::DELIVERED, $delivered->status);
        $this->assertNotNull($delivered->delivered_at);
        $this->assertSame('delivered', $delivered->order->fresh()->order_status);
        $this->assertSame('pending', $delivered->order->fresh()->payment_status); // never altered by shipping
    }

    public function test_an_unknown_tracking_code_keeps_the_status_and_records_the_raw_value(): void
    {
        $shipment = $this->bookedShipment();
        $this->fakeApi([self::API.'shipments/track/*' => Http::response(['status' => true, 'data' => ['status' => 'x', 'history' => [['status_code' => 'ZZ-NEW', 'message' => 'Something new']]]], 200)]);

        $after = $this->fulfilment()->refreshTracking($shipment);

        $this->assertSame(Shipment::BOOKED, $after->status);
        $this->assertSame('ZZ-NEW', $after->provider_status);
    }

    public function test_rto_codes_map_to_rto_statuses(): void
    {
        $shipment = $this->bookedShipment();
        $this->fakeApi([self::API.'shipments/track/*' => Http::response(['status' => true, 'data' => ['status' => 'rto', 'rto_status' => 'delivered', 'rto_awb' => '75312963180', 'history' => [['status_code' => 'RT-DL', 'message' => 'RTO delivered']]]], 200)]);

        $after = $this->fulfilment()->refreshTracking($shipment);

        $this->assertSame(Shipment::RTO_DELIVERED, $after->status);
        $this->assertSame('75312963180', $after->rto_awb);
        $this->assertNotSame('delivered', $after->order->fresh()->order_status);
    }

    public function test_cancellation_success_and_refusal(): void
    {
        $shipment = $this->bookedShipment();
        $this->fakeApi([self::API.'shipments/cancel' => Http::sequence()
            ->push(['status' => false, 'message' => 'Unable to cancel'], 404)
            ->push(['status' => true, 'message' => 'Shipment Cancelled'], 200)]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Unable to cancel'));
        $this->assertSame(Shipment::BOOKED, $shipment->fresh()->status);

        $this->actingAs($admin)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))->assertSessionHas('success');
        $shipment->refresh();
        $this->assertSame(Shipment::CANCELLED, $shipment->status);
        $this->assertNull($shipment->active_order_id);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), 'shipments/cancel') && $r['awb'] === '59650109492');
    }

    public function test_a_delivered_shipment_cannot_be_cancelled(): void
    {
        $shipment = $this->bookedShipment();
        $shipment->update(['status' => Shipment::DELIVERED]);
        Http::fake();

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))->assertSessionHas('error');
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ authorization & visibility

    public function test_customers_and_guests_cannot_use_admin_shipment_actions(): void
    {
        $shipment = $this->bookedShipment();
        $customer = $shipment->order->user;
        Http::fake();

        $this->post(route('admin.sales.orders.shipments.store', $shipment->order), ['package_weight_grams' => 600])->assertRedirect();
        $this->actingAs($customer)->post(route('admin.sales.orders.shipments.store', $shipment->order), ['package_weight_grams' => 600])->assertRedirect();
        $this->actingAs($customer)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))->assertRedirect();

        Http::assertNothingSent();
        $this->assertSame(Shipment::BOOKED, $shipment->fresh()->status);
        $this->assertSame(1, Shipment::count());
    }

    public function test_a_shipment_cannot_be_acted_on_through_another_order(): void
    {
        $shipment = $this->bookedShipment();
        $other = $this->order();

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.cancel', [$other, $shipment]))->assertNotFound();
    }

    public function test_customer_sees_safe_tracking_and_nothing_internal(): void
    {
        $shipment = $this->bookedShipment();
        $shipment->update(['label_url' => 'https://nimubs-assets.s3.amazonaws.com/labels/secret-label.pdf', 'tracking_history' => [['status_code' => 'IT', 'location' => 'Pune Hub', 'event_time' => '2026-10-07 09:00', 'message' => 'Shipment arrived']], 'status' => Shipment::IN_TRANSIT]);
        $owner = $shipment->order->user;

        $this->actingAs($owner)->get(route('orders.show', $shipment->order))
            ->assertOk()
            ->assertSee('Shipment: In transit')
            ->assertSee('59650109492')
            ->assertSee('Bluedart')
            ->assertSee('Shipment arrived')
            ->assertDontSee('secret-label.pdf')
            ->assertDontSee('Velocity');

        $this->actingAs(User::factory()->create())->get(route('orders.show', $shipment->order))->assertNotFound();
    }

    public function test_admin_page_says_not_configured_without_revealing_any_value(): void
    {
        config(['nimbuspost.enabled' => true, 'nimbuspost.email' => 'visible@example.test', 'nimbuspost.password' => 'sup3r-s3cret']);
        $order = $this->order();

        $this->actingAs($this->admin())->get(route('admin.sales.orders.show', $order))
            ->assertOk()
            ->assertSee('NimbusPost is not configured.')
            ->assertSee('NIMBUSPOST_PICKUP_WAREHOUSE_NAME')
            ->assertDontSee('sup3r-s3cret')
            ->assertDontSee('visible@example.test')
            ->assertDontSee('Velocity');
    }

    public function test_admin_page_shows_booked_shipment_details_and_valid_actions_only(): void
    {
        $shipment = $this->bookedShipment();

        $this->actingAs($this->admin())->get(route('admin.sales.orders.show', $shipment->order))
            ->assertOk()
            ->assertSee('59650109492')
            ->assertSee('Bluedart')
            ->assertSee('Cancel shipment')
            ->assertSee('Track / refresh status')
            ->assertDontSee('Create shipment');
    }

    public function test_sync_command_refreshes_in_flight_shipments_and_skips_when_unconfigured(): void
    {
        $this->fakeApi([self::API."shipments/track/*" => Http::response(["status" => true, "data" => ["status" => "x", "history" => [["status_code" => "OFD", "message" => "Out for delivery"]]]], 200)]);
        $this->artisan("nimbuspost:sync-tracking")->expectsOutputToContain("not configured")->assertSuccessful();
        Http::assertNothingSent();

        $shipment = $this->bookedShipment();

        $this->artisan("nimbuspost:sync-tracking")->expectsOutputToContain("Synced 1 of 1")->assertSuccessful();
        $this->assertSame(Shipment::OUT_FOR_DELIVERY, $shipment->fresh()->status);
    }
}