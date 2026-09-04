<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function import(Request $request, SpreadsheetImportReader $reader): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
        ]);

        try {
            $parsed = $reader->parse($request->file('file'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Unable to read the uploaded file: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Product variant import: failed to parse uploaded file.', ['exception' => $e]);

            return back()->with('error', 'Unable to read the uploaded file. Please check the file and try again.');
        }

        $header = $parsed['header'];
        $rows = $parsed['rows'];

        $missingColumns = array_diff(['sku', 'product_slug', 'status'], $header);

        if (! empty($missingColumns)) {
            return back()->with('error', 'Invalid file: missing required column(s): '.implode(', ', $missingColumns));
        }

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
            $data = $request->safe()->except(['new_media', 'media_order', 'primary_selector']);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $variant = ProductVariant::create($data);

            if ($variant->is_default) {
                $this->enforceSingleDefaultVariant($variant);
            }

            $this->syncVariantMedia($variant, $request);
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
            $data = $request->safe()->except(['new_media', 'media_order', 'primary_selector']);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $variant->update($data);

            if ($variant->is_default) {
                $this->enforceSingleDefaultVariant($variant);
            }

            $this->syncVariantMedia($variant, $request);
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

    public function destroyImage(Request $request, ProductVariant $variant, ProductVariantImage $image): RedirectResponse|JsonResponse
    {
        // The route only scopes {variant} in the URI, not in the query — a
        // valid variant + a valid image that belongs to someone else's
        // variant must still be rejected here.
        abort_unless($image->product_variant_id === $variant->id, 404);

        $wasPrimary = $image->is_primary;
        $promoted = null;

        DB::transaction(function () use ($variant, $image, $wasPrimary, &$promoted) {
            Storage::disk('public')->delete($image->image);
            $image->delete();

            // Re-normalize remaining sort_order to 1..N with no gaps.
            $remaining = $variant->images()->orderBy('sort_order')->get();

            $this->reassignSortOrders($remaining);

            if ($wasPrimary) {
                $promoted = $remaining->firstWhere('media_type', 'image');
                $promoted?->update(['is_primary' => true]);
            }
        });

        // expectsJson(), not wantsJson(): the JS sends X-Requested-With and an
        // Accept header, but wantsJson() only trusts the Accept header — if a
        // proxy/CDN in front of the app normalizes it, this endpoint would
        // silently fall through to the redirect below despite the deletion
        // having already succeeded, and fetch() would then choke trying to
        // parse the redirected-to HTML page as JSON.
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'promoted_primary_id' => $promoted?->id,
            ]);
        }

        return back()->with('success', 'Variant media deleted.');
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
     * Reassign sort_order 1..N (in the collection's current order) two-phase,
     * to avoid the unique(product_variant_id, sort_order) constraint
     * colliding with a row's own not-yet-updated value mid-loop.
     *
     * @param  Collection<int, ProductVariantImage>  $items
     */
    private function reassignSortOrders($items): void
    {
        // Phase 1: push every row into a range no final value can reach
        // (max real items per variant is max_images + max_videos, well
        // under 100), so no update in phase 2 can collide with phase 1's
        // still-unprocessed rows.
        foreach ($items as $offset => $item) {
            $item->update(['sort_order' => 100 + $offset]);
        }

        foreach ($items as $index => $item) {
            $item->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Apply the admin's unified image+video order — existing rows reordered,
     * new uploads created in place — from the `media_order` sequence
     * (tokens like "e12,n0,e15,n1"), and resolve which single image (never
     * a video) ends up primary.
     */
    private function syncVariantMedia(ProductVariant $variant, Request $request): void
    {
        $config = config('media.variant');
        $newFiles = $request->file('new_media', []);

        if (empty($newFiles) && ! $request->filled('media_order') && ! $request->filled('primary_selector')) {
            return;
        }

        $tokens = $this->parseMediaOrder($request->string('media_order')->toString(), $variant, $newFiles);

        // Defensive completion: if media_order omits an existing image (a
        // malformed/partial client request rather than the real JS, which
        // always includes every seeded item), append it at the end instead
        // of silently dropping it from the order or leaving its sort_order
        // to collide with a freshly assigned one.
        $mentionedIds = collect($tokens)->where('kind', 'existing')->pluck('id')->all();
        $missingTokens = $variant->images
            ->reject(fn ($image) => in_array($image->id, $mentionedIds, true))
            ->map(fn ($image) => ['kind' => 'existing', 'id' => $image->id])
            ->all();
        $tokens = array_merge($tokens, $missingTokens);

        // Phase 1: move every existing row out of the way of the final
        // 1..N range (see reassignSortOrders()) — all of them, not just
        // ones explicitly mentioned, so nothing can collide in phase 2.
        ProductVariantImage::where('product_variant_id', $variant->id)
            ->get()
            ->each(fn ($image, $offset) => $image->update(['sort_order' => 100 + $offset]));

        $selector = $request->string('primary_selector')->toString();
        $resolvedPrimary = null;

        foreach ($tokens as $position => $token) {
            $sortOrder = $position + 1;

            if ($token['kind'] === 'existing') {
                $image = ProductVariantImage::where('product_variant_id', $variant->id)->find($token['id']);

                if (! $image) {
                    continue;
                }

                $image->update(['sort_order' => $sortOrder]);

                if ($selector === "existing:{$image->id}") {
                    $resolvedPrimary = $image;
                }
            } else {
                $file = $newFiles[$token['index']] ?? null;

                if (! $file) {
                    continue;
                }

                $extension = strtolower((string) $file->getClientOriginalExtension());
                $mediaType = in_array($extension, $config['video_mimes'], true) ? 'video' : 'image';
                $folder = "products/{$variant->product_id}/variants/{$variant->id}/".($mediaType === 'video' ? 'videos' : 'images');

                $image = $variant->images()->create([
                    'image' => $file->store($folder, 'public'),
                    'media_type' => $mediaType,
                    'is_primary' => false,
                    'sort_order' => $sortOrder,
                ]);

                if ($selector === "new:{$token['index']}") {
                    $resolvedPrimary = $image;
                }
            }
        }

        // Only touch is_primary when the admin actually submitted a
        // selection this save — an empty selector means "leave primary
        // as it is", not "clear it". A blanket clear here would silently
        // reassign primary to the first image on every reorder-only or
        // add-only save that happens not to resend the current choice.
        if ($selector !== '') {
            $variant->images()->update(['is_primary' => false]);

            if ($resolvedPrimary && $resolvedPrimary->isImage()) {
                $resolvedPrimary->update(['is_primary' => true]);
            }
        }

        if (! $variant->images()->where('is_primary', true)->exists()) {
            // Nothing is primary yet (a fresh variant, or an explicit
            // selection above didn't resolve) — fall back to the first
            // image (never a video) in the final sort order.
            $variant->images()->where('media_type', 'image')
                ->orderBy('sort_order')
                ->first()
                ?->update(['is_primary' => true]);
        }
    }

    /**
     * Parse the "e12,n0,e15,n1" media_order string into ordered tokens.
     * Falls back to existing-by-sort_order followed by new-by-submission-
     * order if the field is missing (JS didn't run, or a very old client).
     *
     * @param  array<int, UploadedFile>  $newFiles
     * @return array<int, array{kind: string, id?: int, index?: int}>
     */
    private function parseMediaOrder(string $mediaOrder, ProductVariant $variant, array $newFiles): array
    {
        if ($mediaOrder === '') {
            $tokens = $variant->images()->orderBy('sort_order')->pluck('id')
                ->map(fn ($id) => ['kind' => 'existing', 'id' => $id])
                ->all();

            foreach (array_keys($newFiles) as $index) {
                $tokens[] = ['kind' => 'new', 'index' => $index];
            }

            return $tokens;
        }

        $tokens = [];

        foreach (explode(',', $mediaOrder) as $token) {
            $token = trim($token);

            if ($token === '') {
                continue;
            }

            if ($token[0] === 'e' && ctype_digit(substr($token, 1))) {
                $tokens[] = ['kind' => 'existing', 'id' => (int) substr($token, 1)];
            } elseif ($token[0] === 'n' && ctype_digit(substr($token, 1))) {
                $tokens[] = ['kind' => 'new', 'index' => (int) substr($token, 1)];
            }
        }

        return $tokens;
    }
}
