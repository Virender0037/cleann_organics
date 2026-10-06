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
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * NimbusPost Partner API v2 integration. Request/response shapes follow the official reference
 * (https://api-v2.nimbuspost.com/docs/reference/v2). Every HTTP call is faked — nothing reaches NimbusPost.
 */
class NimbusPostFulfilmentTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api-v2.nimbuspost.com/v2/';

    private const KEY = 'npk_testkey0000';

    private const SECRET = 'test-secret-not-real';

    private function configure(array $overrides = []): void
    {
        config(array_merge([
            'nimbuspost.enabled' => true,
            'nimbuspost.base_url' => 'https://api-v2.nimbuspost.com',
            'nimbuspost.api_key' => self::KEY,
            'nimbuspost.api_secret' => self::SECRET,
            'nimbuspost.warehouse_id' => 'WH-001',
            'nimbuspost.pickup_pincode' => '122001',
            'nimbuspost.auto_pickup' => false,
            'nimbuspost.serviceability_package_cm' => [10.0, 10.0, 10.0],
        ], $overrides));
        Cache::flush();
    }

    private function ok(mixed $data, int $status = 200): PromiseInterface
    {
        return Http::response(['success' => true, 'data' => $data, 'meta' => ['requestId' => '550e8400-e29b-41d4-a716-446655440000']], $status);
    }

    private function error(string $code, string $detail, int $status): PromiseInterface
    {
        return Http::response(['success' => false, 'error' => ['type' => 'about:blank', 'title' => $code, 'status' => $status, 'code' => $code, 'detail' => $detail], 'meta' => ['requestId' => 'r-1', 'traceId' => 't-1']], $status);
    }

    private function booking(array $override = []): array
    {
        return array_merge([
            'order_id' => 'ORD-123', 'awb' => 'AWB123456789', 'courier_id' => '671e1a2b3c4d5e6f7a8b9001', 'courier_code' => 'delhivery-surface',
            'courier_name' => 'Delhivery Surface', 'pickup_id' => 'PKP-9', 'order_status' => 'booked',
            'price' => ['forward' => 75, 'rto' => 65, 'cod_charge' => 15, 'surcharges' => 5, 'insurance' => 2, 'total' => 102],
            'label_url' => '', 'routing_key' => 'BLR/HUB', 'edd' => '2026-06-18T00:00:00.000Z',
            'tracking_url' => 'https://track.nimbuspost.com/track/AWB123456789', 'tracking_short_url' => 'https://nmbr.in/abc123',
        ], $override);
    }

    /** Documented two-step booking: POST /v2/orders then POST /v2/shipments/book. */
    private function fakeBooking(array $bookingOverride = [], array $extra = []): void
    {
        Http::fake($extra + [
            self::API.'orders' => $this->ok(['order_id' => 'ORD-123', 'order_number' => 'X', 'order_status' => 'created'], 201),
            self::API.'shipments/book' => $this->ok($this->booking($bookingOverride)),
        ]);
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

    private function form(): array
    {
        return ['package_weight_grams' => 600, 'package_length_cm' => 10, 'package_width_cm' => 8, 'package_height_cm' => 20];
    }

    private function fulfilment(): FulfilmentService
    {
        return app(FulfilmentService::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function sent(string $suffix): int
    {
        return collect(Http::recorded())->filter(fn ($pair) => $pair[0]->url() === self::API.$suffix)->count();
    }

    // ------------------------------------------------------------------ configuration & auth

    public function test_disabled_by_default_and_blank_configuration_is_reported_by_name_without_any_call(): void
    {
        Http::fake();
        $this->assertFalse(app(NimbusPostService::class)->isEnabled());

        config(['nimbuspost.enabled' => true, 'nimbuspost.base_url' => 'https://api-v2.nimbuspost.com']);
        $service = app(NimbusPostService::class);
        $this->assertFalse($service->isConfigured());
        $this->assertSame(['NIMBUSPOST_API_KEY', 'NIMBUSPOST_API_SECRET', 'NIMBUSPOST_WAREHOUSE_ID', 'NIMBUSPOST_PICKUP_PINCODE'], $service->missingConfiguration());

        try {
            $service->track('123');
            $this->fail('expected NotConfigured');
        } catch (NimbusPostNotConfigured) {
            Http::assertNothingSent();
        }
    }

    public function test_a_missing_key_or_a_missing_secret_alone_blocks_configuration(): void
    {
        $this->configure(['nimbuspost.api_key' => null]);
        $this->assertSame(['NIMBUSPOST_API_KEY'], app(NimbusPostService::class)->missingConfiguration());

        $this->configure(['nimbuspost.api_secret' => '']);
        $this->assertSame(['NIMBUSPOST_API_SECRET'], app(NimbusPostService::class)->missingConfiguration());

        $this->configure();
        $this->assertTrue(app(NimbusPostService::class)->isConfigured());
    }

    public function test_a_leftover_v1_base_url_is_reported_as_misconfiguration(): void
    {
        $this->configure(['nimbuspost.base_url' => 'https://api.nimbuspost.com/v1']);

        $missing = app(NimbusPostService::class)->missingConfiguration();

        $this->assertCount(1, $missing);
        $this->assertStringContainsString('NIMBUSPOST_BASE_URL', $missing[0]);
    }

    public function test_every_request_carries_the_documented_key_pair_headers_and_no_login_or_bearer(): void
    {
        $this->configure();
        Http::fake([self::API.'tracking/*' => $this->ok(['orderStatus' => 'booked', 'latest' => null])]);

        app(NimbusPostService::class)->track('AWB1');
        app(NimbusPostService::class)->track('AWB2');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => $r->hasHeader('x-api-key', self::KEY) && $r->hasHeader('x-api-secret', self::SECRET) && ! $r->hasHeader('Authorization'));
        Http::assertNotSent(fn (Request $r) => str_contains($r->url(), 'login'));
    }

    public function test_rejected_credentials_raise_a_safe_authentication_error(): void
    {
        $this->configure();
        Http::fake([self::API.'tracking/*' => $this->error('UNAUTHORIZED', 'Missing or invalid API key. Send both x-api-key and x-api-secret headers.', 401)]);

        try {
            app(NimbusPostService::class)->track('AWB1');
            $this->fail('expected AuthenticationFailed');
        } catch (NimbusPostAuthenticationFailed $e) {
            $this->assertSame('NimbusPost authentication failed. Please verify API credentials.', $e->getMessage());
            $this->assertStringNotContainsString(self::KEY, $e->getMessage());
            $this->assertStringNotContainsString(self::SECRET, $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ serviceability

    public function test_serviceability_request_shape_and_serviceable_unserviceable_unavailable(): void
    {
        $this->configure();
        Http::fake([self::API.'serviceability' => Http::sequence()
            ->push(['success' => true, 'data' => ['available' => [['courierId' => 'c1', 'courierName' => 'Delhivery Surface', 'result' => ['totalPaise' => 10200]]], 'excluded' => []], 'meta' => ['requestId' => 'r']], 200)
            ->push(['success' => true, 'data' => ['available' => [], 'excluded' => [['courierId' => 'c3', 'reason' => 'NO_COD_SERVICE']]], 'meta' => ['requestId' => 'r']], 200)
            ->pushFailedConnection()]);
        $fulfilment = $this->fulfilment();

        $this->assertTrue($fulfilment->checkoutServiceability('411001', 'cod', 450, 600));
        $this->assertFalse($fulfilment->checkoutServiceability('999999', 'cod', 450, 600));
        $this->assertNull($fulfilment->checkoutServiceability('110001', 'prepaid', 450, 600)); // timeout ≠ not serviceable

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'serviceability'
            && $r['pickupPincode'] === '122001' && $r['deliveryPincode'] === '411001' && $r['paymentMode'] === 'cod'
            && $r['packages'] === [['weight' => 600, 'length' => 10.0, 'width' => 10.0, 'height' => 10.0]]
            && $r['orderValuePaise'] === 45000);
        Http::assertSent(fn (Request $r) => $r->url() === self::API.'serviceability' && $r['paymentMode'] === 'prepaid' && ! isset($r['orderValuePaise']));
    }

    public function test_checkout_refuses_an_explicitly_unserviceable_pincode_but_not_an_unreachable_api(): void
    {
        $this->configure();
        $user = User::factory()->create();
        $address = Address::create(['user_id' => $user->id, 'type' => 'shipping', 'name' => 'A', 'phone' => '9876543210', 'address_line_1' => 'x', 'city' => 'Pune', 'state' => 'MH', 'country' => 'India', 'pincode' => '999999', 'is_default' => true]);
        $variant = $this->order()->items->first()->variant;

        Http::fake([self::API.'serviceability' => $this->ok(['available' => [], 'excluded' => []])]);
        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, "can't deliver to PIN code 999999"));
        $this->assertSame(0, Order::where('user_id', $user->id)->count());

        $this->configure();
        Http::fake([self::API.'serviceability' => fn () => throw new ConnectionException('timed out')]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])->assertRedirect();
        $this->assertSame(1, Order::where('user_id', $user->id)->count());
    }

    public function test_checkout_makes_no_nimbuspost_call_while_disabled(): void
    {
        Http::fake();
        $user = User::factory()->create();
        $address = Address::create(['user_id' => $user->id, 'type' => 'shipping', 'name' => 'A', 'phone' => '9876543210', 'address_line_1' => 'x', 'city' => 'Pune', 'state' => 'MH', 'country' => 'India', 'pincode' => '411001', 'is_default' => true]);
        $variant = $this->order()->items->first()->variant;

        $this->actingAs($user)->post(route('cart.items.store'), ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('checkout.store'), ['address_id' => $address->id, 'payment_method' => 'cod'])->assertRedirect();

        Http::assertNothingSent();
    }

    // ------------------------------------------------------------------ payment gate

    public function test_payment_gate_per_payment_method(): void
    {
        $this->configure();
        $f = $this->fulfilment();

        $this->assertSame([], $f->blockers($this->order('cod', 'pending', 'confirmed')));
        $this->assertContains('Confirm this Cash on Delivery order first.', $f->blockers($this->order('cod', 'pending', 'pending')));
        $this->assertSame([], $f->blockers($this->order('razorpay', 'paid', 'confirmed')));
        $this->assertContains('The Razorpay payment has not been received.', $f->blockers($this->order('razorpay', 'pending', 'pending')));
        $this->assertStringContainsString('Manual UPI payment has not been verified', implode(' ', $f->blockers($this->order('manual_upi', 'pending', 'pending'))));
        $this->assertSame([], $f->blockers($this->order('manual_upi', 'paid', 'confirmed')));
        $this->assertStringContainsString('bank transfer has not been verified', implode(' ', $f->blockers($this->order('bank_transfer', 'pending', 'pending'))));
        $this->assertSame([], $f->blockers($this->order('bank_transfer', 'paid', 'confirmed')));
    }

    public function test_bank_transfer_can_be_verified_by_admin_and_then_shipped_as_prepaid(): void
    {
        $this->configure();
        $this->fakeBooking();
        $order = $this->order('bank_transfer', 'pending', 'pending');

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), $this->form())->assertSessionHas('error');
        Http::assertNothingSent();

        $this->actingAs($this->admin())->patch(route('admin.sales.payments.verify', $order->payment), ['admin_note' => 'NEFT received'])->assertRedirect();
        $this->assertSame('confirmed', $order->fresh()->order_status);

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), $this->form())->assertSessionHas('success');
        Http::assertSent(fn (Request $r) => $r->url() === self::API.'orders' && $r['payment_mode'] === 'prepaid' && ! isset($r['order_collectable_amount']));
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
    }

    // ------------------------------------------------------------------ shipment creation

    public function test_cod_booking_uses_the_documented_two_step_flow_and_stores_awb_courier_tracking(): void
    {
        $this->configure();
        $this->fakeBooking();
        $order = $this->order('cod', 'pending', 'confirmed');

        $shipment = $this->fulfilment()->createShipment($order, $this->package());

        Http::assertSent(function (Request $r) use ($order) {
            return $r->url() === self::API.'orders'
                && $r['order_number'] === $order->order_number
                && $r['order_type'] === 'b2c'
                && $r['payment_mode'] === 'cod'
                && $r['order_collectable_amount'] == 450
                && $r['warehouse_id'] === 'WH-001'
                && $r['shipping_address']['phone'] === 9876543210          // number, normalised from "+91 98765 43210"
                && $r['shipping_address']['pincode'] === 411001          // number
                && $r['shipping_address']['name'] === 'Asha Rao'
                && $r['items'] === [['name' => 'Floor Cleaner', 'qty' => 1, 'price' => 450.0, 'sku' => 'FC-500']]
                && $r['package'] === ['weight' => 0.6, 'length' => 10.0, 'width' => 8.0, 'height' => 20.0]; // KG
        });
        Http::assertSent(fn (Request $r) => $r->url() === self::API.'shipments/book' && $r->data() === ['order_id' => 'ORD-123']);

        $this->assertSame(Shipment::BOOKED, $shipment->status);
        $this->assertSame('ORD-123', $shipment->provider_order_id);
        $this->assertSame('AWB123456789', $shipment->awb_number);
        $this->assertSame('Delhivery Surface', $shipment->courier_name);
        $this->assertSame('PKP-9', $shipment->pickup_id);
        $this->assertSame('https://track.nimbuspost.com/track/AWB123456789', $shipment->tracking_url);
        $this->assertSame('2026-06-18', $shipment->estimated_delivery_at->toDateString());
        $this->assertEquals(450, $shipment->cod_amount);
        $this->assertSame('pending', $order->fresh()->payment_status); // shipping never touches payment
    }

    public function test_razorpay_paid_order_ships_as_prepaid_with_zero_cod(): void
    {
        $this->configure();
        $this->fakeBooking();

        $shipment = $this->fulfilment()->createShipment($this->order('razorpay', 'paid', 'confirmed'), $this->package());

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'orders' && $r['payment_mode'] === 'prepaid' && ! isset($r['order_collectable_amount']));
        $this->assertSame('prepaid', $shipment->payment_type);
        $this->assertEquals(0, $shipment->cod_amount);
    }

    public function test_unverified_manual_upi_cannot_be_shipped_and_nothing_is_sent(): void
    {
        $this->configure();
        Http::fake();

        try {
            $this->fulfilment()->createShipment($this->order('manual_upi', 'pending', 'pending'), $this->package());
            $this->fail('expected FulfilmentNotAllowed');
        } catch (FulfilmentNotAllowed) {
            Http::assertNothingSent();
            $this->assertSame(0, Shipment::count());
        }
    }

    public function test_package_dimensions_are_required_for_a_booking(): void
    {
        $this->configure();
        Http::fake();
        $order = $this->order();

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), ['package_weight_grams' => 600])
            ->assertSessionHasErrorsIn('shipment', ['package_length_cm', 'package_width_cm', 'package_height_cm']);
        Http::assertNothingSent();
    }

    public function test_second_create_is_refused_and_only_one_booking_is_sent(): void
    {
        $this->configure();
        $this->fakeBooking();
        $order = $this->order();

        $this->fulfilment()->createShipment($order, $this->package());
        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), $this->form())
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'already has a live shipment'));

        $this->assertSame(1, Shipment::count());
        $this->assertSame(1, $this->sent('orders'));
        $this->assertSame(1, $this->sent('shipments/book'));
    }

    public function test_the_database_guard_blocks_a_concurrent_second_booking(): void
    {
        $order = $this->order();
        Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::CREATING, 'payment_type' => 'cod', 'package_weight_grams' => 600]);

        $this->expectException(UniqueConstraintViolationException::class);
        Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::CREATING, 'payment_type' => 'cod', 'package_weight_grams' => 600]);
    }

    public function test_no_courier_failure_keeps_the_nimbuspost_order_and_the_retry_rebooks_it_on_the_same_row(): void
    {
        $this->configure();
        Http::fake([
            self::API.'orders' => $this->ok(['order_id' => 'ORD-777', 'order_number' => 'X', 'order_status' => 'created'], 201),
            self::API.'shipments/book' => Http::sequence()
                ->push(['success' => false, 'error' => ['code' => 'VALIDATION_FAILED', 'status' => 400, 'detail' => 'No serviceable courier is available for this order.'], 'meta' => ['requestId' => 'r']], 400)
                ->push(['success' => true, 'data' => $this->booking(['order_id' => 'ORD-777']), 'meta' => ['requestId' => 'r']], 200),
        ]);
        $order = $this->order();

        $failed = $this->fulfilment()->createShipment($order, $this->package());
        $this->assertSame(Shipment::FAILED, $failed->status);
        $this->assertNull($failed->active_order_id);
        $this->assertSame('ORD-777', $failed->provider_order_id);
        $this->assertSame('No serviceable courier is available for this order.', $failed->failure_reason);

        $retry = $this->fulfilment()->createShipment($order->fresh(), $this->package());

        $this->assertSame($failed->id, $retry->id);                    // same shipment record
        $this->assertSame(Shipment::BOOKED, $retry->status);
        $this->assertSame(1, Shipment::where('order_id', $order->id)->count());
        $this->assertSame(1, $this->sent('orders'));                    // NimbusPost order created once only
        $this->assertSame(2, $this->sent('shipments/book'));
    }

    public function test_an_earlier_failed_attempt_without_a_nimbuspost_order_is_reused_by_the_retry(): void
    {
        // Mirrors ORD-20261006-T3LPUE: the v1 attempt failed at authentication, before anything reached NimbusPost.
        $this->configure();
        $this->fakeBooking();
        $order = $this->order();
        $old = Shipment::create(['order_id' => $order->id, 'status' => Shipment::FAILED, 'payment_type' => 'cod', 'cod_amount' => 450, 'package_weight_grams' => 50, 'failure_reason' => 'NimbusPost rejected the API credentials.']);

        $shipment = $this->fulfilment()->createShipment($order, $this->package());

        $this->assertSame($old->id, $shipment->id);
        $this->assertSame(Shipment::BOOKED, $shipment->status);
        $this->assertNull($shipment->failure_reason);
        $this->assertSame(1, Shipment::where('order_id', $order->id)->count());
        $this->assertSame(1, $this->sent('orders'));
    }

    public function test_a_timeout_keeps_the_order_locked_until_an_admin_releases_it(): void
    {
        $this->configure();
        Http::fake([
            self::API.'orders' => Http::sequence()->pushFailedConnection()->push(['success' => true, 'data' => ['order_id' => 'ORD-9', 'order_status' => 'created'], 'meta' => ['requestId' => 'r']], 201),
            self::API.'shipments/book' => $this->ok($this->booking()),
        ]);
        $order = $this->order();

        $unconfirmed = $this->fulfilment()->createShipment($order, $this->package());
        $this->assertSame(Shipment::CREATION_UNCONFIRMED, $unconfirmed->status);
        $this->assertSame($order->id, $unconfirmed->active_order_id);

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), $this->form())->assertSessionHas('error');
        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.release', [$order, $unconfirmed]))->assertSessionHas('success');

        $this->assertSame(Shipment::BOOKED, $this->fulfilment()->createShipment($order->fresh(), $this->package())->status);
        $this->assertSame(1, Shipment::where('order_id', $order->id)->count());
        $order->refresh();
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('pending', $order->payment_status);
    }

    public function test_authentication_failure_during_booking_is_recorded_safely_and_can_be_retried(): void
    {
        $this->configure();
        Http::fake([self::API.'orders' => $this->error('UNAUTHORIZED', 'Missing or invalid API key.', 401)]);
        $order = $this->order();

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.store', $order), $this->form())
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'NimbusPost authentication failed. Please verify API credentials.') && ! str_contains($m, self::SECRET));

        $shipment = Shipment::firstOrFail();
        $this->assertSame(Shipment::FAILED, $shipment->status);
        $this->assertNull($shipment->active_order_id);
    }

    public function test_a_malformed_booking_response_is_never_stored_as_booked(): void
    {
        $this->configure();
        $this->fakeBooking(['awb' => '']);

        $shipment = $this->fulfilment()->createShipment($this->order(), $this->package());

        $this->assertSame(Shipment::FAILED, $shipment->status);
        $this->assertNull($shipment->awb_number);
    }

    public function test_non_json_and_server_errors_and_rate_limits(): void
    {
        $this->configure();
        Http::fake([self::API.'tracking/*' => Http::sequence()
            ->push('<html>gateway</html>', 200)
            ->push('', 503)
            ->push(['success' => false, 'error' => ['code' => 'RATE_LIMITED', 'status' => 429, 'detail' => 'Too many requests'], 'meta' => ['requestId' => 'r']], 429)]);
        $service = app(NimbusPostService::class);

        foreach ([NimbusPostMalformedResponse::class, NimbusPostUnavailable::class, NimbusPostUnavailable::class] as $expected) {
            try {
                $service->track('A');
                $this->fail('expected '.$expected);
            } catch (\Throwable $e) {
                $this->assertInstanceOf($expected, $e);
            }
        }
    }

    public function test_auto_pickup_is_requested_after_booking_and_its_failure_never_undoes_the_booking(): void
    {
        $this->configure(['nimbuspost.auto_pickup' => true]);
        $this->fakeBooking([], [self::API.'shipments/pickup' => Http::sequence()
            ->push(['success' => true, 'data' => ['order_id' => 'ORD-123', 'pickup_id' => 'PKP-10', 'pickup_date' => '2026-07-01', 'courier_scheduled' => true, 'internal_pickup_id' => ''], 'meta' => ['requestId' => 'r']], 200)
            ->push(['success' => false, 'error' => ['code' => 'VALIDATION_FAILED', 'status' => 400, 'detail' => 'Order not booked'], 'meta' => ['requestId' => 'r']], 400)]);

        $shipment = $this->fulfilment()->createShipment($this->order(), $this->package());

        Http::assertSent(fn (Request $r) => $r->url() === self::API.'shipments/pickup' && $r['order_id'] === 'ORD-123');
        $this->assertTrue($shipment->pickup_requested);
        $this->assertSame('PKP-10', $shipment->pickup_id);

        $second = $this->fulfilment()->createShipment($this->order(), $this->package());
        $this->assertSame(Shipment::BOOKED, $second->status);
        $this->assertFalse($second->pickup_requested);
    }

    // ------------------------------------------------------------------ tracking / cancel

    private function bookedShipment(): Shipment
    {
        $this->configure();
        $order = $this->order();

        return Shipment::create(['order_id' => $order->id, 'active_order_id' => $order->id, 'status' => Shipment::BOOKED, 'payment_type' => 'cod', 'cod_amount' => 450, 'package_weight_grams' => 600, 'provider_order_id' => 'ORD-123', 'awb_number' => 'AWB123456789', 'courier_name' => 'Delhivery Surface', 'tracking_url' => 'https://track.nimbuspost.com/track/AWB123456789', 'booked_at' => now()]);
    }

    private function trackingSummary(array $override = []): array
    {
        return array_replace_recursive([
            'orderId' => 'ORD-123', 'orderNumber' => 'X', 'orderStatus' => 'booked', 'paymentMode' => 'cod',
            'shipment' => ['awb' => 'AWB123456789', 'courierName' => 'Delhivery Surface', 'edd' => '2026-06-18T00:00:00.000Z', 'pickedAt' => null],
            'latest' => ['shipStatus' => 'in transit', 'statusCode' => '001-IT', 'eventTime' => '2026-06-13T10:30:00.000Z', 'location' => 'Bengaluru Hub', 'message' => 'In transit'],
        ], $override);
    }

    public function test_tracking_stores_the_latest_event_and_maps_only_documented_statuses(): void
    {
        $shipment = $this->bookedShipment();
        Http::fake([self::API.'tracking/AWB123456789' => Http::sequence()
            ->push(['success' => true, 'data' => $this->trackingSummary(), 'meta' => ['requestId' => 'r']], 200)
            ->push(['success' => true, 'data' => $this->trackingSummary(), 'meta' => ['requestId' => 'r']], 200)
            ->push(['success' => true, 'data' => $this->trackingSummary(['orderStatus' => 'delivered', 'latest' => ['statusCode' => '009-DL', 'shipStatus' => 'delivered', 'eventTime' => '2026-06-14T10:00:00.000Z']]), 'meta' => ['requestId' => 'r']], 200)]);
        $f = $this->fulfilment();

        $after = $f->refreshTracking($shipment);
        $this->assertSame(Shipment::BOOKED, $after->status);
        $this->assertSame('001-IT', $after->provider_status);
        $this->assertSame('in transit', $after->latestUpdate()['status']);
        $this->assertSame('2026-06-18', $after->estimated_delivery_at->toDateString());

        $after = $f->refreshTracking($after);                          // same event again → not duplicated
        $this->assertCount(1, $after->tracking_history);

        $after = $f->refreshTracking($after);                          // undocumented "delivered" → NOT inferred
        $this->assertSame(Shipment::BOOKED, $after->status);
        $this->assertSame('009-DL', $after->provider_status);
        $this->assertCount(2, $after->tracking_history);
        $this->assertNotSame('delivered', $after->order->fresh()->order_status);
    }

    public function test_a_documented_pickup_time_makes_the_shipment_in_transit_and_not_cancellable(): void
    {
        $shipment = $this->bookedShipment();
        Http::fake([self::API.'tracking/*' => $this->ok($this->trackingSummary(['shipment' => ['pickedAt' => '2026-06-12T10:00:00.000Z']]))]);

        $after = $this->fulfilment()->refreshTracking($shipment);

        $this->assertSame(Shipment::IN_TRANSIT, $after->status);
        $this->assertFalse($after->isCancellable());
    }

    public function test_a_tracked_cancelled_status_releases_the_order(): void
    {
        $shipment = $this->bookedShipment();
        Http::fake([self::API.'tracking/*' => $this->ok($this->trackingSummary(['orderStatus' => 'cancelled']))]);

        $after = $this->fulfilment()->refreshTracking($shipment);

        $this->assertSame(Shipment::CANCELLED, $after->status);
        $this->assertNull($after->active_order_id);
    }

    public function test_cancellation_success_and_refusal(): void
    {
        $shipment = $this->bookedShipment();
        Http::fake([self::API.'shipments/cancel' => Http::sequence()
            ->push(['success' => false, 'error' => ['code' => 'NOT_FOUND', 'status' => 404, 'detail' => 'No shipment found for AWB AWB123456789.'], 'meta' => ['requestId' => 'r']], 404)
            ->push(['success' => true, 'data' => ['order_id' => 'ORD-123', 'awb' => 'AWB123456789', 'order_status' => 'cancelled', 'refund_amount' => 102], 'meta' => ['requestId' => 'r']], 200)]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'No shipment found'));
        $this->assertSame(Shipment::BOOKED, $shipment->fresh()->status);

        $this->actingAs($admin)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))->assertSessionHas('success');
        $shipment->refresh();
        $this->assertSame(Shipment::CANCELLED, $shipment->status);
        $this->assertNull($shipment->active_order_id);
        Http::assertSent(fn (Request $r) => $r->url() === self::API.'shipments/cancel' && $r['awb'] === 'AWB123456789' && filled($r['reason']));
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

        $this->post(route('admin.sales.orders.shipments.store', $shipment->order), $this->form())->assertRedirect();
        $this->actingAs($customer)->post(route('admin.sales.orders.shipments.store', $shipment->order), $this->form())->assertRedirect();
        $this->actingAs($customer)->post(route('admin.sales.orders.shipments.cancel', [$shipment->order, $shipment]))->assertRedirect();

        Http::assertNothingSent();
        $this->assertSame(Shipment::BOOKED, $shipment->fresh()->status);
    }

    public function test_a_shipment_cannot_be_acted_on_through_another_order(): void
    {
        $shipment = $this->bookedShipment();

        $this->actingAs($this->admin())->post(route('admin.sales.orders.shipments.cancel', [$this->order(), $shipment]))->assertNotFound();
    }

    public function test_customer_sees_safe_tracking_with_the_official_link_and_nothing_internal(): void
    {
        $shipment = $this->bookedShipment();
        $shipment->update(['label_url' => 'https://nimubs-assets.s3.amazonaws.com/labels/secret-label.pdf', 'estimated_delivery_at' => '2026-06-18 00:00:00', 'tracking_history' => [['status_code' => '001-IT', 'status' => 'in transit', 'location' => 'Pune Hub', 'event_time' => '2026-06-13T10:30:00.000Z', 'message' => 'Shipment arrived']]]);

        $this->actingAs($shipment->order->user)->get(route('orders.show', $shipment->order))
            ->assertOk()
            ->assertSee('Shipment: Shipment booked')
            ->assertSee('AWB123456789')
            ->assertSee('Delhivery Surface')
            ->assertSee('Expected delivery: 18 Jun 2026')
            ->assertSee('href="https://track.nimbuspost.com/track/AWB123456789"', false)
            ->assertSee('Shipment arrived')
            ->assertDontSee('secret-label.pdf')
            ->assertDontSee('ORD-123')                                    // NimbusPost internal order id
            ->assertDontSee('2026-06-13T10:30:00.000Z');                 // formatted, not raw

        $this->actingAs(User::factory()->create())->get(route('orders.show', $shipment->order))->assertNotFound();
    }

    public function test_admin_page_says_not_configured_without_revealing_any_value(): void
    {
        config(['nimbuspost.enabled' => true, 'nimbuspost.base_url' => 'https://api-v2.nimbuspost.com', 'nimbuspost.api_key' => 'npk_visiblekey', 'nimbuspost.api_secret' => 'sup3r-s3cret']);
        $order = $this->order();

        $this->actingAs($this->admin())->get(route('admin.sales.orders.show', $order))
            ->assertOk()
            ->assertSee('NimbusPost is not configured.')
            ->assertSee('NIMBUSPOST_WAREHOUSE_ID')
            ->assertDontSee('sup3r-s3cret')
            ->assertDontSee('npk_visiblekey');
    }

    public function test_admin_page_shows_the_booked_shipment_and_valid_actions_only(): void
    {
        $shipment = $this->bookedShipment();
        $shipment->update(['estimated_delivery_at' => '2026-06-18 00:00:00', 'pickup_id' => 'PKP-9']);

        $this->actingAs($this->admin())->get(route('admin.sales.orders.show', $shipment->order))
            ->assertOk()
            ->assertSee('AWB123456789')
            ->assertSee('Delhivery Surface')
            ->assertSee('NimbusPost tracking page')
            ->assertSee('18 Jun 2026')
            ->assertSee('PKP-9')
            ->assertSee('Cancel shipment')
            ->assertSee('Track / refresh status')
            ->assertDontSee('Create shipment');
    }

    // ------------------------------------------------------------------ commands

    public function test_sync_command_refreshes_in_flight_shipments_and_skips_when_unconfigured(): void
    {
        Http::fake([self::API.'tracking/*' => $this->ok($this->trackingSummary())]);
        $this->artisan('nimbuspost:sync-tracking')->expectsOutputToContain('not configured')->assertSuccessful();
        Http::assertNothingSent();

        $shipment = $this->bookedShipment();
        $this->artisan('nimbuspost:sync-tracking')->expectsOutputToContain('Synced 1 of 1')->assertSuccessful();
        $this->assertSame('001-IT', $shipment->fresh()->provider_status);
    }

    public function test_check_command_lists_warehouses_with_only_the_key_pair_so_the_id_can_be_found(): void
    {
        $this->configure(['nimbuspost.warehouse_id' => null, 'nimbuspost.pickup_pincode' => null]);
        Http::fake([self::API.'warehouses' => $this->ok([['warehouse_id' => 'WH-777', 'name' => 'Delhi Store', 'address' => ['city' => 'New Delhi', 'pincode' => 110059]]])]);

        $this->artisan('nimbuspost:check')
            ->expectsOutputToContain('NIMBUSPOST_WAREHOUSE_ID     MISSING')
            ->expectsOutputToContain('Authentication OK')
            ->expectsOutputToContain('WH-777')
            ->expectsOutputToContain('Set NIMBUSPOST_WAREHOUSE_ID to one of the warehouse ids listed above')
            ->assertFailed();

        // Shipment-related calls still refuse to run without the warehouse id.
        $this->expectException(NimbusPostNotConfigured::class);
        app(NimbusPostService::class)->track('AWB1');
    }

    public function test_check_command_verifies_auth_and_warehouse_without_printing_secrets(): void
    {
        $this->configure();
        Http::fake([self::API.'warehouses' => Http::sequence()
            ->push(['success' => true, 'data' => [['warehouse_id' => 'WH-001', 'name' => 'Main Warehouse', 'address' => ['city' => 'Gurgaon', 'pincode' => 122001]]], 'meta' => ['requestId' => 'r']], 200)
            ->push(['success' => false, 'error' => ['code' => 'UNAUTHORIZED', 'status' => 401, 'detail' => 'Missing or invalid API key.'], 'meta' => ['requestId' => 'r']], 401)]);

        $this->artisan('nimbuspost:check')
            ->expectsOutputToContain('NIMBUSPOST_API_KEY          set')
            ->expectsOutputToContain('Authentication OK')
            ->expectsOutputToContain('matches a NimbusPost warehouse')
            ->doesntExpectOutputToContain(self::KEY)
            ->doesntExpectOutputToContain(self::SECRET)
            ->assertSuccessful();

        $this->artisan('nimbuspost:check')->expectsOutputToContain('NimbusPost authentication failed')->assertFailed();
    }
}
