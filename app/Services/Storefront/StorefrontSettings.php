<?php

namespace App\Services\Storefront;

use App\Models\Setting;

/**
 * Typed reader for the admin-editable "storefront" settings group
 * (Admin → Settings → Storefront & Offers): shipping fallback, free-shipping
 * and gift thresholds, voucher rules, and the Instagram profile link.
 *
 * Every business number the customer-facing offers depend on is read from
 * here so the cart bar, checkout, shipping calculation and voucher issuing
 * can never disagree with each other.
 */
class StorefrontSettings
{
    public const DEFAULT_FREE_SHIPPING_THRESHOLD = 399.0;

    public const DEFAULT_GIFT_VOUCHER_THRESHOLD = 999.0;

    public const DEFAULT_VOUCHER_VALUE = 150.0;

    /** Used only when the admin has not set a validity — see voucherValidityDays(). */
    public const DEFAULT_VOUCHER_VALIDITY_DAYS = 365;

    /** @return array<string, string|null> */
    public function all(): array
    {
        return Setting::cached('storefront');
    }

    private function number(string $key, ?float $default): ?float
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : (float) $value;
    }

    /** Orders at/above this (after discount, before shipping) ship free. */
    public function freeShippingThreshold(): float
    {
        return $this->number('free_shipping_threshold', self::DEFAULT_FREE_SHIPPING_THRESHOLD);
    }

    /**
     * Flat charge applied below the free-shipping threshold when no shipping
     * zone/rate matches. Deliberately null until an admin configures it —
     * no amount is invented.
     */
    public function flatShippingCharge(): ?float
    {
        return $this->number('flat_shipping_charge', null);
    }

    /** Second offer tier: free shipping + gift + voucher. */
    public function giftVoucherThreshold(): float
    {
        return $this->number('gift_voucher_threshold', self::DEFAULT_GIFT_VOUCHER_THRESHOLD);
    }

    public function voucherValue(): float
    {
        return $this->number('voucher_value', self::DEFAULT_VOUCHER_VALUE);
    }

    public function voucherMinimumOrder(): float
    {
        return $this->number('voucher_min_order', 0.0);
    }

    public function voucherValidityDays(): int
    {
        $days = $this->number('voucher_validity_days', null);

        return $days !== null && $days > 0 ? (int) $days : self::DEFAULT_VOUCHER_VALIDITY_DAYS;
    }

    public function instagramUrl(): ?string
    {
        $url = trim((string) ($this->all()['instagram_url'] ?? ''));

        return $url !== '' ? $url : null;
    }

    /**
     * The two customer-facing offers, lowest first, each tagged with
     * whether an eligible amount has unlocked it and how much is left.
     *
     * @return array<int, array{threshold: float, title: string, perks: array<int, string>, unlocked: bool, remaining: float}>
     */
    public function offers(float $eligibleAmount): array
    {
        $tiers = [
            [
                'threshold' => $this->freeShippingThreshold(),
                'perks' => ['Free Shipping', 'Surprise Sustainable Gift'],
            ],
            [
                'threshold' => $this->giftVoucherThreshold(),
                'perks' => ['Free Shipping', 'Surprise Gift', '₹'.$this->formatMoney($this->voucherValue()).' voucher for your next shopping'],
            ],
        ];

        return array_map(fn (array $tier) => $tier + [
            'title' => 'Order above ₹'.$this->formatMoney($tier['threshold']),
            'unlocked' => $eligibleAmount >= $tier['threshold'],
            'remaining' => round(max(0, $tier['threshold'] - $eligibleAmount), 2),
        ], $tiers);
    }

    public function formatMoney(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }
}
