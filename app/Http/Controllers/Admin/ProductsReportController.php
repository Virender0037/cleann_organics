<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesReportFilters;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductsReportController extends Controller
{
    use HandlesReportFilters;

    public function index(Request $request): View
    {
        $this->validateProductFilters($request);

        $rows = $this->buildRows($request);

        // Simple length-aware pagination over the collapsed result set —
        // the heavy GROUP BY runs once, not per page.
        $perPage = 20;
        $page = max(1, (int) $request->integer('page', 1));
        $paged = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'products_with_sales' => $rows->count(),
            'units_sold' => (int) $rows->sum('units_sold'),
        ];

        return view('admin.reports.products.index', [
            'rows' => $paged,
            'stats' => $stats,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validateProductFilters($request);

        $rows = $this->buildRows($request)->map(fn (array $r) => [
            $r['product_id'],
            $r['name'],
            $r['category'],
            $r['product_status'] ?? 'deleted',
            $r['units_sold'],
            $r['order_count'],
            number_format($r['revenue'], 2, '.', ''),
        ]);

        return $exporter->stream(
            'products-report.csv',
            ['product_id', 'product', 'category', 'product_status', 'units_sold', 'distinct_orders', 'revenue'],
            $rows,
        );
    }

    private function validateProductFilters(Request $request): void
    {
        $this->validatedFilters($request, [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
    }

    /**
     * One aggregated row per historical product identity (product_id +
     * snapshot name), from order_items on PAID orders only. Deleted
     * products (null product_id) keep their sales under the snapshot name.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRows(Request $request): Collection
    {
        [$from, $to] = $this->dateRange($request, defaultToCurrentMonth: false);

        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.created_at', '<=', $to))
            ->when($request->filled('search'), fn ($q) => $q->where('order_items.product_name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->whereIn(
                'order_items.product_id',
                Product::where('category_id', $request->integer('category_id'))->pluck('id'),
            ))
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->selectRaw('
                order_items.product_id as product_id,
                order_items.product_name as product_name,
                SUM(order_items.quantity) as units_sold,
                COUNT(DISTINCT order_items.order_id) as order_count,
                SUM(order_items.total_price) as revenue
            ')
            ->orderByDesc('revenue');

        $aggregates = $query->get();

        // Current metadata (category, status) for the products that still exist.
        $liveProducts = Product::whereIn('id', $aggregates->pluck('product_id')->filter()->unique())
            ->with('category:id,name')
            ->get()
            ->keyBy('id');

        return $aggregates->map(function ($row) use ($liveProducts) {
            $product = $row->product_id ? $liveProducts->get($row->product_id) : null;

            return [
                'product_id' => $row->product_id,
                'name' => $row->product_name ?: ($product?->name ?? 'Unknown product'),
                'category' => $product?->category?->name ?? '—',
                'product_status' => $product?->status,
                'units_sold' => (int) $row->units_sold,
                'order_count' => (int) $row->order_count,
                'revenue' => (float) $row->revenue,
            ];
        })->values();
    }
}
