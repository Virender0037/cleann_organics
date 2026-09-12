<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\RazorpayPaymentService;
use App\Services\Payment\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The page Razorpay-method orders land on after checkout (and can return to
 * to retry). Every action re-checks order ownership the same way
 * OrderController::show does — a customer can never act on another
 * customer's order by guessing its id.
 */
class RazorpayPaymentController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly RazorpayPaymentService $paymentService,
    ) {}

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->payment_method !== 'razorpay') {
            abort(404);
        }

        $order->load('payment');

        if ($order->payment?->status === 'paid') {
            return redirect()->route('orders.show', $order);
        }

        $gatewayError = null;

        try {
            $payment = $this->paymentService->ensureGatewayOrder($order);
        } catch (\Throwable $e) {
            $payment = $order->payment;
            $gatewayError = 'We could not start the payment right now. Please try again in a moment, or choose Cash on Delivery instead.';
        }

        $user = Auth::user();

        return view('orders.pay', [
            'order' => $order,
            'payment' => $payment,
            'gatewayError' => $gatewayError,
            'razorpayKeyId' => $this->razorpay->keyId(),
            'amountInPaise' => (int) round(((float) $payment->amount) * 100),
            'customerName' => $user->name,
            'customerEmail' => $user->email,
            'customerPhone' => $order->shipping_phone,
        ]);
    }

    public function verify(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order->loadMissing('payment');

        if (! $order->payment) {
            abort(404);
        }

        if ($order->payment->status === 'paid') {
            return redirect()->route('orders.show', $order)->with('success', 'Payment already confirmed for this order.');
        }

        if ($order->payment->gateway_order_id !== $validated['razorpay_order_id']) {
            Log::warning('razorpay.verify_order_id_mismatch', ['order_id' => $order->id]);

            return redirect()->route('orders.pay', $order)->with('error', 'This payment could not be verified. Please try again.');
        }

        $signatureValid = $this->razorpay->verifyPaymentSignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        );

        if (! $signatureValid) {
            Log::warning('razorpay.verify_signature_invalid', ['order_id' => $order->id]);

            $this->paymentService->markFailed($order, 'Signature verification failed.');

            return redirect()->route('orders.pay', $order)->with('error', 'We could not verify this payment. Please try again or choose another payment method.');
        }

        $this->paymentService->markCaptured(
            $order,
            $validated['razorpay_payment_id'],
            $validated['razorpay_order_id'],
            $validated['razorpay_signature'],
        );

        return redirect()->route('orders.show', $order)->with('success', 'Payment successful! Order #'.$order->order_number);
    }

    /** Fired by the browser when the customer closes the Razorpay modal without paying. */
    public function cancel(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $this->paymentService->markCancelled($order);

        return response()->json(['message' => 'ok']);
    }

    private function authorizeOrder(Order $order): void
    {
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }
    }
}
