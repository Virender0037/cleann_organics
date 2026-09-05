<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesReportFilters;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CsvExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportController extends Controller
{
    use HandlesReportFilters;

    private const PAYMENT_METHODS = ['cod', 'upi', 'bank_transfer'];

    public function index(Request $request): View
    {
        $this->validatedFilters($request, [
            'payment_method' => ['nullable', 'in:'.implode(',', self::PAYMENT_METHODS)],
        ]);

        [$from, $to] = $this->dateRange($request, defaultToCurrentMonth: true);
        $method = $request->string('payment_method')->toString() ?: null;

        // Paid orders drive every "revenue" number. The daily table below
        // is the same paid set, broken out by day.
        $paid = $this->paidOrdersQuery($from, $to, $method);

        $paidTotals = (clone $paid)->selectRaw('
            COUNT(*) as paid_orders,
            COALESCE(SUM(subtotal), 0) as subtotal,
            COALESCE(SUM(discount_amount), 0) as discount_amount,
            COALESCE(SUM(shipping_amount), 0) as shipping_amount,
            COALESCE(SUM(tax_amount), 0) as tax_amount,
            COALESCE(SUM(grand_total), 0) as grand_total
        ')->first();

        // Placed order value is an operational figure (everything not
        // cancelled), NOT revenue — labelled as such in the view.
        $placedOrderValue = (float) $this->baseOrdersInRange($from, $to, $method)
            ->where('order_status', '!=', 'cancelled')
            ->sum('grand_total');

        $refundedValue = (float) $this->baseOrdersInRange($from, $to, $method)
            ->where('payment_status', 'refunded')
            ->sum('grand_total');

        $paidRevenue = (float) $paidTotals->grand_total;
        $paidOrders = (int) $paidTotals->paid_orders;

        $daily = (clone $paid)
            ->selectRaw('
                DATE(created_at) as day,
                COUNT(*) as orders,
                COALESCE(SUM(subtotal), 0) as subtotal,
                COALESCE(SUM(discount_amount), 0) as discount_amount,
                COALESCE(SUM(shipping_amount), 0) as shipping_amount,
                COALESCE(SUM(tax_amount), 0) as tax_amount,
                COALESCE(SUM(grand_total), 0) as grand_total
            ')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('day')
            ->paginate(31)
            ->withQueryString();

        return view('admin.reports.sales.index', [
            'from' => $from,
            'to' => $to,
            'paymentMethods' => self::PAYMENT_METHODS,
            'metrics' => [
                'placed_order_value' => $placedOrderValue,
                'paid_revenue' => $paidRevenue,
                'paid_orders' => $paidOrders,
                'average_order_value' => $paidOrders > 0 ? round($paidRevenue / $paidOrders, 2) : 0.0,
                'refunded_value' => $refundedValue,
            ],
            'breakdown' => [
                'subtotal' => (float) $paidTotals->subtotal,
                'discount_amount' => (float) $paidTotals->discount_amount,
                'shipping_amount' => (float) $paidTotals->shipping_amount,
                'tax_amount' => (float) $paidTotals->tax_amount,
                'grand_total' => (float) $paidTotals->grand_total,
            ],
            'daily' => $daily,
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validatedFilters($request, [
            'payment_method' => ['nullable', 'in:'.implode(',', self::PAYMENT_METHODS)],
        ]);

        [$from, $to] = $this->dateRange($request, defaultToCurrentMonth: true);
        $method = $request->string('payment_method')->toString() ?: null;

        $rows = $this->paidOrdersQuery($from, $to, $method)
            ->selectRaw('
                DATE(created_at) as day,
                COUNT(*) as orders,
                COALESCE(SUM(subtotal), 0) as subtotal,
                COALESCE(SUM(discount_amount), 0) as discount_amount,
                COALESCE(SUM(shipping_amount), 0) as shipping_amount,
                COALESCE(SUM(tax_amount), 0) as tax_amount,
                COALESCE(SUM(grand_total), 0) as grand_total
            ')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get()
            ->map(fn ($r) => [
                $r->day,
                $r->orders,
                number_format((float) $r->subtotal, 2, '.', ''),
                number_format((float) $r->discount_amount, 2, '.', ''),
                number_format((float) $r->shipping_amount, 2, '.', ''),
                number_format((float) $r->tax_amount, 2, '.', ''),
                number_format((float) $r->grand_total, 2, '.', ''),
            ]);

        return $exporter->stream(
            'sales-report.csv',
            ['date', 'paid_orders', 'subtotal', 'discount', 'shipping', 'tax', 'paid_revenue'],
            $rows,
        );
    }

    /** Base: all orders in the range, optionally narrowed by payment method. */
    private function baseOrdersInRange(?Carbon $from, ?Carbon $to, ?string $method): Builder
    {
        return $this->applyDateRange(
            Order::query()->when($method, fn ($q) => $q->where('payment_method', $method)),
            $from,
            $to,
        );
    }

    /** The authoritative "paid revenue" set — never redefinable by a filter. */
    private function paidOrdersQuery(?Carbon $from, ?Carbon $to, ?string $method): Builder
    {
        return $this->baseOrdersInRange($from, $to, $method)->where('payment_status', 'paid');
    }
}
