<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\Exceptions\NimbusPostException;
use App\Services\Shipping\Exceptions\NimbusPostUnavailable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Business rules around courier bookings. Controllers call this; only NimbusPostService talks to the API.
 *
 * - Payment gate: Order::fulfilmentBlockedReason() (COD once confirmed; everything else once paid/verified).
 * - One live shipment per order: `shipments.active_order_id` is UNIQUE and is claimed BEFORE any API call, so double
 *   clicks / concurrent requests cannot book twice. A timeout keeps the claim (NimbusPost may have acted) until an
 *   admin confirms otherwise and releases it.
 * - Booking uses NimbusPost v2's documented two-step flow — create the order, then book it — and stores the
 *   NimbusPost order id as soon as it exists. A failed booking is retried on the SAME NimbusPost order and the SAME
 *   shipment row, so retries never create duplicate NimbusPost orders or duplicate shipment records.
 * - Shipping never touches payment_status. Customer shipping charges are not affected by courier rates.
 */
class FulfilmentService
{
    public function __construct(private readonly NimbusPostService $nimbus) {}

    /**
     * Everything that stops a booking right now, worded for admins. Empty = can create.
     *
     * @return array<int, string>
     */
    public function blockers(Order $order): array
    {
        $blockers = [];

        if (! $this->nimbus->isConfigured()) {
            $blockers[] = 'NimbusPost is not configured.';
        }

        if ($reason = $order->fulfilmentBlockedReason()) {
            $blockers[] = $reason;
        }

        if (in_array($order->order_status, ['delivered'], true)) {
            $blockers[] = 'This order is already delivered.';
        }

        if ($order->activeShipment()->exists()) {
            $blockers[] = 'This order already has a live shipment.';
        }

        if (! preg_match('/^\d{6}$/', (string) $order->shipping_pincode)) {
            $blockers[] = 'The delivery pincode must be 6 digits.';
        }

        if ($this->tenDigitPhone($order->shipping_phone) === null) {
            $blockers[] = 'The delivery phone number must be a 10-digit mobile number.';
        }

        $order->loadMissing('items');
        if ($order->items->contains(fn ($item) => blank($item->variant_sku))) {
            $blockers[] = 'Every item needs a SKU (NimbusPost requires one per item).';
        }

        return $blockers;
    }

    /**
     * Prefill for the admin's package form: total weight from the order-item snapshots (kg → g), and dimensions only
     * when every unit is one variant with all three dimensions recorded — multi-item parcels are measured by the
     * packer, never guessed.
     *
     * @return array{weight_grams: ?int, length_cm: ?float, width_cm: ?float, height_cm: ?float, warnings: array<int, string>}
     */
    public function packageSuggestion(Order $order): array
    {
        $order->loadMissing('items.variant');
        $warnings = [];
        $grams = 0;
        $weightKnown = true;

        foreach ($order->items as $item) {
            if ($item->weight === null || (float) $item->weight <= 0) {
                $weightKnown = false;
                $warnings[] = "No weight recorded for \"{$item->product_name}\".";
            } else {
                $grams += (int) round((float) $item->weight * 1000) * (int) $item->quantity; // variant weight is kg
            }
        }

        $dimensions = ['length_cm' => null, 'width_cm' => null, 'height_cm' => null];
        $single = $order->items->count() === 1 && (int) $order->items->first()->quantity === 1 ? $order->items->first()->variant : null;

        if ($single && $single->length_cm && $single->width_cm && $single->height_cm) {
            $dimensions = ['length_cm' => (float) $single->length_cm, 'width_cm' => (float) $single->width_cm, 'height_cm' => (float) $single->height_cm];
        } else {
            $missing = $order->items->filter(fn ($item) => ! $item->variant || ! $item->variant->length_cm || ! $item->variant->width_cm || ! $item->variant->height_cm);
            if ($missing->isNotEmpty()) {
                $warnings[] = 'Package dimensions are incomplete for: '.$missing->pluck('product_name')->unique()->implode(', ').'. Measure the packed parcel.';
            } else {
                $warnings[] = 'Several items: measure the packed parcel.';
            }
        }

        return ['weight_grams' => $weightKnown ? $grams : null] + $dimensions + ['warnings' => $warnings];
    }

