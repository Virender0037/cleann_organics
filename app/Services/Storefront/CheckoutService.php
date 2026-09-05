<?php

namespace App\Services\Storefront;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Checkout is authenticated-only (every route is gated by `auth`, same as
 * Wishlist) — an Address belongs to a customer, so there is no meaningful
 * guest checkout to support without inventing account-less order ownership.
 *
 * Reuses CartService entirely for lines/subtotal/stock-availability rather
 * than re-deriving any of it — pricing, stock clamping, and the
 * available/unavailable flag on each line all come from Phase G unchanged.
 * Only order-domain math (tax, shipping, coupon, grand total) lives here.
 */
class CheckoutService
{
    private const COUPON_SESSION_KEY = 'checkout_coupon_code';

    public function __construct(
        private readonly CartService $cart,
        private readonly Request $request,
    ) {
    }

    /** @return Collection<int, array<string, mixed>> */
    public function lines(): Collection
    {
        return $this->cart->lines();
    }

    public function hasUnavailableLines(): bool
    {
        return $this->lines()->contains(fn (array $line) => ! $line['available']);
    }

    public function subtotal(): float
    {
        return $this->cart->subtotal();
    }

    /**
     * Total weight of everything in the cart, in whatever unit
     * product_variants.weight is recorded in (kg, per the admin form).
     * Lines with no weight recorded count as 0 — never blocks checkout for
     * a data-entry gap, just doesn't add to the total.
     */
    public function totalWeight(): float
    {
        return (float) $this->lines()
            ->where('available', true)
            ->sum(fn (array $line) => (float) ($line['variant']->weight ?? 0) * $line['quantity']);
    }

    /**
     * Tax is per-product (products.tax_rate_id), not a single storefront
     * rate — summed per line at that product's percentage. A product with
     * no tax rate, or whose tax rate has been deactivated, contributes 0
     * rather than falling back to some default rate.
     */
    public function taxAmount(): float
    {
        $lines = $this->lines()->where('available', true);

        $taxRateIds = $lines->pluck('product')->filter()->pluck('tax_rate_id')->filter()->unique();

        if ($taxRateIds->isEmpty()) {
            return 0.0;
        }

        $rates = TaxRate::query()->whereIn('id', $taxRateIds)->where('status', 'active')->get()->keyBy('id');

        return round($lines->sum(function (array $line) use ($rates) {
            $rate = $rates->get($line['product']?->tax_rate_id);

            return $rate ? $line['subtotal'] * ((float) $rate->percentage / 100) : 0.0;
        }), 2);
    }

    /**
     * Resolves the most specific active ShippingZone for an address —
     * exact pincode match first, then a city-only zone (no pincode set),
     * then a state-only zone (no city/pincode set), then a catch-all zone
     * with none of the three set. This precedence isn't specified anywhere
     * else in the app (ShippingZone has no documented matching rule); it's
     * the most defensible reading of "more specific wins" for a free-text
     * geography field, and is applied consistently in one place only.
     */
    private function resolveShippingZone(Address $address): ?ShippingZone
    {
        return ShippingZone::query()->where('status', 'active')->whereNotNull('pincode')->where('pincode', $address->pincode)->first()
            ?? ShippingZone::query()->where('status', 'active')->whereNull('pincode')->whereNotNull('city')->where('city', $address->city)->first()
            ?? ShippingZone::query()->where('status', 'active')->whereNull('pincode')->whereNull('city')->whereNotNull('state')->where('state', $address->state)->first()
            ?? ShippingZone::query()->where('status', 'active')->whereNull('pincode')->whereNull('city')->whereNull('state')->first();
    }

    private function resolveShippingRate(Address $address): ?ShippingRate
    {
        $zone = $this->resolveShippingZone($address);

        if (! $zone) {
            return null;
        }

        $weight = $this->totalWeight();

        return ShippingRate::query()
            ->where('shipping_zone_id', $zone->id)
            ->where('status', 'active')
            ->where('min_weight', '<=', $weight)
            ->where(fn ($q) => $q->whereNull('max_weight')->orWhere('max_weight', '>=', $weight))
            ->orderBy('min_weight')
            ->first();
    }

