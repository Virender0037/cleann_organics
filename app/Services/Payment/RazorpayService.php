<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin REST client for Razorpay's Orders/Payments API plus the two HMAC
 * checks Razorpay's docs specify (checkout payment signature, webhook
 * signature). Deliberately not the razorpay/razorpay composer package —
 * both operations this app needs are a handful of lines against a plain
 * JSON API, so a hand-rolled client avoids an extra dependency for what
 * Laravel's own Http client and hash_hmac() already do.
 */
class RazorpayService
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    private readonly string $keyId;

    private readonly string $keySecret;

    private readonly ?string $webhookSecret;

    public function __construct()
    {
        $this->keyId = (string) config('services.razorpay.key');
        $this->keySecret = (string) config('services.razorpay.secret');
        $this->webhookSecret = config('services.razorpay.webhook_secret');
    }

    public function isConfigured(): bool
    {
        return $this->keyId !== '' && $this->keySecret !== '';
    }

    /** The public Key ID — safe to render into the checkout page's JS. */
    public function keyId(): string
    {
        return $this->keyId;
    }

    /**
     * Creates a Razorpay Order (their "order", not ours) for the given
     * amount. `payment_capture: 1` means Razorpay auto-captures on a
     * successful authorization rather than leaving it in an "authorized"
     * limbo state we'd have to separately capture.
     *
     * @return array{id: string, amount: int, currency: string}
     *
     * @throws RuntimeException when Razorpay isn't configured or the API call fails
     */
    public function createOrder(string $receipt, int $amountInPaise, string $currency = 'INR'): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Razorpay is not configured.');
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->asJson()
                ->timeout(15)
                ->post(self::BASE_URL.'/orders', [
                    'amount' => $amountInPaise,
                    'currency' => $currency,
                    'receipt' => $receipt,
                    'payment_capture' => 1,
                ])
                ->throw();
        } catch (\Throwable $e) {
            Log::error('razorpay.create_order_failed', [
                'receipt' => $receipt,
                'amount' => $amountInPaise,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('Could not create Razorpay order.', previous: $e);
        }

        return $response->json();
    }

    /**
     * The signature Razorpay Checkout hands back to the browser after a
     * successful payment: HMAC-SHA256 of "order_id|payment_id", keyed with
     * the account's Key Secret. Verifying this server-side is what proves
     * the payment actually happened rather than trusting the client's
     * "success" callback alone.
     */
    public function verifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Webhook payloads are signed separately from the checkout signature
     * above, with their own secret (set once, when the webhook is created
     * in the Razorpay Dashboard) over the raw request body.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature): bool
    {
        if (! $this->webhookSecret) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