    /**
     * Books the order with NimbusPost. Returns the Shipment (booked, failed or unconfirmed); throws only when the
     * booking may not be attempted (gate/duplicate), with an admin-safe message.
     *
     * @param  array{weight_grams: int, length_cm: float, width_cm: float, height_cm: float}  $package
     */
    public function createShipment(Order $order, array $package): Shipment
    {
        if ($blockers = $this->blockers($order)) {
            throw new FulfilmentNotAllowed(implode(' ', $blockers));
        }

        $order->loadMissing('items');
        $isCod = $order->payment_method === 'cod';
        $attributes = [
            'order_id' => $order->id,
            'provider' => Shipment::PROVIDER_NIMBUSPOST,
            'active_order_id' => $order->id, // claims the one-live-shipment slot (UNIQUE)
            'status' => Shipment::CREATING,
            'payment_type' => $isCod ? 'cod' : 'prepaid',
            'cod_amount' => $isCod ? $order->grand_total : 0,
            'package_weight_grams' => $package['weight_grams'],
            'package_length_cm' => $package['length_cm'],
            'package_width_cm' => $package['width_cm'],
            'package_height_cm' => $package['height_cm'],
            'failure_reason' => null,
        ];

        // A retry reuses the latest failed attempt (and its NimbusPost order id, if one was created) instead of
        // adding a new row.
        $retry = Shipment::where('order_id', $order->id)->where('status', Shipment::FAILED)->whereNull('awb_number')->latest('id')->first();

        try {
            $shipment = $retry ? tap($retry)->update($attributes) : Shipment::create($attributes);
        } catch (UniqueConstraintViolationException) {
            throw new FulfilmentNotAllowed('A shipment for this order is already being created or exists.');
        }

        // Step 1 — the NimbusPost order (skipped on a retry whose order already exists there).
        if (blank($shipment->provider_order_id)) {
            try {
                $shipment->update(['provider_order_id' => $this->nimbus->createOrder($this->orderPayload($order, $shipment))]);
            } catch (NimbusPostUnavailable $e) {
                return $this->unconfirmed($shipment, $order, $e);
            } catch (NimbusPostException $e) {
                return $this->failed($shipment, $order, $e);
            }
        }

        // Step 2 — book a courier for it.
        try {
            $booking = $this->nimbus->book($shipment->provider_order_id);
        } catch (NimbusPostUnavailable $e) {
            return $this->unconfirmed($shipment, $order, $e);
        } catch (NimbusPostException $e) {
            return $this->failed($shipment, $order, $e); // e.g. "No serviceable courier" — retry re-books this order
        }

        $shipment->update([
            'status' => Shipment::BOOKED,
            'provider_status' => mb_substr((string) ($booking['order_status'] ?? 'booked'), 0, 40),
            'awb_number' => mb_substr((string) $booking['awb'], 0, 50),
            'courier_id' => isset($booking['courier_id']) ? mb_substr((string) $booking['courier_id'], 0, 20) : null,
            'courier_name' => isset($booking['courier_name']) ? mb_substr((string) $booking['courier_name'], 0, 100) : null,
            'pickup_id' => filled($booking['pickup_id'] ?? null) ? mb_substr((string) $booking['pickup_id'], 0, 50) : null,
            'label_url' => $this->safeUrl($booking['label_url'] ?? null),
            'tracking_url' => $this->safeUrl($booking['tracking_url'] ?? null),
            'estimated_delivery_at' => $this->date($booking['edd'] ?? null),
            'failure_reason' => null,
            'booked_at' => now(),
        ]);

        Log::info('shipment.booked', ['order_id' => $order->id, 'shipment_id' => $shipment->id]);

        if (config('nimbuspost.auto_pickup')) {
            $this->schedulePickup($shipment);
        }

        return $shipment->fresh();
    }

    /**
     * After a timeout, an admin checked the NimbusPost panel and confirmed nothing was booked: release the slot so a
     * new attempt is allowed. (If it WAS booked there, they must not use this — cancel it in NimbusPost first.)
     */
    public function releaseUnconfirmed(Shipment $shipment): void
    {
        if ($shipment->status !== Shipment::CREATION_UNCONFIRMED) {
            throw new FulfilmentNotAllowed('Only an unconfirmed creation can be released.');
        }

        $shipment->update(['status' => Shipment::FAILED, 'active_order_id' => null, 'failure_reason' => 'Released by admin after NimbusPost did not confirm the booking.']);
    }

