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

class InventoryController extends Controller
{
    public function stockLevels(Request $request): View
    {
        $variants = $this->baseVariantQuery($request)
            ->when($request->filled('status'), fn ($query) => $query->where('stock_status', $request->string('status')))
            ->orderBy('stock_quantity')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('admin.inventory.stock-levels.index', compact('variants', 'products'));
    }

    public function exportStockLevels(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $variants = $this->baseVariantQuery($request)
            ->when($request->filled('status'), fn ($query) => $query->where('stock_status', $request->string('status')))
            ->orderBy('stock_quantity')
            ->lazy(200);

        $headers = ['id', 'product_name', 'variant_name', 'sku', 'current_stock', 'low_stock_limit', 'stock_status', 'last_updated'];

        $rows = $variants->map(function (ProductVariant $variant) {
            $isOutOfStock = $variant->stock_quantity == 0 || $variant->stock_status === 'out_of_stock';
            $isLowStock = ! $isOutOfStock && $variant->stock_quantity <= $variant->low_stock_quantity;
            $statusLabel = $isOutOfStock ? 'Out of Stock' : ($isLowStock ? 'Low Stock' : 'In Stock');

            return [
                $variant->id,
                $variant->product->name ?? null,
                $variant->variant_name,
                $variant->sku,
                $variant->stock_quantity,
                $variant->low_stock_quantity,
                $statusLabel,
                $variant->updated_at->format('d M Y'),
            ];
        });

        return $exporter->stream('stock-levels.csv', $headers, $rows);
    }

    public function lowStock(Request $request): View
    {
        $variants = $this->baseVariantQuery($request)
            ->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_quantity')
            ->where('stock_status', '!=', 'out_of_stock')
            ->orderBy('stock_quantity')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('admin.inventory.low-stock.index', compact('variants', 'products'));
    }

    public function outOfStock(Request $request): View
    {
        $variants = $this->baseVariantQuery($request)
            ->where(fn ($query) => $query->where('stock_quantity', 0)->orWhere('stock_status', 'out_of_stock'))
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('admin.inventory.out-of-stock.index', compact('variants', 'products'));
    }

    private function baseVariantQuery(Request $request): Builder
    {
        return ProductVariant::with(['product', 'primaryImage'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->where('variant_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')));
    }
}
