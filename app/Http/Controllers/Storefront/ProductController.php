<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\WishlistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist)
    {
    }

    /**
     * Looked up manually rather than via {product:slug} implicit binding —
     * see CategoryController::show() for why: the admin catalog routes bind
     * the same `{product}` parameter name to a numeric id, and a model-level
     * resolveRouteBinding() override would break those.
     */
    public function show(string $slug): View
    {
        $product = Product::query()
            ->public()
            ->with([
                'category:id,name,slug',
                'taxRate',
                'specifications' => fn ($q) => $q->orderBy('sort_order'),
                'tags:id,name,slug',
                'variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'),
                'variants.images',
                'reviews' => fn ($q) => $q->where('status', 'approved')->latest()->with('user:id,name'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        // Already ordered is_default desc, sort_order asc — first() is the
        // variant to show initially, or null if the product has none (it
        // then renders as not purchasable rather than crashing/faking one).
        $defaultVariant = $product->variants->first();

        $approvedReviews = $product->reviews;
        $reviewCount = $approvedReviews->count();
        // products.average_rating/review_count are not kept in sync by the
        // admin review-approval workflow (see ProductReviewController), so
        // this is computed live from approved reviews rather than trusted.
        $averageRating = $reviewCount > 0 ? round($approvedReviews->avg('rating'), 1) : 0.0;

        $ogImage = $defaultVariant?->images->firstWhere('is_primary', true)
            ?? $defaultVariant?->images->where('media_type', 'image')->sortBy('sort_order')->first();

        // The current user's own review for this product, regardless of its
        // status — used only to decide what the review form area shows
        // (the submit form, a pending/rejected note, or nothing to submit
        // since one already exists); never shown to anyone else, and never
        // mixed into $reviews above, which stays approved-only.
        $userReview = Auth::check()
            ? $product->reviews()->withTrashed()->where('user_id', Auth::id())->first()
            : null;

        return view('products.show', [
            'product' => $product,
            'variants' => $product->variants,
            'defaultVariant' => $defaultVariant,
            'variantsPayload' => $this->variantsPayload($product->variants),
            'reviews' => $approvedReviews,
            'reviewCount' => $reviewCount,
            'averageRating' => $averageRating,
            'isWishlisted' => $this->wishlist->isWishlisted($product->id),
            'userReview' => $userReview,
            'relatedProducts' => $product->relatedProducts(),
            'metaTitle' => $product->meta_title ?: $product->name,
            'metaDescription' => $product->meta_description ?: $product->short_description,
            'canonicalUrl' => $product->canonical_url ?: route('products.show', $product->slug),
            'ogImage' => $ogImage?->image,
        ]);
    }

    /**
     * Every active variant's price/stock/media data, keyed by id, so the
     * storefront JS can rebuild the price block, stock label, and gallery
     * in place when a shopper switches variants — no page reload and no new
     * endpoint, since everything a variant switch needs is already on the
     * page.
     */
    private function variantsPayload($variants): array
    {
        return $variants->mapWithKeys(fn ($variant) => [
            $variant->id => [
                'id' => $variant->id,
                'label' => $variant->displayLabel(),
                'sku' => $variant->sku,
                'stock_quantity' => $variant->stock_quantity,
                'stock_label' => $variant->stockLabel(),
                'purchasable' => $variant->isPurchasable(),
                'tiers' => $variant->pricingTiers(),
                'headline_price' => $variant->headlinePrice(),
                'has_multiple_tiers' => $variant->hasMultipleTiers(),
                'media' => $variant->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => Storage::url($image->image),
                    'type' => $image->media_type,
                    'is_primary' => (bool) $image->is_primary,
                ])->values(),
            ],
        ])->all();
    }
}
