<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Models\Address;
use App\Services\Storefront\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Every route here is behind the `auth` middleware (see routes/web.php) —
 * an Address belongs to a customer, so there is no guest checkout to
 * support. Thin by design: all pricing/stock/coupon/shipping logic lives in
 * CheckoutService, matching the CartService/WishlistService pattern from
 * Phases G and H.
 */
class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout)
    {
    }

    /**
     * Address selection is a plain GET (?address_id=) that re-renders this
     * same page — not client-side JS — so shipping/tax/total always come
     * from a real server computation for the address actually selected,
     * never a duplicated client-side copy of the pricing rules. The address
     * radios auto-submit that GET on change (a small, inline, no-framework
     * form submit — see checkout.blade.php), so switching address still
     * feels immediate without any hand-rolled state syncing.
     */
    public function index(Request $request): View
    {
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->latest()->get();

        $selectedAddress = $request->filled('address_id')
            ? $addresses->firstWhere('id', $request->integer('address_id'))
            : null;
        $selectedAddress ??= $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return view('checkout', [
            'lines' => $this->checkout->lines(),
            'hasUnavailableLines' => $this->checkout->hasUnavailableLines(),
            'addresses' => $addresses,
            'selectedAddress' => $selectedAddress,
            'subtotal' => $this->checkout->subtotal(),
            'taxAmount' => $this->checkout->taxAmount(),
            'appliedCoupon' => $this->checkout->appliedCoupon(),
            'discountAmount' => $this->checkout->discountAmount(),
            'shippingAmount' => $selectedAddress ? $this->checkout->shippingAmount($selectedAddress) : 0.0,
            'grandTotal' => $selectedAddress ? $this->checkout->grandTotal($selectedAddress) : null,
        ]);
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $result = $this->checkout->applyCoupon($request->validated('code'));

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function removeCoupon(): RedirectResponse
    {
        $this->checkout->removeCoupon();

        return back()->with('success', 'Coupon removed.');
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $address = Address::findOrFail($request->validated('address_id'));

        $result = $this->checkout->placeOrder($address, $request->validated('payment_method'));

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('orders.show', $result['order'])
            ->with('success', 'Your order has been placed! Order #'.$result['order']->order_number);
    }
}
