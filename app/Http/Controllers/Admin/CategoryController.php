<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')
            ->with('parent')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('admin.catalog.categories.index', compact('categories'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $categories = Category::with('parent')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->ordered()
            ->lazy(200);

        $headers = ['id', 'parent_slug', 'name', 'slug', 'description', 'status', 'sort_order', 'image', 'meta_title', 'meta_keywords', 'meta_description'];

        $rows = $categories->map(fn (Category $category) => [
            $category->id,
            $category->parent?->slug,
            $category->name,
            $category->slug,
            $category->description,
            $category->status,
            $category->sort_order,
            $category->image,
            $category->meta_title,
            $category->meta_keywords,
            $category->meta_description,
        ]);

        return $exporter->stream('categories.csv', $headers, $rows);
    }

    public function create(): View
    {
        $parentCategories = Category::ordered()->get();

        return view('admin.catalog.categories.create', compact('parentCategories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        $parentCategories = Category::whereNotIn('id', [$category->id, ...$category->descendantIds()])
            ->ordered()
            ->get();

        return view('admin.catalog.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has products or subcategories.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
