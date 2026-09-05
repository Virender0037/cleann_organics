<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Storefront\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request, ProductCatalogService $catalog): View
    {
        return view('shop', [
            'products' => $catalog->paginate($request),
            'categories' => Category::query()->active()->ordered()->get(),
            'categoryCounts' => $catalog->categoryProductCounts(),
            'tags' => Tag::query()->where('status', 'active')->orderBy('name')->get(),
            'saleProducts' => $catalog->bestSellers(),
            'priceBounds' => $catalog->priceBounds(),
            'activeCategory' => null,
            'metaTitle' => 'Shop',
            'canonicalUrl' => route('shop'),
        ]);
    }
}
