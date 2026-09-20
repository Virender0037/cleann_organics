<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'status',
        'user_id',
        'source_order_id',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /** The only customer allowed to redeem it, when it's an earned voucher. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The delivered order that earned this voucher (earned vouchers only). */
    public function sourceOrder()
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
