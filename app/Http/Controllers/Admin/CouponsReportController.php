<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Coupon usage report. State is derived (not a stored column); financial
 * figures come from the orders each coupon is attached to — discount given
 * from every such order, revenue from the paid ones only.
 */
class CouponsReportController extends Controller
{
    private const TYPES = ['fixed', 'percentage'];

    private const STATES = ['active', 'upcoming', 'expired', 'inactive'];

    public function index(Request $request): View
    {
        $this->validateFilters($request);

        $rows = $this->buildRows($request);

        $perPage = 20;
        $page = max(1, (int) $request->integer('page', 1));
        $paged = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $now = Carbon::now();
        $stateCounts = Coupon::get(['status', 'start_date', 'end_date'])
            ->groupBy(fn (Coupon $coupon) => $this->deriveState($coupon, $now))
            ->map->count();

        $stats = [
            'total' => Coupon::count(),
            'active' => $stateCounts['active'] ?? 0,
            'expired' => $stateCounts['expired'] ?? 0,
            'discount_given' => (float) Order::whereNotNull('coupon_id')->sum('discount_amount'),
        ];

        return view('admin.reports.coupons.index', [
            'rows' => $paged,
            'stats' => $stats,
            'types' => self::TYPES,
            'states' => self::STATES,
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validateFilters($request);

        $rows = $this->buildRows($request)->map(fn (array $r) => [
            $r['code'],
            $r['type'],
            number_format($r['value'], 2, '.', ''),
            $r['used_count'],
            $r['usage_limit'] ?? '',
            $r['state'],
            $r['orders_count'],
            number_format($r['discount_generated'], 2, '.', ''),
            number_format($r['paid_revenue'], 2, '.', ''),
        ]);

        return $exporter->stream(
            'coupons-report.csv',
            ['code', 'type', 'value', 'used_count', 'usage_limit', 'state', 'orders_count', 'discount_generated', 'paid_revenue'],
            $rows,
        );
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:'.implode(',', self::TYPES)],
            'state' => ['nullable', 'in:'.implode(',', self::STATES)],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRows(Request $request): Collection
    {
        $now = Carbon::now();

        $coupons = Coupon::query()
            ->when($request->filled('search'), fn ($q) => $q->where('code', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->withCount('orders as orders_count')
            ->withSum('orders as discount_generated', 'discount_amount')
            ->withSum([
                'orders as paid_revenue' => fn ($q) => $q->where('payment_status', 'paid'),
            ], 'grand_total')
            ->orderBy('code')
            ->get();

        return $coupons
            ->map(function (Coupon $coupon) use ($now) {
                return [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                    'used_count' => (int) $coupon->used_count,
                    'usage_limit' => $coupon->usage_limit,
                    'state' => $this->deriveState($coupon, $now),
                    'orders_count' => (int) $coupon->orders_count,
                    'discount_generated' => (float) ($coupon->discount_generated ?? 0),
                    'paid_revenue' => (float) ($coupon->paid_revenue ?? 0),
                ];
            })
            ->when($request->filled('state'), fn ($rows) => $rows->where('state', $request->string('state')->toString()))
            ->values();
    }

    private function deriveState(Coupon $coupon, Carbon $now): string
    {
        if ($coupon->status !== 'active') {
            return 'inactive';
        }

        if ($coupon->start_date && $coupon->start_date->isAfter($now)) {
            return 'upcoming';
        }

        if ($coupon->end_date && $coupon->end_date->isBefore($now)) {
            return 'expired';
        }

        return 'active';
    }
}