    /**
     * The name of the ShippingZone the rate was resolved against — frozen
     * onto the order at placement so it survives the admin later renaming
     * or deleting the zone. Null when nothing matched (free-shipping
     * fallback).
     */
    public function shippingZoneName(Address $address): ?string
    {
        return $this->resolveShippingZone($address)?->name;
    }

    /**
     * 0 both when no zone/rate is configured for the address (rather than
     * blocking checkout entirely over an admin data gap) and when the
     * matched rate's free_shipping_above threshold is met by the subtotal.
     */
    public function shippingAmount(Address $address): float
    {
        $rate = $this->resolveShippingRate($address);

        if (! $rate) {
            return 0.0;
        }

        if ($rate->free_shipping_above !== null && $this->subtotal() >= (float) $rate->free_shipping_above) {
            return 0.0;
        }

        return (float) $rate->shipping_charge;
    }

    public function appliedCouponCode(): ?string
    {
        return $this->request->session()->get(self::COUPON_SESSION_KEY);
    }

    /**
     * Re-resolves and re-validates the coupon on every call rather than
     * trusting the session to still hold a valid one — a coupon can expire,
     * hit its usage limit, or drop below the order's minimum between
     * "apply" and the page rendering again.
     */
    public function appliedCoupon(): ?Coupon
    {
        $code = $this->appliedCouponCode();

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $this->couponIsValidFor($coupon, $this->subtotal())) {
            $this->removeCoupon();

            return null;
        }

