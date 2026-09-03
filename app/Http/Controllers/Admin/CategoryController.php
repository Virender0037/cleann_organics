<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $categories = Category::withCount('products')
            ->with('parent')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->ordered()
            ->lazy(200);

        $headers = ['id', 'name', 'slug', 'parent_slug', 'sort_order', 'status', 'products_count'];

        $rows = $categories->map(fn (Category $category) => [
            $category->id,
            $category->name,
            $category->slug,
            $category->parent?->slug,
            $category->sort_order,
            $category->status,
            $category->products_count,
        ]);

        return $exporter->stream('categories.csv', $headers, $rows);
    }

    public function importForm(): View
    {
        return view('admin.catalog.categories.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['name', 'slug', 'parent_slug', 'description', 'status', 'sort_order', 'meta_title', 'meta_keywords', 'meta_description'];

        $exampleRows = [
            ['Organic Foods', 'organic-foods', '', 'Certified organic food products', 'active', '1', 'Organic Foods', 'organic, foods', 'Shop organic foods'],
            ['Organic Oils', 'organic-oils', 'organic-foods', 'Cold-pressed and natural oils', 'active', '1', '', '', ''],
        ];

        return $exporter->stream('categories-import-template.csv', $headers, $exampleRows);
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
            Log::error('Category import: failed to parse uploaded file.', ['exception' => $e]);

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
        $pending = [];

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $name = $data['name'] ?? null;
            $status = $data['status'] ?? null;

            if (blank($name)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: name'];

                continue;
            }

            if (! in_array($status, ['active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid status '{$status}' (must be active or inactive)"];

                continue;
            }

            $slug = blank($data['slug'] ?? null) ? Str::slug($name) : Str::slug($data['slug']);

            if (blank($slug)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Could not generate a valid slug from name'];

                continue;
            }

            if (isset($claimedSlugs[$slug]) || Category::where('slug', $slug)->exists()) {
                $skipped[] = ['row' => $rowNum, 'slug' => $slug, 'message' => 'Duplicate slug'];

                continue;
            }

            $parentSlug = blank($data['parent_slug'] ?? null) ? null : Str::slug($data['parent_slug']);

            if ($parentSlug !== null && $parentSlug === $slug) {
                $errors[] = ['row' => $rowNum, 'message' => "Category '{$slug}' cannot be its own parent"];

                continue;
            }

            $sortOrder = $data['sort_order'] ?? null;

            if (! blank($sortOrder) && ! ctype_digit((string) $sortOrder)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid sort_order '{$sortOrder}' (must be a non-negative integer)"];

                continue;
            }

            $claimedSlugs[$slug] = true;
            $pending[] = [
                'row' => $rowNum,
                'slug' => $slug,
                'parent_slug' => $parentSlug,
                'name' => $name,
                'status' => $status,
                'description' => $data['description'] ?? null,
                'sort_order' => blank($sortOrder) ? 0 : (int) $sortOrder,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ];
        }

        $createdSlugIds = [];
        $madeProgress = true;

        while ($madeProgress && ! empty($pending)) {
            $madeProgress = false;
            $stillPending = [];

            foreach ($pending as $item) {
                $parentId = null;

                if ($item['parent_slug'] !== null) {
                    if (isset($createdSlugIds[$item['parent_slug']])) {
                        $parentId = $createdSlugIds[$item['parent_slug']];
                    } else {
                        $parent = Category::where('slug', $item['parent_slug'])->where('status', 'active')->first();

                        if ($parent) {
                            $parentId = $parent->id;
                        } else {
                            $stillPending[] = $item;

                            continue;
                        }
                    }
                }

                $category = Category::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'parent_id' => $parentId,
                    'description' => $item['description'],
                    'status' => $item['status'],
                    'sort_order' => $item['sort_order'],
                    'meta_title' => $item['meta_title'],
                    'meta_keywords' => $item['meta_keywords'],
                    'meta_description' => $item['meta_description'],
                ]);

                $createdSlugIds[$item['slug']] = $category->id;
                $success++;
                $madeProgress = true;
            }

            $pending = $stillPending;
        }

        foreach ($pending as $item) {
            $errors[] = [
                'row' => $item['row'],
                'message' => "parent_slug '{$item['parent_slug']}' could not be resolved (not found among active categories or elsewhere in this file)",
            ];
        }

        return redirect()->route('admin.catalog.categories.import')
            ->with('import_results', [
                'success' => $success,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
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
