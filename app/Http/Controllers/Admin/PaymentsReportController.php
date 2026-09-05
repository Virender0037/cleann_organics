<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesReportFilters;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CsvExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Payment-transaction report. Every financial total is SUM(payments.amount)
 * for the given status — never Order.grand_total — and there is exactly one
 * Payment row per Order, so no figure is double-counted.
 */
class PaymentsReportController extends Controller
{
    use HandlesReportFilters;

    private const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    private const PAYMENT_METHODS = ['cod', 'upi', 'bank_transfer'];

    public function index(Request $request): View
    {
        $this->validatePaymentFilters($request);

        $base = $this->baseQuery($request);

        $totals = (clone $base)->selectRaw("
            COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as collected,
            COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending,
            COALESCE(SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END), 0) as failed,
            COALESCE(SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END), 0) as refunded,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
        ")->first();

        $payments = $this->baseQuery($request)
            ->with('order.user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.payments.index', [
            'payments' => $payments,
            'metrics' => [
                'collected' => (float) $totals->collected,
                'pending' => (float) $totals->pending,
                'failed' => (float) $totals->failed,
                'failed_count' => (int) $totals->failed_count,
                'refunded' => (float) $totals->refunded,
            ],
            'statuses' => self::STATUSES,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validatePaymentFilters($request);

        $rows = $this->baseQuery($request)
            ->with('order.user')
            ->latest()
            ->lazy(200)
            ->map(fn (Payment $payment) => [
                $payment->order->order_number ?? null,
                $payment->transaction_id,
                $payment->order->user->name ?? null,
                $payment->order->user->email ?? null,
                $payment->payment_method,
                number_format((float) $payment->amount, 2, '.', ''),
                $payment->status,
                $payment->paid_at?->format('Y-m-d H:i'),
                $payment->created_at->format('Y-m-d'),
            ]);

        return $exporter->stream(
            'payments-report.csv',
            ['order_number', 'transaction_id', 'customer_name', 'customer_email', 'payment_method', 'amount', 'status', 'paid_at', 'date'],
            $rows,
        );
    }

    private function validatePaymentFilters(Request $request): void
    {
        $this->validatedFilters($request, [
            'status' => ['nullable', 'in:'.implode(',', self::STATUSES)],
            'payment_method' => ['nullable', 'in:'.implode(',', self::PAYMENT_METHODS)],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        // Payments default to the current calendar month (spec).
        [$from, $to] = $this->dateRange($request, defaultToCurrentMonth: true);

        return $this->applyDateRange(Payment::query(), $from, $to)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('transaction_id', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($q) => $q->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('order.user', fn ($q) => $q
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->string('payment_method')));
    }
}