        return $coupon;
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return ['success' => false, 'message' => 'That coupon code was not found.'];
        }

        if (! $this->couponIsValidFor($coupon, $this->subtotal())) {
            return ['success' => false, 'message' => $this->couponInvalidReason($coupon, $this->subtotal())];
        }

        $this->request->session()->put(self::COUPON_SESSION_KEY, $coupon->code);

        return ['success' => true, 'message' => 'Coupon applied.'];
    }

    public function removeCoupon(): void
    {
        $this->request->session()->forget(self::COUPON_SESSION_KEY);
    }

    private function couponIsValidFor(Coupon $coupon, float $subtotal): bool
    {
        return $coupon->status === 'active'
            && now()->between($coupon->start_date, $coupon->end_date)
            && ($coupon->usage_limit === null || $coupon->used_count < $coupon->usage_limit)
            && $subtotal >= (float) $coupon->minimum_order_amount;
    }

    private function couponInvalidReason(Coupon $coupon, float $subtotal): string
    {
        if ($coupon->status !== 'active') {
            return 'This coupon is no longer active.';
        }

        if (! now()->between($coupon->start_date, $coupon->end_date)) {
            return 'This coupon is not currently valid.';
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return 'This coupon has reached its usage limit.';
        }

        return 'This order does not meet the minimum amount of ₹'.number_format((float) $coupon->minimum_order_amount, 2).' for this coupon.';
    }

    public function discountAmount(): float
    {
        $coupon = $this->appliedCoupon();

        if (! $coupon) {
            return 0.0;
        }

        $subtotal = $this->subtotal();
        $discount = $coupon->type === 'percentage'
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->maximum_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function grandTotal(Address $address): float
    {
        return round($this->subtotal() - $this->discountAmount() + $this->shippingAmount($address) + $this->taxAmount(), 2);
    }

    /**
     * Places the order inside a single transaction: re-validates every
     * line and the coupon fresh (never trusting anything computed earlier
     * in the request), decrements stock, snapshots order items from the
     * current variant/product data (never a live reference), records a
     * pending Payment, increments the coupon's used_count, and clears the
     * cart only after every write succeeds.
     *
     * @return array{success: bool, message: string, order?: Order}
     */
    public function placeOrder(Address $address, string $paymentMethod): array
    {
        if (! in_array($paymentMethod, ['cod', 'upi', 'bank_transfer'], true)) {
            return ['success' => false, 'message' => 'Please choose a valid payment method.'];
        }

        if ($address->user_id !== Auth::id()) {
            return ['success' => false, 'message' => 'That address could not be found.'];
        }

        $lines = $this->lines();

        if ($lines->isEmpty()) {
            return ['success' => false, 'message' => 'Your cart is empty.'];
        }

        if ($lines->contains(fn (array $line) => ! $line['available'])) {
            return ['success' => false, 'message' => 'Some items in your cart are no longer available. Please review your cart before checking out.'];
        }

        // Stock is re-checked here, inside the transaction, immediately
        // before decrementing — CartService's own clamping already keeps
        // cart quantities sane, but this is the last, authoritative check
        // before money and inventory actually move.
        foreach ($lines as $line) {
            $variant = ProductVariant::find($line['variant']->id);

            if (! $variant || $variant->stock_quantity < $line['quantity']) {
                return ['success' => false, 'message' => 'Sorry, "'.($line['product']->name ?? 'an item').'" no longer has enough stock. Please update your cart.'];
            }
        }

        $subtotal = $this->subtotal();
        $coupon = $this->appliedCoupon();
        $discount = $this->discountAmount();
        $shipping = $this->shippingAmount($address);
        $shippingZoneName = $this->shippingZoneName($address);
        $tax = $this->taxAmount();
        $grandTotal = round($subtotal - $discount + $shipping + $tax, 2);

        try {
            $order = $this->createOrderInTransaction($lines, $address, $paymentMethod, $subtotal, $coupon, $discount, $shipping, $shippingZoneName, $tax, $grandTotal);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'insufficient_stock') {
                throw $e;
            }

            return ['success' => false, 'message' => 'Sorry, one of the items in your cart just sold out. Please update your cart and try again.'];
        }

        $this->cart->clear();
        $this->removeCoupon();

        return ['success' => true, 'message' => 'Order placed successfully.', 'order' => $order];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $lines
     */
    private function createOrderInTransaction(Collection $lines, Address $address, string $paymentMethod, float $subtotal, ?Coupon $coupon, float $discount, float $shipping, ?string $shippingZoneName, float $tax, float $grandTotal): Order
    {
        return DB::transaction(function () use ($lines, $address, $paymentMethod, $subtotal, $coupon, $discount, $shipping, $shippingZoneName, $tax, $grandTotal) {
            $order = Order::create([
                'user_id' => Auth::id(),
                // Kept as an admin back-reference only — never read for
                // historical delivery details (see the snapshot below).
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'order_number' => $this->generateOrderNumber(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'order_status' => 'pending',

                // Immutable delivery snapshot — frozen here, never re-read
                // from the addresses table afterwards.
                'shipping_name' => $address->name,
                'shipping_phone' => $address->phone,
                'shipping_address_line_1' => $address->address_line_1,
                'shipping_address_line_2' => $address->address_line_2,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_country' => $address->country,
                'shipping_pincode' => $address->pincode,
                // Phase I collects one address; billing mirrors shipping.
                // billing_* stay null until a later phase collects a
                // distinct billing address and flips this flag.
                'billing_same_as_shipping' => true,
                'shipping_zone_name' => $shippingZoneName,
            ]);

            foreach ($lines as $line) {
                /** @var ProductVariant $variant */
                $variant = ProductVariant::lockForUpdate()->find($line['variant']->id);

                if (! $variant || $variant->stock_quantity < $line['quantity']) {
                    throw new \RuntimeException('insufficient_stock');
                }

                $variant->decrement('stock_quantity', $line['quantity']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']?->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $line['product']?->name ?? 'Product',
                    'variant_sku' => $variant->sku,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'variant_pack_quantity' => $variant->pack_quantity,
                    'unit' => $variant->unit,
                    'weight' => $variant->weight,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_price' => $line['subtotal'],
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'amount' => $grandTotal,
                'status' => 'pending',
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            return $order;
        });
    }

    /**
     * ORD-YYYYMMDD-XXXXXX, retried on the astronomically unlikely chance of
     * a collision rather than trusting randomness alone against the unique
     * constraint.
     */
    private function generateOrderNumber(): string
    {
        do {
            $candidate = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::where('order_number', $candidate)->exists());

        return $candidate;
    }
}
