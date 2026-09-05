<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CsvExporter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Current stock snapshot across active product variants. No date filter —
 * this report only ever describes stock as it stands right now.
 */
class InventoryReportController extends Controller
{
    private const STOCK_FILTERS = ['in_stock', 'low_stock', 'out_of_stock'];

    private const VARIANT_STATUSES = ['active', 'inactive'];

    public function index(Request $request): View
    {
        $this->validateFilters($request);

        $active = ProductVariant::query()->where('status', 'active');

        $stats = [
            'active_variants' => (clone $active)->count(),
            'in_stock' => (clone $active)
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '>', 'low_stock_quantity')
                ->where('stock_status', '!=', 'out_of_stock')
                ->count(),
            'low_stock' => (clone $active)
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_quantity')
                ->where('stock_status', '!=', 'out_of_stock')
                ->count(),
            'out_of_stock' => (clone $active)
                ->where(fn ($q) => $q->where('stock_quantity', '<=', 0)->orWhere('stock_status', 'out_of_stock'))
                ->count(),
            'inactive_variants' => ProductVariant::query()->where('status', 'inactive')->count(),
        ];

        $variants = $this->baseQuery($request)
            ->orderBy('stock_quantity')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.inventory.index', [
            'variants' => $variants,
            'stats' => $stats,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'stockFilters' => self::STOCK_FILTERS,
            'variantStatuses' => self::VARIANT_STATUSES,
        ]);
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $this->validateFilters($request);

        $rows = $this->baseQuery($request)
            ->orderBy('stock_quantity')
            ->lazy(200)
            ->map(fn (ProductVariant $variant) => [
                $variant->sku,
                $variant->product->name ?? '—',
                $variant->variant_name,
                $variant->product->category->name ?? '—',
                $variant->stock_quantity,
                $variant->low_stock_quantity,
                $this->stockLabel($variant),
                $variant->product->status ?? '—',
                $variant->status,
            ]);

        return $exporter->stream(
            'inventory-report.csv',
            ['sku', 'product', 'variant', 'category', 'current_stock', 'low_stock_threshold', 'stock_status', 'product_status', 'variant_status'],
            $rows,
        );
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'stock' => ['nullable', 'in:'.implode(',', self::STOCK_FILTERS)],
            'variant_status' => ['nullable', 'in:'.implode(',', self::VARIANT_STATUSES)],
        ]);
    }

    private function baseQuery(Request $request): Builder
    {
        return ProductVariant::with(['product.category'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('variant_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('variant_status'), fn ($q) => $q->where('status', $request->string('variant_status')))
            ->when($request->string('stock')->toString() === 'in_stock', fn ($q) => $q
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '>', 'low_stock_quantity')
                ->where('stock_status', '!=', 'out_of_stock'))
            ->when($request->string('stock')->toString() === 'low_stock', fn ($q) => $q
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_quantity')
                ->where('stock_status', '!=', 'out_of_stock'))
            ->when($request->string('stock')->toString() === 'out_of_stock', fn ($q) => $q
                ->where(fn ($inner) => $inner->where('stock_quantity', '<=', 0)->orWhere('stock_status', 'out_of_stock')));
    }

    private function stockLabel(ProductVariant $variant): string
    {
        if ($variant->stock_quantity <= 0 || $variant->stock_status === 'out_of_stock') {
            return 'out_of_stock';
        }

        if ($variant->stock_quantity <= $variant->low_stock_quantity) {
            return 'low_stock';
        }

        return 'in_stock';
    }
}
