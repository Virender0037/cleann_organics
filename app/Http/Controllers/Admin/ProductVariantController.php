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

    public function importForm(): View
    {
        return view('admin.catalog.variants.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['product_slug', 'variant_name', 'sku', 'barcode', 'unit', 'size', 'weight', 'color', 'pack_quantity', 'enable_tiered_pricing', 'single_quantity', 'single_price', 'standard_quantity', 'standard_price', 'discount_quantity', 'discount_price', 'stock_quantity', 'low_stock_quantity', 'stock_status', 'is_default', 'sort_order', 'status'];

        $exampleRows = [
            ['organic-honey', '500g Jar', 'HON-500', '', 'gram', '500g', '', '', '0', '1', '100.00', '', '', '', '', '20', '5', 'in_stock', '1', '1', 'active'],
            ['organic-honey', '1kg Jar', 'HON-1000', '', 'gram', '1kg', '', '', '1', '', '', '5', '180.00', '10', '160.00', '15', '5', 'in_stock', '0', '2', 'active'],
        ];

        return $exporter->stream('product-variants-import-template.csv', $headers, $exampleRows);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map('trim', fgetcsv($handle));

        $missingColumns = array_diff(['sku', 'product_slug', 'status'], $header);

        if (! empty($missingColumns)) {
            fclose($handle);

            return back()->with('error', 'Invalid CSV: missing required column(s): '.implode(', ', $missingColumns));
        }

        $rows = [];
        $rowNumber = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($line) === 1 && trim((string) $line[0]) === '') {
                continue;
            }

            $data = array_combine($header, array_pad($line, count($header), null));
            $data = array_map(fn ($value) => blank($value) ? null : trim((string) $value), $data);

            $rows[] = ['row' => $rowNumber, 'data' => $data];
        }

        fclose($handle);

        $success = 0;
        $skipped = [];
        $errors = [];
        $claimedSkus = [];
        $claimedBarcodes = [];

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $sku = $data['sku'] ?? null;

            if (blank($sku)) {
                $errors[] = ['row' => $rowNum, 'sku' => null, 'reason' => 'sku_required'];

                continue;
            }

            if (isset($claimedSkus[$sku])) {
                $skipped[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'duplicate_sku_in_file'];

                continue;
            }

            if (ProductVariant::where('sku', $sku)->exists()) {
                $skipped[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'duplicate_sku'];

                continue;
            }

            $productSlug = $data['product_slug'] ?? null;

            if (blank($productSlug)) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'Missing required field: product_slug'];

                continue;
            }

            $product = Product::where('slug', $productSlug)->first();

            if (! $product) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "product_slug '{$productSlug}' not found"];

                continue;
            }

            $status = $data['status'] ?? null;

            if (! in_array($status, ['active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "Invalid status '{$status}' (must be active or inactive)"];

                continue;
            }

            $stockStatus = $data['stock_status'] ?? null;

            if (! in_array($stockStatus, ['in_stock', 'out_of_stock'], true)) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "Invalid stock_status '{$stockStatus}' (must be in_stock or out_of_stock)"];

                continue;
            }

            $enableTieredPricing = $this->parseCsvBoolean($data['enable_tiered_pricing'] ?? null);
            $isDefault = $this->parseCsvBoolean($data['is_default'] ?? null);

            if ($enableTieredPricing === null) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'Missing or invalid required field: enable_tiered_pricing (must be 1/0, true/false, or yes/no)'];

                continue;
            }

            if ($isDefault === null) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'Missing or invalid required field: is_default (must be 1/0, true/false, or yes/no)'];

                continue;
            }

            $unit = $data['unit'] ?? null;

            if (! blank($unit) && ! in_array($unit, ['kg', 'gram', 'litre', 'piece', 'pack'], true)) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "Invalid unit '{$unit}' (must be kg, gram, litre, piece, or pack)"];

                continue;
            }

            $barcode = $data['barcode'] ?? null;

            if (! blank($barcode) && (isset($claimedBarcodes[$barcode]) || ProductVariant::where('barcode', $barcode)->exists())) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "Duplicate barcode '{$barcode}'"];

                continue;
            }

            $numericFields = ['weight', 'single_quantity', 'single_price', 'standard_quantity', 'standard_price', 'discount_quantity', 'discount_price', 'stock_quantity', 'low_stock_quantity', 'sort_order'];
            $invalidNumeric = null;

            foreach ($numericFields as $field) {
                $value = $data[$field] ?? null;

                if (blank($value)) {
                    continue;
                }

                if (! is_numeric($value) || (float) $value < 0) {
                    $invalidNumeric = $field;

                    break;
                }
            }

            if ($invalidNumeric !== null) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => "Invalid {$invalidNumeric} '{$data[$invalidNumeric]}' (must be a non-negative number)"];

                continue;
            }

            if ($enableTieredPricing) {
                $tieredRequired = ['standard_quantity', 'standard_price', 'discount_quantity', 'discount_price'];
                $missingTiered = array_filter($tieredRequired, fn ($field) => blank($data[$field] ?? null));

                if (! empty($missingTiered)) {
                    $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'Missing required field(s) for tiered pricing: '.implode(', ', $missingTiered)];

                    continue;
                }
            }

            try {
                DB::transaction(function () use ($product, $sku, $data, $status, $stockStatus, $enableTieredPricing, $isDefault, $unit, $barcode) {
                    $variantData = [
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'variant_name' => $data['variant_name'] ?? null,
                        'barcode' => $barcode,
                        'unit' => $unit,
                        'size' => $data['size'] ?? null,
                        'weight' => $data['weight'] ?? null,
                        'color' => $data['color'] ?? null,
                        'pack_quantity' => $data['pack_quantity'] ?? null,
                        'enable_tiered_pricing' => $enableTieredPricing,
                        'single_quantity' => $data['single_quantity'] ?? null,
                        'single_price' => $data['single_price'] ?? null,
                        'standard_quantity' => $data['standard_quantity'] ?? null,
                        'standard_price' => $data['standard_price'] ?? null,
                        'discount_quantity' => $data['discount_quantity'] ?? null,
                        'discount_price' => $data['discount_price'] ?? null,
                        'stock_status' => $stockStatus,
                        'is_default' => $isDefault,
                        'status' => $status,
                    ];

                    if (! blank($data['stock_quantity'] ?? null)) {
                        $variantData['stock_quantity'] = (int) $data['stock_quantity'];
                    }

                    if (! blank($data['low_stock_quantity'] ?? null)) {
                        $variantData['low_stock_quantity'] = (int) $data['low_stock_quantity'];
                    }

                    if (! blank($data['sort_order'] ?? null)) {
                        $variantData['sort_order'] = (int) $data['sort_order'];
                    }

                    $variant = ProductVariant::create($variantData);

                    if ($variant->is_default) {
                        $this->enforceSingleDefaultVariant($variant);
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'sku' => $sku, 'reason' => 'Failed to create variant: '.$e->getMessage()];

                continue;
            }

            $claimedSkus[$sku] = true;

            if (! blank($barcode)) {
                $claimedBarcodes[$barcode] = true;
            }

            $success++;
        }

        return redirect()->route('admin.catalog.variants.import')
            ->with('import_results', [
                'success' => $success,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
    }

    private function parseCsvBoolean(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower(trim($value))) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
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