    /**
     * Pulls the latest tracking event (v2 returns the latest event only). Internal status changes only for the
     * order statuses v2 documents (Shipment::NIMBUSPOST_ORDER_STATUSES) or a documented pickup time; everything else
     * is stored raw and shown as NimbusPost's own text. Delivery is NOT inferred — v2 documents no delivered code.
     */
    public function refreshTracking(Shipment $shipment): Shipment
    {
        if (! $shipment->isTrackable()) {
            throw new FulfilmentNotAllowed('This shipment cannot be tracked.');
        }

        $data = $this->nimbus->track($shipment->awb_number);
        $latest = is_array($data['latest'] ?? null) ? $data['latest'] : null;
        $history = $shipment->tracking_history ?? [];

        if ($latest) {
            $event = [
                'status_code' => mb_substr((string) ($latest['statusCode'] ?? ''), 0, 20),
                'status' => mb_substr((string) ($latest['shipStatus'] ?? ''), 0, 60),
                'location' => mb_substr((string) ($latest['location'] ?? ''), 0, 120),
                'event_time' => mb_substr((string) ($latest['eventTime'] ?? ''), 0, 30),
                'message' => mb_substr((string) ($latest['message'] ?? ''), 0, 200),
            ];
            $last = end($history) ?: null;
            if ($last === false || $last === null || ($last['event_time'] ?? null) !== $event['event_time'] || ($last['status_code'] ?? null) !== $event['status_code']) {
                $history[] = $event;
                $history = array_slice($history, -50);
            }
        }

        $orderStatus = strtolower(trim((string) ($data['orderStatus'] ?? '')));
        $updates = [
            'tracking_history' => $history,
            'provider_status' => mb_substr($latest['statusCode'] ?? ($orderStatus !== '' ? $orderStatus : (string) $shipment->provider_status), 0, 40),
            'estimated_delivery_at' => $this->date($data['shipment']['edd'] ?? null) ?? $shipment->estimated_delivery_at,
            'last_synced_at' => now(),
        ];

        if (isset(Shipment::NIMBUSPOST_ORDER_STATUSES[$orderStatus])) {
            $updates['status'] = Shipment::NIMBUSPOST_ORDER_STATUSES[$orderStatus];
            if ($updates['status'] === Shipment::CANCELLED) {
                $updates['active_order_id'] = null;
                $updates['cancelled_at'] = $shipment->cancelled_at ?? now();
            }
        } elseif ($orderStatus !== '') {
            Log::notice('shipment.unmapped_status', ['shipment_id' => $shipment->id, 'status' => $orderStatus]);
        }

        // A documented pickup time means the courier has the parcel: it is no longer cancellable from here.
        if (filled($data['shipment']['pickedAt'] ?? null) && in_array($updates['status'] ?? $shipment->status, [Shipment::BOOKED, Shipment::PENDING_PICKUP], true)) {
            $updates['status'] = Shipment::IN_TRANSIT;
        }

        $shipment->update($updates);

        return $shipment->fresh();
    }

    public function cancel(Shipment $shipment): Shipment
    {
        if (! $shipment->isCancellable()) {
            throw new FulfilmentNotAllowed('This shipment can no longer be cancelled.');
        }

        $this->nimbus->cancel($shipment->awb_number, 'Cancelled by seller'); // throws on refusal — nothing changes then

        $shipment->update(['status' => Shipment::CANCELLED, 'provider_status' => 'cancelled', 'active_order_id' => null, 'cancelled_at' => now()]);

        return $shipment->fresh();
    }

