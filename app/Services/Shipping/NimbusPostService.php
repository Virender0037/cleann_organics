<?php

namespace App\Services\Shipping;

use App\Services\Shipping\Exceptions\NimbusPostAuthenticationFailed;
use App\Services\Shipping\Exceptions\NimbusPostMalformedResponse;
use App\Services\Shipping\Exceptions\NimbusPostNotConfigured;
use App\Services\Shipping\Exceptions\NimbusPostRejected;
use App\Services\Shipping\Exceptions\NimbusPostUnavailable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The ONLY place that talks to NimbusPost. Every endpoint, header and field below comes from NimbusPost's official
 * Partner API v2 reference (https://api-v2.nimbuspost.com/docs/reference/v2, OpenAPI 2.0.0):
 *
 *   Auth: headers `x-api-key` (npk_…) + `x-api-secret` on EVERY request. No token, no signing.
 *   POST /v2/serviceability    {pickupPincode, deliveryPincode, paymentMode, packages[{weight g, length, width, height}],
 *                               orderValuePaise}                         → data.available[] ([] = not serviceable)
 *   POST /v2/orders            create the order, no booking              → data.order_id
 *   POST /v2/shipments/book    {order_id}                                → data.awb, courier_name, edd, tracking_url…
 *   POST /v2/shipments/pickup  {order_id}                                → data.courier_scheduled…
 *   GET  /v2/tracking/{awb}                                              → data.orderStatus, shipment, latest{…}
 *   POST /v2/shipments/cancel  {awb, reason}                             → data.order_status = cancelled
 *   GET  /v2/warehouses                                                  → data[] {warehouse_id, name, address}
 *
 * Envelopes: success {success: true, data, meta}; failure {success: false, error: {code, detail, status}, meta}.
 * We branch on error.code (documented as stable). Secrets: the key pair goes only into request headers; headers and
 * request bodies (customer PII) are never logged.
 */
class NimbusPostService
{
    public const AUTH_FAILED_MESSAGE = 'NimbusPost authentication failed. Please verify API credentials.';

    public function isEnabled(): bool
    {
        return (bool) config('nimbuspost.enabled');
    }

    /** Enabled, with the key pair, the pickup warehouse id and its pincode. */
    public function isConfigured(): bool
    {
        return $this->isEnabled() && $this->missingConfiguration() === [];
    }

    /** @return array<int, string> names of the missing settings (never their values) */
    public function missingConfiguration(): array
    {
        $required = [
            'NIMBUSPOST_API_KEY' => config('nimbuspost.api_key'),
            'NIMBUSPOST_API_SECRET' => config('nimbuspost.api_secret'),
            'NIMBUSPOST_WAREHOUSE_ID' => config('nimbuspost.warehouse_id'),
            'NIMBUSPOST_PICKUP_PINCODE' => config('nimbuspost.pickup_pincode'),
        ];

        $missing = array_keys(array_filter($required, fn ($value) => blank($value)));

        // A base URL left over from the v1 integration would silently send v2 requests to the old host.
        if (! str_contains((string) config('nimbuspost.base_url'), 'api-v2.')) {
            $missing[] = 'NIMBUSPOST_BASE_URL (must be the v2 host https://api-v2.nimbuspost.com)';
        }

        return $missing;
    }

    /**
     * Couriers able to deliver from the pickup pincode to $destinationPincode. NimbusPost's empty `available` list is
     * the "not serviceable" answer; a timeout/5xx throws NimbusPostUnavailable instead (never "not serviceable").
     *
     * @param  array{0: float, 1: float, 2: float}  $dimensionsCm  length, width, height
     * @return array<int, array<string, mixed>>
     */
    public function serviceableCouriers(string $destinationPincode, string $paymentMode, float $orderAmount, int $weightGrams, array $dimensionsCm): array
    {
        $payload = [
            'pickupPincode' => (string) config('nimbuspost.pickup_pincode'),
            'deliveryPincode' => $destinationPincode,
            'paymentMode' => $paymentMode,
            'packages' => [[
                'weight' => max(1, $weightGrams),          // GRAMS on this endpoint
                'length' => $dimensionsCm[0],
                'width' => $dimensionsCm[1],
                'height' => $dimensionsCm[2],
            ]],
        ];

        if ($paymentMode === 'cod') {
            $payload['orderValuePaise'] = (int) round($orderAmount * 100); // required for COD
        }

        $data = $this->call('POST', '/v2/serviceability', $payload);

        if (! is_array($data) || ! array_key_exists('available', $data) || ! is_array($data['available'])) {
            throw new NimbusPostMalformedResponse('NimbusPost serviceability response had no courier list.');
        }

        return array_values($data['available']);
    }

    /**
     * Step 1 of a booking: create the NimbusPost order (no courier yet).
     *
     * @param  array<string, mixed>  $payload  built by FulfilmentService::orderPayload()
     */
    public function createOrder(array $payload): string
    {
        $data = $this->call('POST', '/v2/orders', $payload);

        if (! is_array($data) || blank($data['order_id'] ?? null)) {
            throw new NimbusPostMalformedResponse('NimbusPost did not return an order id.');
        }

        return (string) $data['order_id'];
    }

    /**
     * Step 2: book a courier for an existing NimbusPost order. Requires the documented awb in the answer; anything
     * else counts as malformed so the caller never stores a half-booking as success.
     *
     * @return array<string, mixed>
     */
    public function book(string $providerOrderId): array
    {
        $data = $this->call('POST', '/v2/shipments/book', ['order_id' => $providerOrderId]);

        if (! is_array($data) || blank($data['awb'] ?? null)) {
            throw new NimbusPostMalformedResponse('NimbusPost did not return an AWB number.');
        }

        return $data;
    }

    /** @return array<string, mixed> pickup result (`courier_scheduled`, `pickup_id`, `pickup_date`, …) */
    public function requestPickup(string $providerOrderId): array
    {
        $data = $this->call('POST', '/v2/shipments/pickup', ['order_id' => $providerOrderId]);

        return is_array($data) ? $data : [];
    }

    /** @return array<string, mixed> tracking summary (`orderStatus`, `shipment`, `latest`) for the AWB */
    public function track(string $awb): array
    {
        $data = $this->call('GET', '/v2/tracking/'.rawurlencode($awb));

        if (! is_array($data)) {
            throw new NimbusPostMalformedResponse('NimbusPost tracking response had no data.');
        }

        return $data;
    }

    public function cancel(string $awb, string $reason = 'Cancelled by seller'): void
    {
        $this->call('POST', '/v2/shipments/cancel', ['awb' => $awb, 'reason' => $reason]);
    }

    /** @return array<int, array<string, mixed>> the org's warehouses (used by `php artisan nimbuspost:check`) */
    public function warehouses(): array
    {
        $data = $this->call('GET', '/v2/warehouses');

        return is_array($data) ? array_values($data) : [];
    }

    // ------------------------------------------------------------------ internals

    /** @return mixed the `data` member of a {"success": true, …} envelope */
    private function call(string $method, string $path, array $payload = []): mixed
    {
        $this->assertConfigured();

        $client = Http::baseUrl((string) config('nimbuspost.base_url'))
            ->withHeaders([
                'x-api-key' => (string) config('nimbuspost.api_key'),
                'x-api-secret' => (string) config('nimbuspost.api_secret'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(max(1, (int) config('nimbuspost.timeout')));

        return $this->decode($this->send($client, $method, $path, $payload), $path);
    }

    private function send(PendingRequest $client, string $method, string $path, array $payload): Response
    {
        try {
            return $method === 'GET' ? $client->get($path) : $client->post($path, $payload);
        } catch (ConnectionException $e) {
            // Covers timeouts. Only the path is logged — never headers (credentials) or the body (customer PII).
            Log::warning('nimbuspost.unreachable', ['path' => $path, 'error' => class_basename($e)]);

            throw new NimbusPostUnavailable('NimbusPost did not respond in time.', previous: $e);
        }
    }

    private function decode(Response $response, string $path): mixed
    {
        $body = $response->json();
        $error = is_array($body) && is_array($body['error'] ?? null) ? $body['error'] : [];
        $code = (string) ($error['code'] ?? '');
        $requestId = is_array($body) ? ($body['meta']['requestId'] ?? null) : null; // safe to log: support correlation id

        if ($response->status() === 401 || $code === 'UNAUTHORIZED') {
            Log::warning('nimbuspost.unauthorized', ['path' => $path, 'request_id' => $requestId]);

            throw new NimbusPostAuthenticationFailed(self::AUTH_FAILED_MESSAGE);
        }

        if ($response->status() === 429 || $code === 'RATE_LIMITED' || $response->serverError()) {
            Log::warning('nimbuspost.unavailable', ['path' => $path, 'http_status' => $response->status(), 'request_id' => $requestId]);

            throw new NimbusPostUnavailable('NimbusPost is temporarily unavailable. Please try again shortly.');
        }

        if (! is_array($body) || ! array_key_exists('success', $body)) {
            Log::warning('nimbuspost.malformed_response', ['path' => $path, 'http_status' => $response->status()]);

            throw new NimbusPostMalformedResponse('NimbusPost returned an unexpected response.');
        }

        if ($body['success'] !== true) {
            // error.detail is documented as "safe to surface to end users"; it is shown to admins only.
            $detail = trim(strip_tags((string) ($error['detail'] ?? 'Request refused.')));
            Log::info('nimbuspost.rejected', ['path' => $path, 'http_status' => $response->status(), 'code' => $code, 'request_id' => $requestId]);

            throw new NimbusPostRejected(mb_substr($detail, 0, 300));
        }

        return $body['data'] ?? null;
    }

    private function assertConfigured(): void
    {
        if (! $this->isEnabled()) {
            throw new NimbusPostNotConfigured('NimbusPost is disabled (NIMBUSPOST_ENABLED=false).');
        }

        $missing = $this->missingConfiguration();

        if ($missing !== []) {
            throw new NimbusPostNotConfigured('NimbusPost is not configured. Missing: '.implode(', ', $missing).'.');
        }
    }
}
