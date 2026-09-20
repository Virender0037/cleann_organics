<?php

namespace App\Services\Storefront;

use App\Models\OrderItem;
use App\Models\ProductReview;

/**
 * Who may review a product: only a customer with a DELIVERED order that
 * contains it (cancelled/undelivered/failed orders never qualify), and only
 * once per product (also enforced by the unique(user_id, product_id) DB
 * constraint on product_reviews). Reviews are matched by the order item's
 * product_id, so it still works after a variant is renamed or removed.
 */
class ReviewEligibility
{
    public function hasPurchased(int $userId, int $productId): bool
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId)->where('order_status', 'delivered'))
            ->exists();
    }

    public function hasReviewed(int $userId, int $productId): bool
    {
        // withTrashed: a soft-deleted review still occupies the unique
        // (user_id, product_id) slot in the database.
        return ProductReview::withTrashed()->where('user_id', $userId)->where('product_id', $productId)->exists();
    }

    public function canReview(int $userId, int $productId): bool
    {
        return $this->hasPurchased($userId, $productId) && ! $this->hasReviewed($userId, $productId);
    }

    /**
     * Of the given product ids, which this customer may review right now.
     *
     * @param  iterable<int>  $productIds
     * @return array<int, int>
     */
    public function reviewableAmong(int $userId, iterable $productIds): array
    {
        $ids = collect($productIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $purchased = OrderItem::query()
            ->whereIn('product_id', $ids)
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId)->where('order_status', 'delivered'))
            ->pluck('product_id')
            ->unique();

        $reviewed = ProductReview::withTrashed()->where('user_id', $userId)->whereIn('product_id', $ids)->pluck('product_id');

        return $purchased->diff($reviewed)->values()->all();
    }
}