    /**
     * The create-order body, field-for-field from NimbusPost v2's documented `POST /v2/orders` contract.
     *
     * @return array<string, mixed>
     */
    public function orderPayload(Order $order, Shipment $shipment): array
    {
        $payload = [
            'order_number' => (string) $order->order_number,
            'order_type' => 'b2c',                                    // documented value for a normal forward order
            'payment_mode' => $shipment->payment_type,                // cod | prepaid
            'warehouse_id' => (string) config('nimbuspost.warehouse_id'),
            'shipping_address' => array_filter([
                'name' => (string) $order->shipping_name,
                'address' => (string) $order->shipping_address_line_1,
                'address_opt' => filled($order->shipping_address_line_2) ? (string) $order->shipping_address_line_2 : null,
                'pincode' => (int) $order->shipping_pincode,           // number, per the docs
                'city' => (string) $order->shipping_city,
                'state' => (string) $order->shipping_state,
                'country' => filled($order->shipping_country) ? (string) $order->shipping_country : null,
                'phone' => (int) $this->tenDigitPhone($order->shipping_phone), // number, per the docs
            ], fn ($value) => $value !== null && $value !== ''),
            'items' => $order->items->map(fn ($item) => [
                'name' => (string) $item->product_name,
                'qty' => (int) $item->quantity,
                'price' => round((float) $item->unit_price, 2),       // unit price in rupees
                'sku' => (string) $item->variant_sku,
            ])->values()->all(),
            'package' => [
                'weight' => round($shipment->package_weight_grams / 1000, 3), // KILOGRAMS on this endpoint
                'length' => (float) $shipment->package_length_cm,
                'width' => (float) $shipment->package_width_cm,
                'height' => (float) $shipment->package_height_cm,
            ],
        ];

        if ($shipment->payment_type === 'cod') {
            $payload['order_collectable_amount'] = round((float) $order->grand_total, 2); // required for COD
        }

        return $payload;
    }

    /**
     * Checkout serviceability: true (deliverable), false (NimbusPost answered "no courier" for this pincode), or null
     * (not checked: NimbusPost disabled/unconfigured, or unavailable). Null must never block an order.
     */
    public function checkoutServiceability(string $pincode, string $paymentType, float $orderAmount, int $weightGrams): ?bool
    {
        if (! $this->nimbus->isConfigured() || ! preg_match('/^\d{6}$/', $pincode)) {
            return null;
        }

        $key = 'nimbuspost.v2.serviceable.'.$pincode.'.'.$paymentType;

        if (($cached = cache()->get($key)) !== null) {
            return (bool) $cached;
        }

        try {
            $serviceable = $this->nimbus->serviceableCouriers($pincode, $paymentType, $orderAmount, $weightGrams, config('nimbuspost.serviceability_package_cm')) !== [];
        } catch (NimbusPostException $e) {
            Log::notice('shipment.serviceability_unknown', ['error' => class_basename($e)]);

            return null;
        }

        cache()->put($key, $serviceable, now()->addMinutes(max(1, (int) config('nimbuspost.serviceability_cache_minutes'))));

        return $serviceable;
    }

    private function schedulePickup(Shipment $shipment): void
    {
        try {
            $pickup = $this->nimbus->requestPickup($shipment->provider_order_id);
            $shipment->update([
                'pickup_requested' => (bool) ($pickup['courier_scheduled'] ?? false),
                'pickup_id' => filled($pickup['pickup_id'] ?? null) ? mb_substr((string) $pickup['pickup_id'], 0, 50) : $shipment->pickup_id,
            ]);
        } catch (NimbusPostException $e) {
            // The booking stands; pickup can be raised from the NimbusPost dashboard.
            Log::warning('shipment.pickup_request_failed', ['shipment_id' => $shipment->id, 'error' => class_basename($e)]);
        }
    }

    private function unconfirmed(Shipment $shipment, Order $order, NimbusPostException $e): Shipment
    {
        // Unknown outcome: NimbusPost may have acted. Keep the slot claimed so nobody books a second time.
        $shipment->update(['status' => Shipment::CREATION_UNCONFIRMED, 'failure_reason' => $e->getMessage()]);
        Log::warning('shipment.creation_unconfirmed', ['order_id' => $order->id, 'shipment_id' => $shipment->id]);

        return $shipment->fresh();
    }

    private function failed(Shipment $shipment, Order $order, NimbusPostException $e): Shipment
    {
        // NimbusPost refused (or answered malformed): nothing was booked. Release the slot so the admin can retry; a
        // NimbusPost order id, if one was created, stays on the row and is re-used by that retry.
        $shipment->update(['status' => Shipment::FAILED, 'active_order_id' => null, 'failure_reason' => mb_substr($e->getMessage(), 0, 500)]);
        Log::warning('shipment.creation_failed', ['order_id' => $order->id, 'shipment_id' => $shipment->id, 'error' => class_basename($e)]);

        return $shipment->fresh();
    }

    private function tenDigitPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^\d{10}$/', $digits) ? $digits : null;
    }

    private function safeUrl(mixed $url): ?string
    {
        $url = trim((string) $url);

        return $url !== '' && preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function date(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
