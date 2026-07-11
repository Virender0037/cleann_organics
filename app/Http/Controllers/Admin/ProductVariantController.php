<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductVariantController extends Controller
{
    public function index(Request $request): View
    {
        $variants = ProductVariant::with(['product', 'primaryImage'])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('variant_name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('sku', 'like', '%'.$request->string('search').'%');
            }))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('stock_status'), fn ($query) => $query->where('stock_status', $request->string('stock_status')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('admin.catalog.variants.index', compact('variants', 'products'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $variants = ProductVariant::with('product')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('variant_name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('sku', 'like', '%'.$request->string('search').'%');
            }))
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('stock_status'), fn ($query) => $query->where('stock_status', $request->string('stock_status')))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lazy(200);

        $headers = ['id', 'product_name', 'variant_name', 'is_default', 'sku', 'unit', 'stock_quantity', 'price', 'status'];

        $rows = $variants->map(fn (ProductVariant $variant) => [
            $variant->id,
            $variant->product->name ?? null,
            $variant->variant_name,
            (int) $variant->is_default,
            $variant->sku,
            $variant->unit,
            $variant->stock_quantity,
            $variant->single_price,
            $variant->status,
        ]);

        return $exporter->stream('product-variants.csv', $headers, $rows);
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        return view('admin.catalog.variants.create', compact('products'));
    }

    public function store(StoreProductVariantRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->safe()->except('images');
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $variant = ProductVariant::create($data);

            if ($variant->is_default) {
                $this->enforceSingleDefaultVariant($variant);
            }

            $this->storeVariantImages($variant, $request->file('images', []));
        });

        return redirect()->route('admin.catalog.variants.index')->with('success', 'Variant created.');
    }

    public function edit(ProductVariant $variant): View
    {
        $variant->load('images');
        $products = Product::orderBy('name')->get();

        return view('admin.catalog.variants.edit', compact('variant', 'products'));
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant): RedirectResponse
    {
        DB::transaction(function () use ($request, $variant) {
            $data = $request->safe()->except(['images', 'primary_image']);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $variant->update($data);

            if ($variant->is_default) {
                $this->enforceSingleDefaultVariant($variant);
            }

            $this->storeVariantImages($variant, $request->file('images', []));

            if ($request->filled('primary_image')) {
                $variant->images()->update(['is_primary' => false]);
                $variant->images()->where('id', $request->integer('primary_image'))->update(['is_primary' => true]);
            }
        });

        return redirect()->route('admin.catalog.variants.index')->with('success', 'Variant updated.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        if ($variant->cartItems()->exists() || $variant->orderItems()->exists()) {
            return back()->with('error', 'Cannot delete a variant that is referenced by a cart or an order.');
        }

        $variant->delete();

        return back()->with('success', 'Variant deleted.');
    }

    public function destroyImage(ProductVariant $variant, ProductVariantImage $image): RedirectResponse
    {
        $wasPrimary = $image->is_primary;

        Storage::disk('public')->delete($image->image);
        $image->delete();

        if ($wasPrimary) {
            $nextImage = $variant->images()->orderBy('sort_order')->first();
            $nextImage?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Variant image deleted.');
    }

    /**
     * Ensure only the given variant is marked as the default for its product.
     */
    private function enforceSingleDefaultVariant(ProductVariant $variant): void
    {
        ProductVariant::where('product_id', $variant->product_id)
            ->where('id', '!=', $variant->id)
            ->update(['is_default' => false]);
    }

    /**
     * Store uploaded images for a variant, respecting the unique(product_variant_id, sort_order) constraint.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function storeVariantImages(ProductVariant $variant, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $hadNoImages = ! $variant->images()->exists();
        $nextSortOrder = ($variant->images()->max('sort_order') ?? -1) + 1;

        foreach ($files as $index => $file) {
            $variant->images()->create([
                'image' => $file->store('variants', 'public'),
                'is_primary' => $hadNoImages && $index === 0,
                'sort_order' => $nextSortOrder + $index,
            ]);
        }
    }
}
