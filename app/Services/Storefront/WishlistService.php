<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Wishlist is authenticated-customer-only by design (see CLAUDE.md/Phase H
 * spec: guests must never get an anonymous DB row) — unlike CartService,
 * there is no guest/session branch here at all. Every method assumes
 * Auth::check() is already true; callers reach this only through routes
 * gated by the `auth` middleware, so a guest never gets this far.
 */
class WishlistService
{
    /**
     * Product ids the current user has wishlisted, memoized for the
     * duration of the request — product-card renders this component
     * possibly dozens of times per page (shop grid, related products), so
     * this must never run more than one query per request regardless of
     * how many times isWishlisted() is called.
     */
    private function wishlistedProductIds(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return once(fn () => Wishlist::query()
            ->where('user_id', Auth::id())
            ->pluck('product_id'));
    }

    public function isWishlisted(int $productId): bool
    {
        return $this->wishlistedProductIds()->contains($productId);
    }

    public function count(): int
    {
        return $this->wishlistedProductIds()->count();
    }

    /**
     * Every real, storefront-visible product this user has wishlisted,
     * including ones that have since gone inactive/out of stock — those
     * are still returned (so the page can show them as unavailable and let
     * the customer remove them) unless the product itself was hard-deleted,
     * in which case the row has nothing left to render and is dropped.
     *
     * @return Collection<int, Wishlist>
     */
    public function lines(): Collection
    {
        if (! Auth::check()) {
            return collect();
        }

        return Wishlist::query()
            ->where('user_id', Auth::id())
            ->with([
                'product.category',
                'product.variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'),
                'product.variants.images',
            ])
            ->latest()
            ->get()
            ->filter(fn (Wishlist $wishlist) => $wishlist->product !== null)
            ->values();
    }

    /**
     * Adds a product, never trusting the id alone: it must resolve to a
     * real, currently-public (active product + active category) product —
     * the same Phase F storefront-visibility rule cart/PDP already apply.
     * Adding an already-wishlisted product is a friendly no-op, not an
     * error — the unique(user_id, product_id) constraint is the last line
     * of defense against a duplicate row, but this check avoids ever
     * relying on catching that constraint violation.
     *
     * @return array{success: bool, message: string}
     */
    public function add(int $productId): array
    {
        $product = Product::query()->public()->find($productId);

        if (! $product) {
            return ['success' => false, 'message' => 'This product is not available.'];
        }

        $wishlist = Wishlist::query()->firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        return [
            'success' => true,
            'message' => $wishlist->wasRecentlyCreated ? 'Added to your wishlist.' : 'Already in your wishlist.',
        ];
    }

    /**
     * Scoped by user_id AND product_id together — never by a bare wishlist
     * row id — so there is no id a client could submit that would ever
     * match a row belonging to someone else. Removing something that was
     * never there is treated the same as success (nothing left to do).
     *
     * @return array{success: bool, message: string}
     */
    public function remove(int $productId): array
    {
        Wishlist::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        return ['success' => true, 'message' => 'Removed from your wishlist.'];
    }
}
