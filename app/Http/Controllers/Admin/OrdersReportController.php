<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesReportFilters;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CsvExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrdersReportController extends Controller
{
    use HandlesReportFilters;

    private const ORDER_STATUSES = ['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled'];

    private const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    private const PAYMENT_METHODS = ['cod', 'upi', 'bank_transfer'];

    public function index(Request $request): View
    {
        $this->validateOrderFilters($request);
        $base = $this->baseQuery($request);

        $stats = [
            'total' => (clone $base)->count(),
            'delivered' => (clone $base)->where('order_status', 'delivered')->count(),
            'in_progress' => (clone $base)->whereNotIn('order_status', ['delivered', 'cancelled'])->count(),
            'cancelled' => (clone $base)->where('order_status', 'cancelled')->count(),
        ];

        $orders = $this->baseQuery($request)
            ->with('user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.orders.index', [
            'orders' => $orders,
            'stats' => $stats,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validateOrderFilters($request);

        $orders = $this->baseQuery($request)
            ->with('user')
            ->latest()
            ->lazy(200);

        $rows = $orders->map(fn (Order $order) => [
            $order->order_number,
            $order->user->name ?? null,
            $order->user->email ?? null,
            $order->created_at->format('Y-m-d'),
            $order->payment_method,
            $order->payment_status,
            $order->order_status,
            number_format((float) $order->grand_total, 2, '.', ''),
        ]);

        return $exporter->stream(
            'orders-report.csv',
            ['order_number', 'customer_name', 'customer_email', 'date', 'payment_method', 'payment_status', 'order_status', 'grand_total'],
            $rows,
        );
    }

    private function validateOrderFilters(Request $request): void
    {
        $this->validatedFilters($request, [
            'order_status' => ['nullable', 'in:'.implode(',', self::ORDER_STATUSES)],
            'payment_status' => ['nullable', 'in:'.implode(',', self::PAYMENT_STATUSES)],
            'payment_method' => ['nullable', 'in:'.implode(',', self::PAYMENT_METHODS)],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        // All-time by default (spec) — a date range only applies when the
        // admin supplies from/to.
        [$from, $to] = $this->dateRange($request, defaultToCurrentMonth: false);

        return $this->applyDateRange(Order::query(), $from, $to)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('order_status'), fn ($q) => $q->where('order_status', $request->string('order_status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->string('payment_method')));
    }
}
