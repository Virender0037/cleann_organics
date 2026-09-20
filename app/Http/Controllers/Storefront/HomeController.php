<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Reel;
use App\Services\News\EnvironmentalNewsService;
use App\Services\Storefront\ProductCatalogService;
use App\Services\Storefront\StorefrontSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Slide/card counts match what the original static Shopery markup
     * showed per section, so a fully-stocked catalog renders visually the
     * same as before — see the STOREFRONT REAL-DATA AUDIT. A thinner
     * catalog simply renders fewer real cards; nothing is padded out with
     * fake ones.
     */
    private const CATEGORY_LIMIT = 12;

    private const POPULAR_PRODUCTS_LIMIT = 10;

    private const FEATURED_PRODUCTS_LIMIT = 6;

    private const HOT_DEALS_LIMIT = 12;

    private const COLLECTION_LIMIT = 8;

    private const REELS_LIMIT = 8;

    private const NEWS_LIMIT = 9;

    public function index(ProductCatalogService $catalog, EnvironmentalNewsService $news, StorefrontSettings $settings): View
    {
        return view('home', [
            // One slideshow, fully admin-managed (Admin → CMS → Homepage
            // Banners); every slide resolves its own click-through.
            'banners' => HomeBanner::query()->section(HomeBanner::SECTION_HERO)->active()->ordered()->with(['product:id,slug', 'category:id,slug', 'tag:id,slug'])->get(),
            'benefits' => HomeBanner::query()->section(HomeBanner::SECTION_BENEFIT)->active()->ordered()->with(['product:id,slug', 'category:id,slug', 'tag:id,slug'])->get(),
            'homeCategories' => Category::query()->active()->ordered()->limit(self::CATEGORY_LIMIT)->get(),
            'priceBands' => $catalog->priceBands(),
            'popularProducts' => $catalog->bestSellers(self::POPULAR_PRODUCTS_LIMIT),
            'featuredProducts' => $catalog->featured(self::FEATURED_PRODUCTS_LIMIT),
            'dealProducts' => $catalog->dealsProducts(self::HOT_DEALS_LIMIT),
            'bioEnzymeProducts' => $catalog->byTagSlug(ProductCatalogService::TAG_BIO_ENZYME, self::COLLECTION_LIMIT),
            'lowestPriceProducts' => $catalog->byTagSlug(ProductCatalogService::TAG_LOWEST_PRICE_365, self::COLLECTION_LIMIT),
            'reels' => Reel::query()->active()->ordered()
                ->with(['product.variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'), 'product.variants.images'])
                ->limit(self::REELS_LIMIT)->get(),
            'instagramUrl' => $settings->instagramUrl(),
            'freeShippingLabel' => $settings->formatMoney($settings->freeShippingThreshold()),
            'environmentalNews' => $news->latest(self::NEWS_LIMIT),
            // Fallback for the Latest News section when the external feed has nothing: real blog posts.
            'latestBlogs' => \App\Models\Blog::query()->published()->with('category')->latest('published_at')->limit(6)->get(),
        ]);
    }
}
