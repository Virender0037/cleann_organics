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
            'payload' => ['payment' => ['entity' => ['id' => 'pay_wh1', 'order_id' => 'order_wh1']]],
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
                    'entity' => ['id' => 'pay_wh3', 'order_id' => 'order_wh3'],
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
            'payload' => ['payment' => ['entity' => ['id' => 'pay_wh5', 'order_id' => 'order_wh5']]],
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
            'payload' => ['payment' => ['entity' => ['id' => 'pay_unknown', 'order_id' => 'order_does_not_exist']]],
        ]);

        $response->assertOk();
    }
}
