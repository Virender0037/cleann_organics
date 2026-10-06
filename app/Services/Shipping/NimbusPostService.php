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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The ONLY place that talks to NimbusPost. Every endpoint, field and response shape below is taken from NimbusPost's
 * published Postman collection "Nimbuspost Partners API" (https://documenter.getpostman.com/view/9692837/TW6wHnoz):
 *
 *   POST users/login             {email, password}                  → {status: true, data: "<token>"}
 *   POST courier/serviceability  {origin, destination, payment_type, order_amount, weight(g), length/breadth/height(cm)}
 *                                                                    → {status: true, data: [couriers]}; [] = not serviceable
 *   POST shipments               {order_number, payment_type, order_amount, package_*, consignee{}, pickup{}, order_items[]}
 *                                                                    → {status: true, data: {shipment_id, awb_number, courier_name, status, label, …}}
 *   GET  shipments/track/{awb}                                      → {status: true, data: {status, rto_status, history[]}}
 *   POST shipments/cancel        {awb}                              → {status: true, message: "Shipment Cancelled"}
 *
 * Failures are documented as {"status": false, "message": "…"} (with 4xx codes). Nothing here is guessed beyond that
 * collection: no webhook, no public tracking URL and no token lifetime are documented, so none is assumed.
 *
 * Secrets: the password is only ever sent in the login body; the token is cached encrypted. Neither is logged.
 */
class NimbusPostService
{
    private const TOKEN_CACHE_KEY = 'nimbuspost.token';

    public function isEnabled(): bool
    {
        return (bool) config('nimbuspost.enabled');
    }

    /** Enabled, with credentials and the pickup fields the create-shipment API requires. */
    public function isConfigured(): bool
    {
        return $this->isEnabled() && $this->missingConfiguration() === [];
    }

    /** @return array<int, string> names of the missing settings (never their values) */
    public function missingConfiguration(): array
    {
        $required = [
            'NIMBUSPOST_EMAIL' => config('nimbuspost.email'),
            'NIMBUSPOST_PASSWORD' => config('nimbuspost.password'),
            'NIMBUSPOST_PICKUP_WAREHOUSE_NAME' => config('nimbuspost.pickup.warehouse_name'),
            'NIMBUSPOST_PICKUP_NAME' => config('nimbuspost.pickup.name'),
            'NIMBUSPOST_PICKUP_ADDRESS' => config('nimbuspost.pickup.address'),
            'NIMBUSPOST_PICKUP_CITY' => config('nimbuspost.pickup.city'),
            'NIMBUSPOST_PICKUP_STATE' => config('nimbuspost.pickup.state'),
            'NIMBUSPOST_PICKUP_PINCODE' => config('nimbuspost.pickup.pincode'),
            'NIMBUSPOST_PICKUP_PHONE' => config('nimbuspost.pickup.phone'),
        ];

        return array_keys(array_filter($required, fn ($value) => blank($value)));
    }

    /**
     * Couriers able to deliver from the pickup pincode to $destinationPincode. An empty list is NimbusPost's documented
     * "not serviceable" answer; a timeout/5xx throws NimbusPostUnavailable instead (never "not serviceable").
     *
     * @return array<int, array<string, mixed>>
     */
    public function serviceableCouriers(string $destinationPincode, string $paymentType, float $orderAmount, int $weightGrams, ?array $dimensionsCm = null): array
    {
        $payload = [
            'origin' => (string) config('nimbuspost.pickup.pincode'),
            'destination' => $destinationPincode,
            'payment_type' => $paymentType,
            'order_amount' => round($orderAmount, 2),
            'weight' => max(1, $weightGrams),
        ];

        if ($dimensionsCm) {
            $payload += ['length' => $dimensionsCm['length'], 'breadth' => $dimensionsCm['width'], 'height' => $dimensionsCm['height']];
        }

        $data = $this->call('POST', 'courier/serviceability', $payload)['data'] ?? null;

        if (! is_array($data)) {
            throw new NimbusPostMalformedResponse('NimbusPost serviceability response had no courier list.');
        }

        return array_values($data);
    }

    /**
     * Books the shipment and returns NimbusPost's `data` block. Requires shipment_id and awb_number (documented
     * success fields); anything else counts as malformed so the caller never stores a half-booking as success.
     *
     * @param  array<string, mixed>  $payload  built by FulfilmentService::shipmentPayload()
     * @return array<string, mixed>
     */
    public function createShipment(array $payload): array
    {
        $data = $this->call('POST', 'shipments', $payload)['data'] ?? null;

        if (! is_array($data) || blank($data['awb_number'] ?? null) || blank($data['shipment_id'] ?? null)) {
            throw new NimbusPostMalformedResponse('NimbusPost did not return a shipment id and AWB number.');
        }

        return $data;
    }

    /** @return array<string, mixed> NimbusPost's `data` block for the AWB (status, rto_status, history…) */
    public function track(string $awb): array
    {
        $data = $this->call('GET', 'shipments/track/'.rawurlencode($awb))['data'] ?? null;

        if (! is_array($data)) {
            throw new NimbusPostMalformedResponse('NimbusPost tracking response had no data.');
        }

        return $data;
    }

    public function cancel(string $awb): void
    {
        $this->call('POST', 'shipments/cancel', ['awb' => $awb]);
    }

    // ------------------------------------------------------------------ internals

    /**
     * @return array<string, mixed> the decoded body of a {"status": true, …} response
     */
    private function call(string $method, string $path, array $payload = [], bool $retried = false): array
    {
        $this->assertConfigured();

        $response = $this->send($this->client()->withToken($this->token()), $method, $path, $payload);

        if ($response->status() === 401 && ! $retried) {
            // Token lifetime isn't documented: on a 401, log in again once and retry.
            Cache::forget(self::TOKEN_CACHE_KEY);

            return $this->call($method, $path, $payload, true);
        }

        if ($response->status() === 401) {
            throw new NimbusPostAuthenticationFailed('NimbusPost rejected the API credentials.');
        }

        return $this->decode($response, $path);
    }

    private function token(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cached)) {
            try {
                return Crypt::decryptString($cached);
            } catch (\Throwable) {
                Cache::forget(self::TOKEN_CACHE_KEY);
            }
        }

        $response = $this->send($this->client(), 'POST', 'users/login', [
            'email' => (string) config('nimbuspost.email'),
            'password' => (string) config('nimbuspost.password'),
        ], logPayload: false);

        $body = $response->json();

        if ($response->status() === 401 || (is_array($body) && ($body['status'] ?? null) === false)) {
            Log::warning('nimbuspost.login_failed', ['http_status' => $response->status()]);

            throw new NimbusPostAuthenticationFailed('NimbusPost rejected the API credentials.');
        }

        if ($response->serverError()) {
            throw new NimbusPostUnavailable('NimbusPost login is temporarily unavailable.');
        }

        $token = is_array($body) ? ($body['data'] ?? null) : null;

        if (! is_string($token) || $token === '') {
            throw new NimbusPostMalformedResponse('NimbusPost login did not return a token.');
        }

        Cache::put(self::TOKEN_CACHE_KEY, Crypt::encryptString($token), now()->addMinutes(max(1, (int) config('nimbuspost.token_cache_minutes'))));

        return $token;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('nimbuspost.base_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(max(1, (int) config('nimbuspost.timeout')));
    }

    private function send(PendingRequest $client, string $method, string $path, array $payload, bool $logPayload = true): Response
    {
        try {
            return $method === 'GET' ? $client->get($path) : $client->post($path, $payload);
        } catch (ConnectionException $e) {
            // Covers timeouts. Only the path is logged — never headers (token) or the login body.
            Log::warning('nimbuspost.unreachable', ['path' => $path, 'error' => $e->getMessage()]);

            throw new NimbusPostUnavailable('NimbusPost did not respond in time.', previous: $e);
        }
    }

    /** @return array<string, mixed> */
    private function decode(Response $response, string $path): array
    {
        if ($response->serverError()) {
            Log::warning('nimbuspost.server_error', ['path' => $path, 'http_status' => $response->status()]);

            throw new NimbusPostUnavailable('NimbusPost is temporarily unavailable.');
        }

        $body = $response->json();

        if (! is_array($body) || ! array_key_exists('status', $body)) {
            Log::warning('nimbuspost.malformed_response', ['path' => $path, 'http_status' => $response->status()]);

            throw new NimbusPostMalformedResponse('NimbusPost returned an unexpected response.');
        }

        if ($body['status'] !== true) {
            $message = trim(strip_tags((string) ($body['message'] ?? 'Request refused.')));
            Log::info('nimbuspost.rejected', ['path' => $path, 'http_status' => $response->status(), 'message' => $message]);

            throw new NimbusPostRejected(mb_substr($message, 0, 300));
        }

        return $body;
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
