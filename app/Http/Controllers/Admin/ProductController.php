<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\TaxRate;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->with(['variants' => function ($query) {
                $query->where('status', 'active')
                    ->orderByDesc('is_default')
                    ->orderBy('sort_order')
                    ->with(['images' => function ($query) {
                        $query->where('media_type', 'image')
                            ->orderByDesc('is_primary')
                            ->orderBy('sort_order');
                    }]);
            }])
            ->withCount('variants')
            ->withSum('variants', 'stock_quantity')
            ->withMin('variants', 'single_price')
            ->withMax('variants', 'single_price')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.catalog.products.index', compact('products'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $products = Product::with('category')
            ->withCount('variants')
            ->withSum('variants', 'stock_quantity')
            ->withMin('variants', 'single_price')
            ->withMax('variants', 'single_price')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->lazy(200);

        $headers = ['id', 'name', 'slug', 'category_name', 'variants_count', 'status', 'is_featured', 'stock', 'price_range'];

        $rows = $products->map(function (Product $product) {
            if ($product->variants_count === 0) {
                $stock = null;
            } else {
                $stock = $product->variants_sum_stock_quantity > 0 ? 'In Stock' : 'Out of Stock';
            }

            if ($product->variants_min_single_price === null) {
                $priceRange = null;
            } elseif ($product->variants_min_single_price == $product->variants_max_single_price) {
                $priceRange = (string) $product->variants_min_single_price;
            } else {
                $priceRange = $product->variants_min_single_price.' - '.$product->variants_max_single_price;
            }

            return [
                $product->id,
                $product->name,
                $product->slug,
                $product->category->name ?? null,
                $product->variants_count,
                $product->status,
                (int) $product->is_featured,
                $stock,
                $priceRange,
            ];
        });

        return $exporter->stream('products.csv', $headers, $rows);
    }

    public function importForm(): View
    {
        return view('admin.catalog.products.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['name', 'slug', 'category_slug', 'tax_rate_name', 'brand', 'short_description', 'description', 'status', 'is_returnable', 'return_days', 'is_featured', 'is_latest', 'is_best_seller', 'sort_order', 'meta_title', 'meta_keywords', 'meta_description', 'tags'];

        $exampleRows = [
            ['Organic Honey', 'organic-honey', 'organic-foods', 'GST 5%', 'Nature Farms', 'Pure organic honey', '100% raw organic honey', 'active', '1', '7', '1', '0', '1', '1', 'Organic Honey', 'honey, organic', 'Buy organic honey online', 'Organic, Bestseller'],
            ['Cold Pressed Oil', 'cold-pressed-oil', 'organic-oils', '', 'Nature Farms', 'Cold pressed cooking oil', '', 'draft', '0', '', '0', '0', '0', '2', '', '', '', ''],
        ];

        return $exporter->stream('products-import-template.csv', $headers, $exampleRows);
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
            Log::error('Product import: failed to parse uploaded file.', ['exception' => $e]);

            return back()->with('error', 'Unable to read the uploaded file. Please check the file and try again.');
        }

        $header = $parsed['header'];
        $rows = $parsed['rows'];

        $missingColumns = array_diff(['name', 'status'], $header);

        if (! empty($missingColumns)) {
            return back()->with('error', 'Invalid file: missing required column(s): '.implode(', ', $missingColumns));
        }

        $success = 0;
        $skipped = [];
        $errors = [];
        $claimedSlugs = [];

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $name = $data['name'] ?? null;
            $status = $data['status'] ?? null;

            if (blank($name)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: name'];

                continue;
            }

            if (! in_array($status, ['draft', 'active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid status '{$status}' (must be draft, active, or inactive)"];

                continue;
            }

            $slug = blank($data['slug'] ?? null) ? Str::slug($name) : Str::slug($data['slug']);

            if (blank($slug)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Could not generate a valid slug from name'];

                continue;
            }

            if (isset($claimedSlugs[$slug]) || Product::where('slug', $slug)->exists()) {
                $skipped[] = ['row' => $rowNum, 'slug' => $slug, 'reason' => 'duplicate_slug'];

                continue;
            }

            $categorySlug = $data['category_slug'] ?? null;

            if (blank($categorySlug)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: category_slug'];

                continue;
            }

            $category = Category::where('slug', $categorySlug)->where('status', 'active')->first();

            if (! $category) {
                $errors[] = ['row' => $rowNum, 'message' => "category_slug '{$categorySlug}' not found or inactive"];

                continue;
            }

            $taxRateId = null;
            $taxRateName = $data['tax_rate_name'] ?? null;

            if (! blank($taxRateName)) {
                $taxRate = TaxRate::where('name', $taxRateName)->where('status', 'active')->first();

                if (! $taxRate) {
                    $errors[] = ['row' => $rowNum, 'message' => "tax_rate_name '{$taxRateName}' not found or inactive"];

                    continue;
                }

                $taxRateId = $taxRate->id;
            }

            $isReturnable = $this->parseCsvBoolean($data['is_returnable'] ?? null);
            $isFeatured = $this->parseCsvBoolean($data['is_featured'] ?? null);
            $isLatest = $this->parseCsvBoolean($data['is_latest'] ?? null);
            $isBestSeller = $this->parseCsvBoolean($data['is_best_seller'] ?? null);

            $booleanFields = ['is_returnable' => $isReturnable, 'is_featured' => $isFeatured, 'is_latest' => $isLatest, 'is_best_seller' => $isBestSeller];
            $invalidBoolean = null;

            foreach ($booleanFields as $field => $value) {
                if ($value === null) {
                    $invalidBoolean = $field;

                    break;
                }
            }

            if ($invalidBoolean !== null) {
                $errors[] = ['row' => $rowNum, 'message' => "Missing or invalid required field: {$invalidBoolean} (must be 1/0, true/false, or yes/no)"];

                continue;
            }

            $returnDays = $data['return_days'] ?? null;

            if (! blank($returnDays) && (! ctype_digit((string) $returnDays) || (int) $returnDays > 255)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid return_days '{$returnDays}' (must be an integer between 0 and 255)"];

                continue;
            }

            $sortOrder = $data['sort_order'] ?? null;

            if (! blank($sortOrder) && ! ctype_digit((string) $sortOrder)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid sort_order '{$sortOrder}' (must be a non-negative integer)"];

                continue;
            }

            $tagIds = [];
            $missingTags = [];

            if (! blank($data['tags'] ?? null)) {
                $tagNames = array_filter(array_map('trim', explode(',', $data['tags'])));

                foreach ($tagNames as $tagName) {
                    $tag = Tag::where('name', $tagName)->where('status', 'active')->first();

                    if ($tag) {
                        $tagIds[] = $tag->id;
                    } else {
                        $missingTags[] = $tagName;
                    }
                }
            }

            if (! empty($missingTags)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Unknown tag(s): '.implode(', ', $missingTags)];

                continue;
            }

            try {
                DB::transaction(function () use ($name, $slug, $category, $taxRateId, $data, $status, $isReturnable, $returnDays, $isFeatured, $isLatest, $isBestSeller, $sortOrder, $tagIds) {
                    $productData = [
                        'category_id' => $category->id,
                        'tax_rate_id' => $taxRateId,
                        'name' => $name,
                        'slug' => $slug,
                        'brand' => $data['brand'] ?? null,
                        'short_description' => $data['short_description'] ?? null,
                        'description' => $data['description'] ?? null,
                        'status' => $status,
                        'is_returnable' => $isReturnable,
                        'is_featured' => $isFeatured,
                        'is_latest' => $isLatest,
                        'is_best_seller' => $isBestSeller,
                        'sort_order' => blank($sortOrder) ? 0 : (int) $sortOrder,
                        'meta_title' => $data['meta_title'] ?? null,
                        'meta_keywords' => $data['meta_keywords'] ?? null,
                        'meta_description' => $data['meta_description'] ?? null,
                    ];

                    if (! blank($returnDays)) {
                        $productData['return_days'] = (int) $returnDays;
                    }

                    $product = Product::create($productData);

                    if (! empty($tagIds)) {
                        $product->tags()->sync($tagIds);
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'message' => 'Failed to create product: '.$e->getMessage()];

                continue;
            }

            $claimedSlugs[$slug] = true;
            $success++;
        }

        return redirect()->route('admin.catalog.products.import')
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
        $categories = Category::ordered()->get();
        $taxRates = TaxRate::all();
        $tags = Tag::orderBy('name')->get();

        return view('admin.catalog.products.create', compact('categories', 'taxRates', 'tags'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->safe()->except(['specifications', 'tags']);
            $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $product = Product::create($data);

            $this->syncSpecifications($product, $request->input('specifications', []));
            $product->tags()->sync($request->input('tags', []));
        });

        return redirect()->route('admin.catalog.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['specifications' => fn ($query) => $query->orderBy('sort_order'), 'tags']);
        $categories = Category::ordered()->get();
        $taxRates = TaxRate::all();
        $tags = Tag::orderBy('name')->get();

        return view('admin.catalog.products.edit', compact('product', 'categories', 'taxRates', 'tags'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $data = $request->safe()->except(['specifications', 'tags']);
            $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $product->update($data);

            $this->syncSpecifications($product, $request->input('specifications', []));
            $product->tags()->sync($request->input('tags', []));
        });

        return redirect()->route('admin.catalog.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->variants()->exists()) {
            return back()->with('error', 'Cannot delete a product that has variants.');
        }

        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    /**
     * Replace a product's specification rows with the submitted set.
     *
     * @param  array<int, array{title?: string, value?: string|null}>  $specifications
     */
    private function syncSpecifications(Product $product, array $specifications): void
    {
        $product->specifications()->delete();

        foreach (array_values($specifications) as $index => $spec) {
            if (blank($spec['title'] ?? null)) {
                continue;
            }

            $product->specifications()->create([
                'title' => $spec['title'],
                'value' => $spec['value'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
