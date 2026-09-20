<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CsvExporter;
use App\Services\Payment\ManualUpiPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesPaymentController extends Controller
{
    public function __construct(private readonly ManualUpiPaymentService $manualUpi) {}

    public function index(Request $request): View
    {
        $payments = Payment::with('order.user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                        ->orWhere('gateway_order_id', 'like', "%{$search}%")
                        ->orWhere('upi_reference', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($query) use ($search) {
                            $query->where('order_number', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($request->filled('payment_method'), fn ($query) => $query->where('payment_method', $request->string('payment_method')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.payments.index', compact('payments'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $payments = Payment::with('order.user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                        ->orWhere('gateway_order_id', 'like', "%{$search}%")
                        ->orWhere('upi_reference', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($query) use ($search) {
                            $query->where('order_number', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($request->filled('payment_method'), fn ($query) => $query->where('payment_method', $request->string('payment_method')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->lazy(200);

        $headers = ['id', 'order_number', 'customer_name', 'customer_email', 'transaction_id', 'payment_method', 'amount', 'status', 'paid_at'];

        $rows = $payments->map(fn (Payment $payment) => [
            $payment->id,
            $payment->order->order_number ?? null,
            $payment->order->user->name ?? null,
            $payment->order->user->email ?? null,
            $payment->transaction_id,
            $payment->payment_method,
            $payment->amount,
            $payment->status,
            $payment->paid_at?->format('d M Y H:i'),
        ]);

        return $exporter->stream('sales-payments.csv', $headers, $rows);
    }

    public function show(Payment $payment): View
    {
        $payment->load('order.user');

        return view('admin.sales.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->payment_method !== 'manual_upi') {
            abort(404);
        }

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->manualUpi->verify($payment, $validated['admin_note'] ?? null);

        return redirect()
            ->route('admin.sales.payments.show', $payment)
            ->with('success', 'Payment marked as paid.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->payment_method !== 'manual_upi') {
            abort(404);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $this->manualUpi->reject($payment, $validated['admin_note']);

        return redirect()
            ->route('admin.sales.payments.show', $payment)
            ->with('success', 'Payment rejected — the customer can resubmit proof.');
    }

    /** Admin's view of a customer's submitted proof — gated by the whole admin route group's `superadmin` middleware. */
    public function proof(Payment $payment): StreamedResponse
    {
        return $this->manualUpi->streamProof($payment);
    }
}
