<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * The end date is inclusive: admins pick it with a date-only input (stored as 00:00:00) and customers are told
     * "valid until <date>", so a coupon stays usable through the whole of that day. Comparing against the raw
     * midnight value used to kill a coupon at the start of its last day — and a one-day coupon (start = end) never worked.
     */
    public function expiresAt(): CarbonInterface
    {
        return $this->end_date->copy()->endOfDay();
    }

    public function isExpired(?CarbonInterface $at = null): bool
    {
        return ($at ?? now())->greaterThan($this->expiresAt());
    }

    public function isWithinValidity(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $at->greaterThanOrEqualTo($this->start_date) && ! $this->isExpired($at);
    }

    /** Coupons whose last valid day is already over (same inclusive rule as expiresAt()). */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('end_date', '<', now()->startOfDay());
    }
}
