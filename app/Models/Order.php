<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'coupon_id',

        'order_number',

        'subtotal',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',

        'payment_method',
        'payment_status',

        'order_status',

        'notes',

        // Immutable delivery snapshot — frozen at placement, never a live
        // reference to the addresses table (see the add_delivery_snapshot
        // migration). address_id stays only as an admin back-reference.
        'shipping_name',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_pincode',

        'billing_same_as_shipping',
        'billing_name',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_pincode',

        'shipping_zone_name',

        'confirmed_at',
        'packed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',

        'billing_same_as_shipping' => 'boolean',

        'confirmed_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnRequest::class, 'order_id');
    }

    /**
     * The frozen shipping address for this order, as a plain array — always
     * read this (never $order->address) for anything a customer or admin
     * sees about where the order went.
     *
     * @return array<string, string|null>
     */
    public function shippingSnapshot(): array
    {
        return [
            'name' => $this->shipping_name,
            'phone' => $this->shipping_phone,
            'address_line_1' => $this->shipping_address_line_1,
            'address_line_2' => $this->shipping_address_line_2,
            'city' => $this->shipping_city,
            'state' => $this->shipping_state,
            'country' => $this->shipping_country,
            'pincode' => $this->shipping_pincode,
        ];
    }

    /**
     * The frozen billing address. When billing_same_as_shipping (the only
     * case Phase I produces), this is the shipping snapshot; a later phase
     * that collects a distinct billing address fills billing_* and flips the
     * flag, and this starts returning those instead — no caller changes.
     *
     * @return array<string, string|null>
     */
    public function billingSnapshot(): array
    {
        if ($this->billing_same_as_shipping) {
            return $this->shippingSnapshot();
        }

        return [
            'name' => $this->billing_name,
            'phone' => $this->billing_phone,
            'address_line_1' => $this->billing_address_line_1,
            'address_line_2' => $this->billing_address_line_2,
            'city' => $this->billing_city,
            'state' => $this->billing_state,
            'country' => $this->billing_country,
            'pincode' => $this->billing_pincode,
        ];
    }
}