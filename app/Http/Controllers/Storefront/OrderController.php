<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\Storefront\ReviewEligibility;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Route-model-bound by id, but every lookup is re-scoped to
     * Auth::id() — a customer can never view another customer's order by
     * changing the id in the URL; an id that exists but isn't theirs 404s
     * exactly like one that doesn't exist at all, so it never even reveals
     * that the order exists.
     */
    public function show(Order $order): View
    {
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }

        // No 'address' load — the view renders the order's own immutable
        // shipping snapshot, never the (possibly edited or deleted) live row.
        $order->load([
            'items.product' => fn ($query) => $query->with([
                'variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'),
                'variants.images',
            ]),
            'payment',
            'coupon',
            'earnedVoucher',
        ]);

        $productIds = $order->items->pluck('product_id')->filter()->unique();

        return view('orders.show', [
            'order' => $order,
            // Only publicly visible products get a link; a deleted/inactive
            // product still renders its order-time snapshot, just unlinked.
            'linkableProductIds' => Product::query()->public()->whereIn('id', $productIds)->pluck('id')->all(),
            // Only products from a delivered order that the customer
            // hasn't already reviewed show "Write a Review".
            'reviewableProductIds' => app(ReviewEligibility::class)->reviewableAmong(Auth::id(), $productIds),
        ]);
    }
}
