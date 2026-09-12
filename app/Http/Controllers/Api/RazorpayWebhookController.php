<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\RazorpayPaymentService;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay's server-to-server notification of what actually happened to a
 * payment — the source of truth this app relies on independently of
 * whatever the customer's browser reports back via
 * RazorpayPaymentController::verify(). Every write this triggers goes
 * through RazorpayPaymentService, which makes both this and the browser
 * callback safe to fire for the same payment (Razorpay retries webhooks
 * that don't return 2xx, so duplicate deliveries are expected, not an edge
 * case).
 *
 * Registered in routes/api.php (stateless `api` middleware group — no CSRF,
 * no session) rather than routes/web.php, since Razorpay authenticates the
 * request via the X-Razorpay-Signature header, not a browser session.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly RazorpayPaymentService $paymentService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $rawPayload = $request->getContent();

        if ($signature === '' || ! $this->razorpay->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('razorpay.webhook_invalid_signature', ['ip' => $request->ip()]);

            return response()->json(['message' => 'invalid signature'], 400);
        }

        $data = json_decode($rawPayload, true) ?? [];
        $event = $data['event'] ?? null;
        $paymentEntity = $data['payload']['payment']['entity'] ?? null;

        if (! is_array($paymentEntity) || empty($paymentEntity['order_id'])) {
            // Not a payment event we care about (e.g. refund/order events) —
            // acknowledge so Razorpay doesn't keep retrying it.
            return response()->json(['message' => 'ignored']);
        }

        $payment = Payment::where('gateway_order_id', $paymentEntity['order_id'])->first();

        if (! $payment) {
            Log::warning('razorpay.webhook_unknown_gateway_order', ['gateway_order_id' => $paymentEntity['order_id']]);

            return response()->json(['message' => 'order not found']);
        }

        $order = $payment->order;

        match ($event) {
            'payment.captured' => $this->paymentService->markCaptured(
                $order,
                $paymentEntity['id'],
                $paymentEntity['order_id'],
                null,
                $data,
            ),
            'payment.failed' => $this->paymentService->markFailed(
                $order,
                $paymentEntity['error_description'] ?? 'Payment failed at the gateway.',
                $data,
            ),
            default => Log::info('razorpay.webhook_unhandled_event', ['event' => $event, 'order_id' => $order->id]),
        };

        return response()->json(['message' => 'ok']);
    }
}
