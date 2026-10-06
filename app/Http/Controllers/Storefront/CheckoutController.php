<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Models\Address;
use App\Models\Order;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\StorefrontSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly StorefrontSettings $settings,
    ) {}

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
            'eligibleAmount' => $this->checkout->eligibleAmount(),
            'offers' => $this->settings->offers($this->checkout->eligibleAmount()),
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

        // One Place Order at a time per customer. A double-click (or a retry while the first request is still
        // running) waits here instead of racing it: both requests would otherwise read the same cart and create
        // two orders, decrement stock twice and spend a single-use coupon twice.
        $lock = Cache::lock('checkout:place-order:'.$request->user()->id, 30);

        if (! $lock->block(20)) {
            return redirect()->route('checkout')->with('error', 'Your order is still being placed. Please check your order history before trying again.');
        }

        try {
            $result = $this->checkout->placeOrder($address, $request->validated('payment_method'));
        } finally {
            $lock->release();
        }

        if (! $result['success']) {
            // The duplicate submit of an order that has just gone through: the browser shows THIS response, so send
            // the shopper to that order rather than to an "empty cart" error.
            $justPlaced = ($result['reason'] ?? null) === 'empty_cart'
                ? $request->user()->orders()->where('created_at', '>=', now()->subMinutes(2))->latest('id')->first()
                : null;

            if ($justPlaced) {
                return $this->redirectToOrder($justPlaced);
            }

            return back()->with('error', $result['message']);
        }

        return $this->redirectToOrder($result['order']);
    }

    private function redirectToOrder(Order $order): RedirectResponse
    {
        if ($order->payment_method === 'razorpay') {
            return redirect()->route('orders.pay', $order);
        }

        if ($order->payment_method === 'manual_upi') {
            return redirect()->route('orders.manual-upi.pay', $order);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Your order has been placed! Order #'.$order->order_number);
    }
}
