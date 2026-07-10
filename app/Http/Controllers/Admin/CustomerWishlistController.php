<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerWishlistController extends Controller
{
    public function index(Request $request, User $customer): View
    {
        $wishlists = $customer->wishlists()
            ->with(['product.category', 'product.defaultVariant'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('variants', fn ($query) => $query->where('sku', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->whereHas('product', fn ($query) => $query->where('category_id', $request->integer('category_id'))))
            ->when($request->filled('stock_status'), fn ($query) => $query->whereHas('product.defaultVariant', fn ($query) => $query->where('stock_status', $request->string('stock_status'))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::ordered()->get();

        return view('admin.customers.wishlists.index', compact('customer', 'wishlists', 'categories'));
    }

    public function destroy(User $customer, Wishlist $wishlist): RedirectResponse
    {
        $wishlist->delete();

        return back()->with('success', 'Removed from wishlist.');
    }
}
