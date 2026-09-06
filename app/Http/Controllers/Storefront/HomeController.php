<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\News\EnvironmentalNewsService;
use App\Services\Storefront\ProductCatalogService;
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

    private const NEWS_LIMIT = 9;

    public function index(ProductCatalogService $catalog, EnvironmentalNewsService $news): View
    {
        return view('home', [
            'homeCategories' => Category::query()->active()->ordered()->limit(self::CATEGORY_LIMIT)->get(),
            'popularProducts' => $catalog->bestSellers(self::POPULAR_PRODUCTS_LIMIT),
            'featuredProducts' => $catalog->featured(self::FEATURED_PRODUCTS_LIMIT),
            'dealProducts' => $catalog->dealsProducts(self::HOT_DEALS_LIMIT),
            'environmentalNews' => $news->latest(self::NEWS_LIMIT),
        ]);
    }
}
