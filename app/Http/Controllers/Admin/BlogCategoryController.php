<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogCategoryRequest;
use App\Http\Requests\Admin\UpdateBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $blogCategories = BlogCategory::withCount('blogs')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cms.blog-categories.index', compact('blogCategories'));
    }

    public function create(): View
    {
        return view('admin.cms.blog-categories.create');
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        BlogCategory::create($data);

        return redirect()->route('admin.cms.blog-categories.index')->with('success', 'Blog category created.');
    }

    public function edit(BlogCategory $blogCategory): View
    {
        return view('admin.cms.blog-categories.edit', compact('blogCategory'));
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $blogCategory->update($data);

        return redirect()->route('admin.cms.blog-categories.index')->with('success', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        if ($blogCategory->blogs()->exists()) {
            return back()->with('error', 'Cannot delete a category that has blogs.');
        }

        $blogCategory->delete();

        return back()->with('success', 'Blog category deleted.');
    }
}
