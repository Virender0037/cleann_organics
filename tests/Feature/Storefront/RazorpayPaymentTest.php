<?php

namespace Tests\Feature\Storefront;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RazorpayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function product(): Product
    {
        return Product::create([
            'category_id' => $this->category()->id,
            'name' => 'Green Apple',
            'slug' => 'green-apple-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product): ProductVariant
    {
        return $product->variants()->create([
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
        ]);
    }

    private function address(User $user): Address
    {
        return Address::create([
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
        ]);
    }

    private function configureRazorpay(): void
    {
        config([
            'services.razorpay.key' => 'rzp_test_key',
            'services.razorpay.secret' => 'test_secret',
            'services.razorpay.webhook_secret' => 'test_webhook_secret',
        ]);
    }

    /**
     * Fakes Razorpay's order creation, and its "fetch payment" API reporting a captured payment for exactly the
     * amount we stored (or $paidPaise when given — to simulate an under/over payment).
     */
    private function fakeGatewayOrder(string $gatewayOrderId = 'order_test123', ?int $paidPaise = null, string $currency = 'INR', string $status = 'captured'): void
    {
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => $gatewayOrderId,
                'amount' => 10000,
                'currency' => 'INR',
            ], 200),
            'https://api.razorpay.com/v1/payments/*' => function ($request) use ($gatewayOrderId, $paidPaise, $currency, $status) {
                $payment = Payment::where('gateway_order_id', $gatewayOrderId)->firstOrFail();

                return Http::response([
                    'id' => basename(parse_url($request->url(), PHP_URL_PATH)),
                    'order_id' => $gatewayOrderId,
                    'amount' => $paidPaise ?? (int) round(((float) $payment->amount) * 100),
                    'currency' => $currency,
                    'status' => $status,
                ], 200);
            },
        ]);
    }

    private function signedVerifyPayload(string $gatewayOrderId, string $paymentId): array
    {
        return [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $gatewayOrderId,
            'razorpay_signature' => hash_hmac('sha256', $gatewayOrderId.'|'.$paymentId, 'test_secret'),
        ];
    }

    public function test_verify_marks_paid_only_when_razorpay_reports_the_exact_order_amount(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_exact1');
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_exact1', 'pay_exact1'))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame('paid', $order->fresh()->payment_status);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_ends_with($request->url(), '/v1/payments/pay_exact1'));
    }

    public function test_verify_does_not_mark_paid_when_razorpay_reports_a_lower_amount(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_low1', paidPaise: 100); // ₹1
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_low1', 'pay_low1'))
            ->assertSessionHas('error');

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $order->payment->status);
        $this->assertNull($order->payment->paid_at);
        $this->assertStringContainsString('Amount mismatch', $order->payment->failure_reason);
    }

    public function test_verify_does_not_mark_paid_when_razorpay_reports_a_higher_amount(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_high1', paidPaise: 99999999);
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_high1', 'pay_high1'))->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertStringContainsString('Amount mismatch', $order->fresh()->payment->failure_reason);
    }

    public function test_verify_does_not_mark_paid_for_the_right_number_in_another_currency(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_usd1', currency: 'USD');
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_usd1', 'pay_usd1'))->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_browser_supplied_amount_fields_are_ignored(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_tamper1', paidPaise: 100);
        $order = $this->placeRazorpayOrder(User::factory()->create());
        $expected = (int) round(((float) $order->payment->amount) * 100);

        // The browser claims the "right" amount, but Razorpay says ₹1 was paid: Razorpay wins.
        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_tamper1', 'pay_tamper1') + ['amount' => $expected, 'currency' => 'INR'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_verify_marks_nothing_paid_when_razorpay_cannot_be_reached(): void
    {
        $this->configureRazorpay();
        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response(['id' => 'order_down1', 'amount' => 10000, 'currency' => 'INR'], 200),
            'https://api.razorpay.com/v1/payments/*' => Http::response([], 503),
        ]);
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_down1', 'pay_down1'))
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);
    }

    public function test_verify_refuses_a_payment_razorpay_reports_as_failed(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_failed1', status: 'failed');
        $order = $this->placeRazorpayOrder(User::factory()->create());

        $this->post(route('orders.pay.verify', $order), $this->signedVerifyPayload('order_failed1', 'pay_failed1'))
            ->assertRedirect(route('orders.pay', $order))
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    /** Places a razorpay order for the given user via a fresh cart + address, returns the Order. */
    private function placeRazorpayOrder(User $user): Order
    {
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->post('/checkout', [
            'address_id' => $address->id,
            'payment_method' => 'razorpay',
        ]);

        return Order::where('user_id', $user->id)->latest()->firstOrFail();
    }

    public function test_placing_a_razorpay_order_creates_gateway_order_and_redirects_to_pay_page(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder();

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $this->assertSame('razorpay', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('order_test123', $order->payment->gateway_order_id);

        $this->get(route('orders.pay', $order))->assertOk()->assertSee($order->order_number);
    }

    public function test_checkout_still_succeeds_even_if_gateway_order_creation_fails(): void
    {
        $this->configureRazorpay();
        Http::fake(['https://api.razorpay.com/*' => Http::response([], 500)]);

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $this->assertSame('razorpay', $order->payment_method);
        $this->assertNull($order->payment->gateway_order_id);

        // The pay page retries creating the gateway order lazily and shows
        // a friendly error rather than a crash when that retry also fails.
        $this->get(route('orders.pay', $order))->assertOk()->assertSee('try again', false);
    }

    public function test_customer_cannot_view_another_customers_pay_page(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder();

        $owner = User::factory()->create();
        $order = $this->placeRazorpayOrder($owner);

        $intruder = User::factory()->create();
        $this->actingAs($intruder)->get(route('orders.pay', $order))->assertNotFound();
    }

    public function test_pay_page_404s_for_a_non_razorpay_order(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);

        $order = Order::where('user_id', $user->id)->latest()->firstOrFail();

        $this->get(route('orders.pay', $order))->assertNotFound();
    }

    public function test_verify_with_valid_signature_marks_payment_captured_and_confirms_order(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_valid123');

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $paymentId = 'pay_valid123';
        $signature = hash_hmac('sha256', 'order_valid123|'.$paymentId, 'test_secret');

        $response = $this->post(route('orders.pay.verify', $order), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => 'order_valid123',
            'razorpay_signature' => $signature,
        ]);

        $response->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame($paymentId, $order->payment->gateway_payment_id);
        $this->assertNotNull($order->payment->paid_at);
    }

    public function test_verify_is_idempotent_on_duplicate_calls(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_dup123');

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $paymentId = 'pay_dup123';
        $signature = hash_hmac('sha256', 'order_dup123|'.$paymentId, 'test_secret');
        $payload = [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => 'order_dup123',
            'razorpay_signature' => $signature,
        ];

        $this->post(route('orders.pay.verify', $order), $payload);
        $firstPaidAt = $order->fresh()->payment->paid_at;

        // A second identical callback (e.g. a duplicated browser retry)
        // must not error, re-decrement anything, or move paid_at.
        $this->post(route('orders.pay.verify', $order), $payload)
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertTrue($firstPaidAt->equalTo($order->payment->paid_at));
    }

    public function test_verify_with_invalid_signature_marks_payment_failed(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_bad123');

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $response = $this->post(route('orders.pay.verify', $order), [
            'razorpay_payment_id' => 'pay_bad123',
            'razorpay_order_id' => 'order_bad123',
            'razorpay_signature' => 'not-a-real-signature',
        ]);

        $response->assertRedirect(route('orders.pay', $order));

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame('failed', $order->payment->status);
        $this->assertSame('pending', $order->order_status);
    }

    public function test_verify_rejects_a_gateway_order_id_that_does_not_match_the_payment(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_real123');

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $signature = hash_hmac('sha256', 'order_forged123|pay_x', 'test_secret');

        $this->post(route('orders.pay.verify', $order), [
            'razorpay_payment_id' => 'pay_x',
            'razorpay_order_id' => 'order_forged123',
            'razorpay_signature' => $signature,
        ])->assertRedirect(route('orders.pay', $order));

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);
    }

    public function test_cancel_records_reason_without_blocking_retry(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder();

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $this->postJson(route('orders.pay.cancel', $order))->assertOk();

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->payment->status);
        $this->assertNotNull($order->payment->failure_reason);
    }

    public function test_already_paid_order_redirects_away_from_pay_page(): void
    {
        $this->configureRazorpay();
        $this->fakeGatewayOrder('order_paid123');

        $user = User::factory()->create();
        $order = $this->placeRazorpayOrder($user);

        $signature = hash_hmac('sha256', 'order_paid123|pay_paid123', 'test_secret');
        $this->post(route('orders.pay.verify', $order), [
            'razorpay_payment_id' => 'pay_paid123',
            'razorpay_order_id' => 'order_paid123',
            'razorpay_signature' => $signature,
        ]);

        $this->get(route('orders.pay', $order))->assertRedirect(route('orders.show', $order));
    }

    public function test_place_order_still_accepts_cod_and_bank_transfer(): void
    {
        $user = User::factory()->create();
        $address = $this->address($user);
        $variant = $this->variant($this->product());

        $this->actingAs($user);
        $this->postJson('/cart/items', ['product_variant_id' => $variant->id, 'quantity' => 1]);

        $response = $this->post('/checkout', ['address_id' => $address->id, 'payment_method' => 'cod']);
        $order = Order::where('user_id', $user->id)->latest()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('cod', $order->payment_method);
    }
}
