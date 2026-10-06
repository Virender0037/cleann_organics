<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One courier booking for an order (provider: NimbusPost). Shipment status is its own track — it never writes
 * orders.payment_status, and orders.order_status is only advanced by explicit, guarded code (FulfilmentService).
 */
class Shipment extends Model
{
    public const PROVIDER_NIMBUSPOST = 'nimbuspost';

    // Internal statuses.
    public const CREATING = 'creating';                      // request in flight (holds the duplicate guard)

    public const CREATION_UNCONFIRMED = 'creation_unconfirmed'; // NimbusPost timed out: booking may exist — check the panel

    public const FAILED = 'failed';                          // NimbusPost refused; safe to retry

    public const BOOKED = 'booked';

    public const PENDING_PICKUP = 'pending_pickup';

    public const IN_TRANSIT = 'in_transit';

    public const EXCEPTION = 'exception';                    // NDR / delivery exception

    public const OUT_FOR_DELIVERY = 'out_for_delivery';

    public const DELIVERED = 'delivered';

    public const RTO = 'rto';

    public const RTO_IN_TRANSIT = 'rto_in_transit';

    public const RTO_DELIVERED = 'rto_delivered';

    public const CANCELLED = 'cancelled';

    /** NimbusPost tracking status codes (documented on "Track Single Shipment") → internal status. */
    public const NIMBUSPOST_TRACKING_CODES = [
        'PP' => self::PENDING_PICKUP,
        'IT' => self::IN_TRANSIT,
        'EX' => self::EXCEPTION,
        'OFD' => self::OUT_FOR_DELIVERY,
        'DL' => self::DELIVERED,
        'RT' => self::RTO,
        'RT-IT' => self::RTO_IN_TRANSIT,
        'RT-DL' => self::RTO_DELIVERED,
    ];

    /** Statuses that no longer hold the one-live-shipment-per-order guard. */
    public const RELEASED = [self::FAILED, self::CANCELLED];

    /** Nothing further will happen to these. */
    public const TERMINAL = [self::FAILED, self::CANCELLED, self::DELIVERED, self::RTO_DELIVERED];

    /** Cancellation is offered only before the courier has the parcel. */
    public const CANCELLABLE = [self::BOOKED, self::PENDING_PICKUP];

    /** Tracking can be refreshed for these (they have an AWB and may still change). */
    public const TRACKABLE = [self::BOOKED, self::PENDING_PICKUP, self::IN_TRANSIT, self::EXCEPTION, self::OUT_FOR_DELIVERY, self::RTO, self::RTO_IN_TRANSIT];

    private const CUSTOMER_LABELS = [
        self::BOOKED => 'Shipment booked',
        self::PENDING_PICKUP => 'Awaiting courier pickup',
        self::IN_TRANSIT => 'In transit',
        self::EXCEPTION => 'Delivery attempt issue',
        self::OUT_FOR_DELIVERY => 'Out for delivery',
        self::DELIVERED => 'Delivered',
        self::RTO => 'Returning to seller',
        self::RTO_IN_TRANSIT => 'Returning to seller',
        self::RTO_DELIVERED => 'Returned to seller',
        self::CANCELLED => 'Shipment cancelled',
    ];

    protected $fillable = [
        'order_id', 'provider', 'active_order_id', 'status', 'provider_status', 'payment_type', 'cod_amount',
        'provider_order_id', 'provider_shipment_id', 'awb_number', 'courier_id', 'courier_name', 'label_url',
        'manifest_url', 'pickup_requested', 'package_weight_grams', 'package_length_cm', 'package_width_cm',
        'package_height_cm', 'tracking_history', 'rto_awb', 'ndr_reason', 'failure_reason', 'booked_at',
        'delivered_at', 'cancelled_at', 'last_synced_at',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'pickup_requested' => 'boolean',
        'package_weight_grams' => 'integer',
        'package_length_cm' => 'decimal:2',
        'package_width_cm' => 'decimal:2',
        'package_height_cm' => 'decimal:2',
        'tracking_history' => 'array',
        'booked_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /** Booked with the courier (has an AWB) — what a customer may see. */
    public function isVisibleToCustomer(): bool
    {
        return $this->awb_number !== null && array_key_exists($this->status, self::CUSTOMER_LABELS);
    }

    public function customerStatusLabel(): string
    {
        return self::CUSTOMER_LABELS[$this->status] ?? 'Preparing for dispatch';
    }

    public function adminStatusLabel(): string
    {
        return match ($this->status) {
            self::CREATING => 'Creating…',
            self::CREATION_UNCONFIRMED => 'Unconfirmed — check NimbusPost',
            self::FAILED => 'Failed',
            default => self::CUSTOMER_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE, true) && $this->awb_number !== null;
    }

    public function isTrackable(): bool
    {
        return in_array($this->status, self::TRACKABLE, true) && $this->awb_number !== null;
    }
}
