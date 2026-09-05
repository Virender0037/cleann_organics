<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddWishlistItemRequest;
use App\Models\Product;
use App\Services\Storefront\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Every action here runs behind the `auth` middleware (see routes/web.php) —
 * a guest is redirected to /sign-in before ever reaching a method on this
 * controller, and Laravel's own redirect()->guest()/->intended() handle
 * getting them back afterwards, so there is no guest-handling code here.
 */
class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlist)
    {
    }

    public function index(): View
    {
        return view('wishlist', [
            'lines' => $this->wishlist->lines(),
        ]);
    }

    public function store(AddWishlistItemRequest $request): RedirectResponse
    {
        $result = $this->wishlist->add((int) $request->validated('product_id'));

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Keyed by product, not by a wishlist row id: the delete is scoped to
     * Auth::id() + this product together inside the service, so there is no
     * id a request could supply that would ever touch another customer's
     * row — a nonexistent/never-wishlisted product here is just a no-op,
     * not an error, since the end state the customer wants ("not in my
     * wishlist") is already true.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $result = $this->wishlist->remove($product->id);

        return back()->with('success', $result['message']);
    }
}
