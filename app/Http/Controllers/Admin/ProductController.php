<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\TaxRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
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
