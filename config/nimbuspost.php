<?php

/*
|--------------------------------------------------------------------------
| NimbusPost — the only external shipping provider
|--------------------------------------------------------------------------
|
| Contract: NimbusPost's published Postman collection "Nimbuspost Partners API"
| (https://documenter.getpostman.com/view/9692837/TW6wHnoz), base https://api.nimbuspost.com/v1:
|   POST users/login {email, password} → data = token, then "Authorization: Bearer {token}".
| Credentials are created in the NimbusPost panel (Settings → API). Real values live ONLY in .env —
| never in Git, logs, Blade or JavaScript. Read through config() so `php artisan config:cache` works.
|
| Customer shipping charges are NOT taken from NimbusPost: they follow the storefront rules
| (Admin → Settings → Storefront & Offers). NimbusPost rates are internal/operational only.
*/

return [
    // Master switch. While false (or credentials are blank) nothing calls NimbusPost and checkout behaves as before.
    'enabled' => (bool) env('NIMBUSPOST_ENABLED', false),

    'base_url' => rtrim((string) env('NIMBUSPOST_BASE_URL', 'https://api.nimbuspost.com/v1'), '/'),

    // API user credentials (NimbusPost panel → Settings → API). Secrets.
    'email' => env('NIMBUSPOST_EMAIL'),
    'password' => env('NIMBUSPOST_PASSWORD'),

    // Seconds. A timeout is treated as "NimbusPost unavailable", never as "not serviceable".
    'timeout' => (int) env('NIMBUSPOST_TIMEOUT', 20),

    // The token's lifetime is not documented; it is cached this long and re-fetched once on a 401.
    'token_cache_minutes' => (int) env('NIMBUSPOST_TOKEN_CACHE_MINUTES', 60),

    // Serviceability answers are cached per pincode + payment type to avoid one API call per checkout.
    'serviceability_cache_minutes' => (int) env('NIMBUSPOST_SERVICEABILITY_CACHE_MINUTES', 360),

    // Ask NimbusPost to request courier pickup automatically when a shipment is booked ("request_auto_pickup").
    'auto_pickup' => (bool) env('NIMBUSPOST_AUTO_PICKUP', false),

    // Pickup (warehouse) address sent with every shipment — required by the create-shipment API.
    'pickup' => [
        'warehouse_name' => env('NIMBUSPOST_PICKUP_WAREHOUSE_NAME'), // max 20 chars, as named in NimbusPost
        'name' => env('NIMBUSPOST_PICKUP_NAME'),
        'address' => env('NIMBUSPOST_PICKUP_ADDRESS'),
        'address_2' => env('NIMBUSPOST_PICKUP_ADDRESS_2'),
        'city' => env('NIMBUSPOST_PICKUP_CITY'),
        'state' => env('NIMBUSPOST_PICKUP_STATE'),
        'pincode' => env('NIMBUSPOST_PICKUP_PINCODE'),               // 6 digits; also the serviceability origin
        'phone' => env('NIMBUSPOST_PICKUP_PHONE'),                   // 10 digits
        'gst_number' => env('NIMBUSPOST_PICKUP_GST_NUMBER'),         // optional
    ],
];
