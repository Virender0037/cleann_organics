<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Services\Storefront\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Thin by design — every read/mutation is delegated to CartService, which
 * is the only place cart business logic (pricing, stock, ownership) lives.
 */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('shopping-cart', [
            'lines' => $lines,
            'subtotal' => $this->cart->subtotal(),
            'itemCount' => $this->cart->itemCount(),
        ]);
    }

    /**
     * Small JSON partial used to refresh the header mini-cart via fetch()
     * after a mutation elsewhere on the page, without a full reload and
     * without re-rendering (or re-querying for) the rest of the page.
     */
    public function mini(): View
    {
        return view('components.frontend.cart-mini-contents', [
            'lines' => $this->cart->lines(),
            'subtotal' => $this->cart->subtotal(),
            'itemCount' => $this->cart->itemCount(),
        ]);
    }

    public function store(AddCartItemRequest $request): RedirectResponse|JsonResponse
    {
        $result = $this->cart->addItem(
            (int) $request->validated('product_variant_id'),
            (int) $request->validated('quantity')
        );

        return $this->respond($request, $result);
    }

    public function update(UpdateCartItemRequest $request, string $item): RedirectResponse|JsonResponse
    {
        $result = $this->cart->updateItem($item, (int) $request->validated('quantity'));

        return $this->respond($request, $result);
    }

    public function destroy(Request $request, string $item): RedirectResponse|JsonResponse
    {
        $result = $this->cart->removeItem($item);

        return $this->respond($request, $result);
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        $result = ['success' => true, 'message' => 'Cart cleared.'];

        return $this->respond($request, $result);
    }

    /**
     * @param  array{success: bool, message: string, line?: array}  $result
     */
    private function respond(Request $request, array $result): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'itemCount' => $this->cart->itemCount(),
                'subtotal' => $this->cart->subtotal(),
                'miniCartHtml' => view('components.frontend.cart-mini-contents', [
                    'lines' => $this->cart->lines(),
                    'subtotal' => $this->cart->subtotal(),
                    'itemCount' => $this->cart->itemCount(),
                ])->render(),
            ], $result['success'] ? 200 : 422);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
