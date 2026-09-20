<?php

namespace App\Services\Storefront;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

/**
 * Issues the "₹150 voucher for your next shopping" earned by an order at or
 * above the second offer threshold, once that order is delivered.
 *
 * Reuses the existing Coupon table/redemption path (CheckoutService applies
 * it like any coupon) — a voucher is just a fixed-value, single-use coupon
 * bound to the customer (coupons.user_id) and to the order that earned it
 * (coupons.source_order_id, UNIQUE).
 *
 * Idempotency is enforced by that UNIQUE constraint, not just an "exists"
 * check: a repeated delivered event (admin double-click, a future courier
 * webhook redelivery, a re-saved order) either finds the existing voucher or
 * loses the insert race and is treated as already issued — it can never mint
 * a second voucher for the same order.
 *
 * "Cannot be used on the order that generated it" holds by construction: the
 * voucher does not exist until that order is delivered, long after its
 * coupon/discount was fixed; CheckoutService additionally refuses a voucher
 * whose source order is the one being paid for.
 */
class VoucherService
{
    public function __construct(private readonly StorefrontSettings $settings) {}

    /** Merchandise value after discount, before shipping (tax is included in prices). */
    public function qualifyingAmount(Order $order): float
    {
        return round((float) $order->subtotal - (float) $order->discount_amount, 2);
    }

    public function qualifies(Order $order): bool
    {
        return $order->order_status === 'delivered'
            && $order->user_id !== null
            && $this->qualifyingAmount($order) >= $this->settings->giftVoucherThreshold();
    }

    /** Returns the voucher for this order (existing or newly issued), or null if it doesn't qualify. */
    public function issueForDeliveredOrder(Order $order): ?Coupon
    {
        if (! $this->qualifies($order)) {
            return null;
        }

        $existing = Coupon::where('source_order_id', $order->id)->first();

        if ($existing) {
            return $existing;
        }

        try {
            return Coupon::create([
                'code' => $this->generateCode(),
                'type' => 'fixed',
                'value' => $this->settings->voucherValue(),
                'minimum_order_amount' => $this->settings->voucherMinimumOrder(),
                'maximum_discount_amount' => null,
                'usage_limit' => 1,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addDays($this->settings->voucherValidityDays()),
                'status' => 'active',
                'user_id' => $order->user_id,
                'source_order_id' => $order->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Lost a race with a concurrent delivered event — the voucher exists.
            return Coupon::where('source_order_id', $order->id)->first();
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'VCH-'.strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
