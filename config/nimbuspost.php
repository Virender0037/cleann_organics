<?php

/*
|--------------------------------------------------------------------------
| NimbusPost — the only external shipping provider (Partner API v2)
|--------------------------------------------------------------------------
|
| Contract: NimbusPost's official v2 reference, https://api-v2.nimbuspost.com/docs/reference/v2
| (OpenAPI "NimbusPost — Partner API v2", 2.0.0). Every request carries an API-key PAIR as two headers:
|   x-api-key: npk_…   and   x-api-secret: …
| No login, no token, no request signing. Keys are minted in the dashboard (Settings → API Keys) and must have
| the `admin` role (a `viewer` key gets 401 on shipment create/book/cancel). If the key has an IP allowlist it
| must contain this server's exact outbound IP. PII access is NOT needed to create shipments (it only unmasks
| PII in NimbusPost's responses).
|
| Real values live ONLY in .env — never in Git, logs, Blade or JavaScript. Read through config() so
| `php artisan config:cache` works.
|
| Customer shipping charges are NOT taken from NimbusPost: they follow the storefront rules
| (Admin → Settings → Storefront & Offers). NimbusPost rates are internal/operational only.
*/

return [
    // Master switch. While false (or required settings are blank) nothing calls NimbusPost and checkout behaves as before.
    'enabled' => (bool) env('NIMBUSPOST_ENABLED', false),

    'base_url' => rtrim((string) env('NIMBUSPOST_BASE_URL', 'https://api-v2.nimbuspost.com'), '/'),

    // The API-key pair (Settings → API Keys). Both are secrets: never logged or rendered.
    'api_key' => env('NIMBUSPOST_API_KEY'),       // public key id, `npk_…`
    'api_secret' => env('NIMBUSPOST_API_SECRET'),

    // Pickup warehouse as registered in NimbusPost (`php artisan nimbuspost:check` lists them).
    'warehouse_id' => env('NIMBUSPOST_WAREHOUSE_ID'),

    // 6-digit pincode of that warehouse — the origin for serviceability checks.
    'pickup_pincode' => env('NIMBUSPOST_PICKUP_PINCODE'),

    // Seconds. A timeout is treated as "NimbusPost unavailable", never as "not serviceable".
    'timeout' => (int) env('NIMBUSPOST_TIMEOUT', 20),

    // Serviceability answers are cached per pincode + payment mode to avoid one API call per checkout.
    'serviceability_cache_minutes' => (int) env('NIMBUSPOST_SERVICEABILITY_CACHE_MINUTES', 360),

    // v2 serviceability REQUIRES parcel dimensions, which a cart doesn't have. This size (cm, "L,W,H") is used only
    // for the checkout serviceability probe — never for a booking, where the admin enters the measured parcel.
    'serviceability_package_cm' => array_map('floatval', array_pad(explode(',', (string) env('NIMBUSPOST_SERVICEABILITY_PACKAGE_CM', '10,10,10')), 3, 10)),

    // After booking, also ask NimbusPost to schedule the courier pickup (POST /v2/shipments/pickup).
    'auto_pickup' => (bool) env('NIMBUSPOST_AUTO_PICKUP', false),
];
