<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function orderWithRazorpayPayment(string $gatewayOrderId): Order
    {
        $user = User::factory()->create();

        $address = Address::create([
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

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'order_number' => 'ORD-TEST-'.uniqid(),
            'subtotal' => 100,
            'grand_total' => 100,
            'payment_method' => 'razorpay',
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'shipping_name' => $address->name,
            'shipping_phone' => $address->phone,
            'shipping_address_line_1' => $address->address_line_1,
            'shipping_city' => $address->city,
            'shipping_state' => $address->state,
            'shipping_country' => $address->country,
            'shipping_pincode' => $address->pincode,
            'billing_same_as_shipping' => true,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'razorpay',
            'amount' => 100,
            'status' => 'pending',
            'gateway_order_id' => $gatewayOrderId,
        ]);

        return $order->fresh('payment');
    }

    private function postWebhook(array $payload, string $secret = 'test_webhook_secret'): TestResponse
    {
        $body = json_encode($payload);
        $signature = hash_hmac('sha256', $body, $secret);

        return $this->postJson('/api/webhooks/razorpay', $payload, ['X-Razorpay-Signature' => $signature]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.razorpay.webhook_secret' => 'test_webhook_secret']);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $order = $this->orderWithRazorpayPayment('order_wh1');

        $response = $this->postJson('/api/webhooks/razorpay', [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_wh1', 'order_id' => 'order_wh1', 'amount' => 10000, 'currency' => 'INR']]],
        ], ['X-Razorpay-Signature' => 'totally-wrong']);

        $response->assertStatus(400);
        $this->assertSame('pending', $order->payment->fresh()->status);
    }

    public function test_payment_captured_event_marks_order_paid(): void
    {
        $order = $this->orderWithRazorpayPayment('order_wh2');

        $response = $this->postWebhook([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_wh2',
                        'order_id' => 'order_wh2',
                        'status' => 'captured',
                        'amount' => 10000,
                        'currency' => 'INR',
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame('pay_wh2', $order->payment->gateway_payment_id);
    }

    public function test_duplicate_payment_captured_webhook_is_idempotent(): void
    {
        $order = $this->orderWithRazorpayPayment('order_wh3');

        $payload = [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => ['id' => 'pay_wh3', 'order_id' => 'order_wh3', 'amount' => 10000, 'currency' => 'INR'],
                ],
            ],
        ];

        $this->postWebhook($payload)->assertOk();
        $firstPaidAt = $order->fresh()->payment->paid_at;

        // Razorpay retries webhook deliveries that don't return 2xx, and can
        // also just send the same event twice — this must not error or move
        // paid_at a second time.
        $this->postWebhook($payload)->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment->status);
        $this->assertTrue($firstPaidAt->equalTo($order->payment->paid_at));
    }

    public function test_payment_failed_event_marks_order_failed(): void
    {
        $order = $this->orderWithRazorpayPayment('order_wh4');

        $response = $this->postWebhook([
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_wh4',
                        'order_id' => 'order_wh4',
                        'error_description' => 'Card declined.',
                    ],
                ],
            ],
        ]);

        $response->assertOk();

        $order->refresh();
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame('failed', $order->payment->status);
        $this->assertSame('Card declined.', $order->payment->failure_reason);
    }

    public function test_failed_event_never_downgrades_an_already_paid_payment(): void
    {
        $order = $this->orderWithRazorpayPayment('order_wh5');

        $this->postWebhook([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_wh5', 'order_id' => 'order_wh5', 'amount' => 10000, 'currency' => 'INR']]],
        ])->assertOk();

        // A late/duplicate "failed" notification for the same payment must
        // never undo a capture that already happened.
        $this->postWebhook([
            'event' => 'payment.failed',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_wh5', 'order_id' => 'order_wh5', 'error_description' => 'late failure']]],
        ])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment->status);
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_webhook_for_unknown_gateway_order_is_acknowledged_without_erroring(): void
    {
        $response = $this->postWebhook([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_unknown', 'order_id' => 'order_does_not_exist', 'amount' => 10000, 'currency' => 'INR']]],
        ]);

        $response->assertOk();
    }

    private function capturedEvent(string $gatewayOrderId, string $paymentId, mixed $amount = 10000, string $currency = 'INR'): array
    {
        $entity = ['id' => $paymentId, 'order_id' => $gatewayOrderId, 'status' => 'captured', 'currency' => $currency];
        if ($amount !== null) {
            $entity['amount'] = $amount;
        }

        return ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => $entity]]];
    }

    public function test_captured_webhook_with_the_exact_amount_marks_paid(): void
    {
        $order = $this->orderWithRazorpayPayment('order_amt_ok'); // payment amount ₹100 = 10000 paise

        $this->postWebhook($this->capturedEvent('order_amt_ok', 'pay_amt_ok', 10000))->assertOk();

        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_captured_webhook_with_a_lower_amount_is_not_marked_paid(): void
    {
        $order = $this->orderWithRazorpayPayment('order_amt_low');

        $this->postWebhook($this->capturedEvent('order_amt_low', 'pay_amt_low', 9999))->assertOk();

        $order->refresh();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('pending', $order->payment->status);
        $this->assertNull($order->payment->paid_at);
        $this->assertStringContainsString('Amount mismatch', $order->payment->failure_reason);
    }

    public function test_captured_webhook_with_a_higher_amount_is_not_marked_paid(): void
    {
        $order = $this->orderWithRazorpayPayment('order_amt_high');

        $this->postWebhook($this->capturedEvent('order_amt_high', 'pay_amt_high', 10001))->assertOk();

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_captured_webhook_without_an_amount_or_in_another_currency_is_not_marked_paid(): void
    {
        $missing = $this->orderWithRazorpayPayment('order_amt_none');
        $this->postWebhook($this->capturedEvent('order_amt_none', 'pay_amt_none', null))->assertOk();
        $this->assertSame('pending', $missing->fresh()->payment_status);

        $usd = $this->orderWithRazorpayPayment('order_amt_usd');
        $this->postWebhook($this->capturedEvent('order_amt_usd', 'pay_amt_usd', 10000, 'USD'))->assertOk();
        $this->assertSame('pending', $usd->fresh()->payment_status);
    }

    public function test_duplicate_captured_webhook_after_payment_is_a_no_op_even_with_a_different_amount(): void
    {
        $order = $this->orderWithRazorpayPayment('order_amt_dup');
        $this->postWebhook($this->capturedEvent('order_amt_dup', 'pay_amt_dup', 10000))->assertOk();
        $paidAt = $order->fresh()->payment->paid_at;

        $this->postWebhook($this->capturedEvent('order_amt_dup', 'pay_amt_dup', 10000))->assertOk();
        $this->postWebhook($this->capturedEvent('order_amt_dup', 'pay_amt_dup', 1))->assertOk();

        $payment = $order->fresh()->payment;
        $this->assertSame('paid', $payment->status);
        $this->assertNull($payment->failure_reason);
        $this->assertEquals($paidAt, $payment->paid_at);
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_captured_webhook_with_the_right_amount_but_an_invalid_signature_changes_nothing(): void
    {
        $order = $this->orderWithRazorpayPayment('order_amt_sig');

        $this->postWebhook($this->capturedEvent('order_amt_sig', 'pay_amt_sig', 10000), 'wrong_secret')->assertStatus(400);

        $this->assertSame('pending', $order->fresh()->payment_status);
    }
}
