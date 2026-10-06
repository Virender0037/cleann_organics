<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\Exceptions\NimbusPostException;
use App\Services\Shipping\Exceptions\NimbusPostMalformedResponse;
use App\Services\Shipping\Exceptions\NimbusPostRejected;
use App\Services\Shipping\Exceptions\NimbusPostUnavailable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Business rules around courier bookings. Controllers call this; only NimbusPostService talks to the API.
 *
 * - Payment gate: Order::fulfilmentBlockedReason() (COD once confirmed; everything else once paid/verified).
 * - One live shipment per order: `shipments.active_order_id` is UNIQUE and is claimed BEFORE the API call, so double
 *   clicks / concurrent requests cannot book twice. A timeout keeps the claim (the booking may exist on NimbusPost's
 *   side) until an admin confirms there is none and releases it.
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

        if (mb_strlen((string) $order->order_number) > 20) {
            $blockers[] = 'The order number is longer than NimbusPost allows (20).';
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
     * @param  array{weight_grams: int, length_cm: ?float, width_cm: ?float, height_cm: ?float}  $package
     */
    public function createShipment(Order $order, array $package): Shipment
    {
        if ($blockers = $this->blockers($order)) {
            throw new FulfilmentNotAllowed(implode(' ', $blockers));
        }

        $order->loadMissing('items', 'user', 'payment');
        $isCod = $order->payment_method === 'cod';

        try {
            $shipment = Shipment::create([
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
                'pickup_requested' => (bool) config('nimbuspost.auto_pickup'),
            ]);
        } catch (UniqueConstraintViolationException) {
            throw new FulfilmentNotAllowed('A shipment for this order is already being created or exists.');
        }

        try {
            $data = $this->nimbus->createShipment($this->shipmentPayload($order, $shipment));
        } catch (NimbusPostUnavailable $e) {
            // Unknown outcome: NimbusPost may have booked it. Keep the slot claimed so nobody books a second AWB.
            $shipment->update(['status' => Shipment::CREATION_UNCONFIRMED, 'failure_reason' => $e->getMessage()]);
            Log::warning('shipment.creation_unconfirmed', ['order_id' => $order->id, 'shipment_id' => $shipment->id]);

            return $shipment;
        } catch (NimbusPostException $e) {
            // Refused / malformed / auth: nothing was booked. Release the slot so the admin can fix data and retry.
            $shipment->update(['status' => Shipment::FAILED, 'active_order_id' => null, 'failure_reason' => mb_substr($e->getMessage(), 0, 500)]);
            Log::warning('shipment.creation_failed', ['order_id' => $order->id, 'shipment_id' => $shipment->id, 'error' => get_class($e)]);

            return $shipment;
        }

        $shipment->update([
            'status' => Shipment::BOOKED,
            'provider_status' => mb_substr((string) ($data['status'] ?? 'booked'), 0, 40),
            'provider_order_id' => (string) ($data['order_id'] ?? ''),
            'provider_shipment_id' => (string) $data['shipment_id'],
            'awb_number' => (string) $data['awb_number'],
            'courier_id' => isset($data['courier_id']) ? (string) $data['courier_id'] : null,
            'courier_name' => isset($data['courier_name']) ? mb_substr((string) $data['courier_name'], 0, 100) : null,
            'label_url' => $this->safeUrl($data['label'] ?? null),
            'manifest_url' => $this->safeUrl($data['manifest'] ?? null),
            'failure_reason' => null,
            'booked_at' => now(),
        ]);

        Log::info('shipment.booked', ['order_id' => $order->id, 'shipment_id' => $shipment->id]);

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

    /** Pulls tracking from NimbusPost and maps it through Shipment::NIMBUSPOST_TRACKING_CODES. */
    public function refreshTracking(Shipment $shipment): Shipment
    {
        if (! $shipment->isTrackable()) {
            throw new FulfilmentNotAllowed('This shipment cannot be tracked.');
        }

        $data = $this->nimbus->track($shipment->awb_number);
        $history = array_values(array_filter((array) ($data['history'] ?? []), 'is_array'));
        $history = array_map(fn (array $event) => [
            'status_code' => mb_substr((string) ($event['status_code'] ?? ''), 0, 10),
            'location' => mb_substr((string) ($event['location'] ?? ''), 0, 120),
            'event_time' => mb_substr((string) ($event['event_time'] ?? ''), 0, 25),
            'message' => mb_substr((string) ($event['message'] ?? ''), 0, 200),
        ], $history);

        $latest = end($history) ?: null;
        $code = $latest['status_code'] ?? null;
        $mapped = $code !== null ? (Shipment::NIMBUSPOST_TRACKING_CODES[strtoupper($code)] ?? null) : null;

        if ($code !== null && $mapped === null) {
            Log::notice('shipment.unknown_tracking_code', ['shipment_id' => $shipment->id, 'code' => $code]);
        }

        $updates = [
            'tracking_history' => $history,
            'provider_status' => mb_substr((string) ($code ?? ($data['status'] ?? $shipment->provider_status)), 0, 40),
            'rto_awb' => filled($data['rto_awb'] ?? null) ? mb_substr((string) $data['rto_awb'], 0, 50) : $shipment->rto_awb,
            'last_synced_at' => now(),
        ];

        if ($mapped !== null) {
            $updates['status'] = $mapped;
            $updates['ndr_reason'] = $mapped === Shipment::EXCEPTION ? ($latest['message'] ?: null) : $shipment->ndr_reason;

            if ($mapped === Shipment::DELIVERED && ! $shipment->delivered_at) {
                $updates['delivered_at'] = now();
            }
        }

        $shipment->update($updates);

        // A delivered parcel marks the order delivered (fires the existing voucher hook); nothing else is touched —
        // never payment_status, and never a downgrade.
        if ($mapped === Shipment::DELIVERED && $shipment->order->order_status !== 'delivered' && $shipment->order->order_status !== 'cancelled') {
            $shipment->order->update(['order_status' => 'delivered', 'delivered_at' => $shipment->order->delivered_at ?? now()]);
        }

        return $shipment->fresh();
    }

    public function cancel(Shipment $shipment): Shipment
    {
        if (! $shipment->isCancellable()) {
            throw new FulfilmentNotAllowed('This shipment can no longer be cancelled.');
        }

        $this->nimbus->cancel($shipment->awb_number); // throws Rejected("Unable to cancel") etc. — nothing changes then

        $shipment->update(['status' => Shipment::CANCELLED, 'active_order_id' => null, 'cancelled_at' => now()]);

        return $shipment->fresh();
    }

    /**
     * The create-shipment body, field-for-field from NimbusPost's documented "Create Shipment" request.
     *
     * @return array<string, mixed>
     */
    public function shipmentPayload(Order $order, Shipment $shipment): array
    {
        $pickup = (array) config('nimbuspost.pickup');

        $payload = [
            'order_number' => (string) $order->order_number,
            'payment_type' => $shipment->payment_type,                 // cod | prepaid
            'order_amount' => round((float) $order->grand_total, 2),   // "Total Order Amount" (collected on delivery for COD)
            'shipping_charges' => round((float) $order->shipping_amount, 2),
            'discount' => round((float) $order->discount_amount, 2),
            'package_weight' => $shipment->package_weight_grams,       // grams
            'request_auto_pickup' => $shipment->pickup_requested ? 'yes' : 'no',
            'consignee' => array_filter([
                'name' => mb_substr((string) $order->shipping_name, 0, 200),
                'address' => mb_substr((string) $order->shipping_address_line_1, 0, 200),
                'address_2' => mb_substr((string) $order->shipping_address_line_2, 0, 200) ?: null,
                'city' => mb_substr((string) $order->shipping_city, 0, 40),
                'state' => mb_substr((string) $order->shipping_state, 0, 40),
                'pincode' => (string) $order->shipping_pincode,
                'phone' => $this->tenDigitPhone($order->shipping_phone),
            ], fn ($value) => $value !== null && $value !== ''),
            'pickup' => array_filter([
                'warehouse_name' => mb_substr((string) $pickup['warehouse_name'], 0, 20),
                'name' => mb_substr((string) $pickup['name'], 0, 200),
                'address' => mb_substr((string) $pickup['address'], 0, 200),
                'address_2' => filled($pickup['address_2'] ?? null) ? mb_substr((string) $pickup['address_2'], 0, 200) : null,
                'city' => mb_substr((string) $pickup['city'], 0, 40),
                'state' => mb_substr((string) $pickup['state'], 0, 40),
                'pincode' => (string) $pickup['pincode'],
                'phone' => $this->tenDigitPhone($pickup['phone']),
                'gst_umber' => filled($pickup['gst_number'] ?? null) ? (string) $pickup['gst_number'] : null, // sic — NimbusPost's documented key
            ], fn ($value) => $value !== null && $value !== ''),
            'order_items' => $order->items->map(fn ($item) => [
                'name' => (string) $item->product_name,
                'qty' => (string) $item->quantity,
                'price' => (string) round((float) $item->unit_price, 2),
                'sku' => (string) ($item->variant_sku ?? ''),
            ])->values()->all(),
        ];

        if ($shipment->package_length_cm && $shipment->package_width_cm && $shipment->package_height_cm) {
            $payload['package_length'] = (float) $shipment->package_length_cm;
            $payload['package_breadth'] = (float) $shipment->package_width_cm;  // NimbusPost calls width "breadth"
            $payload['package_height'] = (float) $shipment->package_height_cm;
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

        $key = 'nimbuspost.serviceable.'.$pincode.'.'.$paymentType;

        if (($cached = cache()->get($key)) !== null) {
            return (bool) $cached;
        }

        try {
            $serviceable = $this->nimbus->serviceableCouriers($pincode, $paymentType, $orderAmount, $weightGrams) !== [];
        } catch (NimbusPostRejected|NimbusPostUnavailable|NimbusPostMalformedResponse|NimbusPostException $e) {
            Log::notice('shipment.serviceability_unknown', ['pincode' => $pincode, 'error' => get_class($e)]);

            return null;
        }

        cache()->put($key, $serviceable, now()->addMinutes(max(1, (int) config('nimbuspost.serviceability_cache_minutes'))));

        return $serviceable;
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
}
