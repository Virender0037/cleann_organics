<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Models\ProductVariant;
use App\Support\Marketplaces;
use Illuminate\Support\Collection;

/**
 * Turns a product's marketplace rows into what the storefront renders: which offers apply to a variant,
 * whether our own price is genuinely the best, and the display data for each offer.
 *
 * Own price = the variant's headline price (ProductVariant::headlinePrice()) — the very same number the
 * product card and detail page lead with — so no pricing rule is duplicated here.
 */
class MarketplaceComparison
{
    /**
     * Offers that apply to $variant: for each marketplace, a row assigned to that exact variant wins, otherwise the
     * product-level row (product_variant_id NULL) is the fallback. Only visible (active + priced + enabled) rows count.
     *
     * Reads `visibleMarketplacePrices`; callers on list pages must eager-load it (this never lazy-loads there —
     * see hasLoadedOffers()).
     *
     * @return Collection<int, ProductMarketplacePrice>
     */
    public function offersFor(Product $product, ?ProductVariant $variant): Collection
    {
        $rows = $product->visibleMarketplacePrices
            ->filter(fn (ProductMarketplacePrice $row) => $row->isDisplayable());

        $chosen = [];

        foreach ($rows as $row) {
            $applies = $row->product_variant_id === null
                || ($variant !== null && (int) $row->product_variant_id === (int) $variant->id);

            if (! $applies) {
                continue;
            }

            $current = $chosen[$row->marketplace] ?? null;

            // A variant-specific row always beats the product-level fallback for the same marketplace.
            if ($current === null || ($current->product_variant_id === null && $row->product_variant_id !== null)) {
                $chosen[$row->marketplace] = $row;
            }
        }

        return collect($chosen)->sortBy([['sort_order', 'asc'], ['id', 'asc']])->values();
    }

    /** List pages pass a product whose offers were eager-loaded; anything else must not trigger N+1 queries. */
    public function hasLoadedOffers(Product $product): bool
    {
        return $product->relationLoaded('visibleMarketplacePrices');
    }

    /**
     * "Best Price" only when our price is strictly lower than EVERY visible marketplace offer.
     * Ties, missing own price, or no offers all mean no claim is made.
     *
     * @param  Collection<int, ProductMarketplacePrice>  $offers
     */
    public function isBestPrice(?float $ownPrice, Collection $offers): bool
    {
        if ($ownPrice === null || $offers->isEmpty()) {
            return false;
        }

        return $offers->every(fn (ProductMarketplacePrice $offer) => $ownPrice < (float) $offer->selling_price);
    }

    /**
     * Everything the comparison view needs for one product + variant, or null when there is nothing to compare
     * (in which case the caller renders no section, button or dialog at all).
     *
     * @return array<string, mixed>|null
     */
    public function payload(Product $product, ?ProductVariant $variant, string $source): ?array
    {
        if (! $this->hasLoadedOffers($product)) {
            return null;
        }

        $offers = $this->offersFor($product, $variant);

        if ($offers->isEmpty()) {
            return null;
        }

        $own = $variant?->headlinePrice();

        return [
            'product' => $product,
            'variant' => $variant,
            'productName' => $product->name,
            'productUrl' => route('products.show', $product->slug),
            'ownPrice' => $own,
            'purchasable' => (bool) ($variant && $variant->isPurchasable() && $own !== null),
            'isBest' => $this->isBestPrice($own, $offers),
            'source' => $source,
            'offers' => $offers->map(fn (ProductMarketplacePrice $offer) => [
                'id' => $offer->id,
                'key' => $offer->marketplace,
                'label' => $offer->label(),
                'logo' => Marketplaces::logoUrl($offer->marketplace),
                'price' => (float) $offer->selling_price,
                'mrp' => $offer->mrp !== null ? (float) $offer->mrp : null,
                'discount' => $offer->discountPercent(),
                'url' => $offer->outboundUrl($source),
                'checked' => $offer->last_checked_at?->format('j M'),
            ])->all(),
        ];
    }
}
