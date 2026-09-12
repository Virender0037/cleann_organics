<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\ManualUpiPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The page manual_upi-method orders land on after checkout, and return to
 * in order to resubmit proof after a rejection. Every action re-checks
 * order ownership the same way RazorpayPaymentController/OrderController
 * does — a customer can never act on (or view the proof of) another
 * customer's order by guessing its id.
 */
class ManualUpiPaymentController extends Controller
{
    public function __construct(private readonly ManualUpiPaymentService $manualUpi) {}

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->payment_method !== 'manual_upi') {
            abort(404);
        }

        $order->load('payment');

        if ($order->payment?->status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        return view('orders.manual-upi-pay', [
            'order' => $order,
            'payment' => $order->payment,
            'details' => $this->manualUpi->buildPaymentDetails($order),
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->payment_method !== 'manual_upi') {
            abort(404);
        }

        $order->loadMissing('payment');

        if (! $order->payment || $order->payment->status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        $validated = $request->validate([
            'upi_reference' => ['required', 'string', 'max:100'],
            'screenshot' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $this->manualUpi->submitProof($order, $validated['upi_reference'], $request->file('screenshot'));

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Thanks — we\'ll verify your payment shortly.');
    }

    /** The customer's own view of their submitted proof — never anyone else's. */
    public function proof(Order $order): StreamedResponse
    {
        $this->authorizeOrder($order);

        $order->loadMissing('payment');

        if (! $order->payment) {
            abort(404);
        }

        return $this->manualUpi->streamProof($order->payment);
    }

    private function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }
    }
}
