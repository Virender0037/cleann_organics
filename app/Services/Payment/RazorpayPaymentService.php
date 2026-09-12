<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * All state transitions for a Razorpay-backed Payment go through here, and
 * every one of them is idempotent: the checkout "success" redirect, the
 * webhook, and (if the customer reloads the pay page) a second manual
 * attempt can all call the same method for the same order without double
 * -processing it. The rule that makes that safe is simple and applied
 * everywhere below: once a payment is 'paid', nothing here downgrades or
 * overwrites it again.
 */
class RazorpayPaymentService
{
    public function __construct(private readonly RazorpayService $razorpay) {}

    /**
     * Ensures the order's Payment row has a live Razorpay order to pay
     * against, creating one if it's missing (first attempt, or a previous
     * attempt never reached Razorpay). Reuses the existing gateway_order_id
     * on a retry rather than minting a new one — Razorpay accepts repeated
     * checkout attempts against the same order id until it's paid.
     *
     * @throws RuntimeException bubbled up from RazorpayService::createOrder()
     */
    public function ensureGatewayOrder(Order $order): Payment
    {
        /** @var Payment $payment */
        $payment = $order->payment()->firstOrFail();

        if ($payment->gateway_order_id) {
            return $payment;
        }

        $gatewayOrder = $this->razorpay->createOrder(
            receipt: $order->order_number,
            amountInPaise: (int) round(((float) $payment->amount) * 100),
        );

        $payment->update(['gateway_order_id' => $gatewayOrder['id']]);

        return $payment->fresh();
    }

    /**
     * @param  array<string, mixed>  $meta  Raw gateway payload, kept for support/audit — never anything secret.
     */
    public function markCaptured(Order $order, string $gatewayPaymentId, ?string $gatewayOrderId, ?string $signature, array $meta = []): void
    {
        DB::transaction(function () use ($order, $gatewayPaymentId, $gatewayOrderId, $signature, $meta) {
            /** @var Payment $payment */
            $payment = $order->payment()->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                // Already captured by an earlier call (browser callback,
                // webhook, or a retried webhook delivery) — no-op.
                return;
            }

            if ($gatewayOrderId && $payment->gateway_order_id && $payment->gateway_order_id !== $gatewayOrderId) {
                Log::warning('razorpay.capture_order_mismatch', [
                    'order_id' => $order->id,
                    'expected_gateway_order_id' => $payment->gateway_order_id,
                    'received_gateway_order_id' => $gatewayOrderId,
                ]);

                return;
            }

            $payment->update([
                'status' => 'paid',
                'transaction_id' => $gatewayPaymentId,
                'gateway_payment_id' => $gatewayPaymentId,
                'gateway_signature' => $signature,
                'meta' => $meta ?: $payment->meta,
                'paid_at' => now(),
                'failure_reason' => null,
            ]);

            $order->forceFill(['payment_status' => 'paid'])->save();

            if ($order->order_status === 'pending') {
                $order->forceFill([
                    'order_status' => 'confirmed',
                    'confirmed_at' => now(),
                ])->save();
            }

            Log::info('razorpay.payment_captured', [
                'order_id' => $order->id,
                'gateway_payment_id' => $gatewayPaymentId,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function markFailed(Order $order, string $reason, array $meta = []): void
    {
        DB::transaction(function () use ($order, $reason, $meta) {
            /** @var Payment $payment */
            $payment = $order->payment()->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                // A late/duplicate failure notification for a payment that
                // has already succeeded by another path — never downgrade it.
                return;
            }

            $payment->update([
                'status' => 'failed',
                'failure_reason' => $reason,
                'meta' => $meta ?: $payment->meta,
            ]);

            $order->forceFill(['payment_status' => 'failed'])->save();

            Log::warning('razorpay.payment_failed', [
                'order_id' => $order->id,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * The customer closed the Razorpay modal without paying. This is purely
     * a client-side event (no gateway attempt necessarily occurred), so it
     * only records the reason — it deliberately leaves status as 'pending'
     * rather than 'failed' so the customer can immediately retry from the
     * order page without looking like something broke.
     */
    public function markCancelled(Order $order, string $reason = 'Cancelled by customer before completing payment.'): void
    {
        DB::transaction(function () use ($order, $reason) {
            /** @var Payment $payment */
            $payment = $order->payment()->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return;
            }

            $payment->update(['failure_reason' => $reason]);
        });

        Log::info('razorpay.payment_cancelled', ['order_id' => $order->id]);
    }
}
