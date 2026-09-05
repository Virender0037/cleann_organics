<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\Storefront\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Looks up the category manually (rather than via {category:slug}
     * implicit route-model binding) because the admin catalog routes bind
     * the same `{category}` parameter name to a numeric id — a model-level
     * resolveRouteBinding() override to scope this to active/by-slug would
     * silently break every admin category edit/update/delete route.
     */
    public function show(Request $request, string $slug, ProductCatalogService $catalog): View
    {
        $category = Category::query()->active()->where('slug', $slug)->firstOrFail();

        return view('shop', [
            'products' => $catalog->paginate($request, $category),
            'categories' => Category::query()->active()->ordered()->get(),
            'categoryCounts' => $catalog->categoryProductCounts(),
            'tags' => Tag::query()->where('status', 'active')->orderBy('name')->get(),
            'saleProducts' => $catalog->bestSellers(),
            'priceBounds' => $catalog->priceBounds(),
            'activeCategory' => $category,
            'metaTitle' => $category->meta_title ?: $category->name,
            'metaDescription' => $category->meta_description,
            'canonicalUrl' => route('category.show', $category->slug),
        ]);
    }
}
