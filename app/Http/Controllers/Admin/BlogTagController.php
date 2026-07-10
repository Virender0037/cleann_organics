<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogTagRequest;
use App\Http\Requests\Admin\UpdateBlogTagRequest;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogTagController extends Controller
{
    public function index(Request $request): View
    {
        $blogTags = BlogTag::withCount('blogs')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cms.blog-tags.index', compact('blogTags'));
    }

    public function create(): View
    {
        return view('admin.cms.blog-tags.create');
    }

    public function store(StoreBlogTagRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        BlogTag::create($data);

        return redirect()->route('admin.cms.blog-tags.index')->with('success', 'Blog tag created.');
    }

    public function edit(BlogTag $blogTag): View
    {
        return view('admin.cms.blog-tags.edit', compact('blogTag'));
    }

    public function update(UpdateBlogTagRequest $request, BlogTag $blogTag): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        $blogTag->update($data);

        return redirect()->route('admin.cms.blog-tags.index')->with('success', 'Blog tag updated.');
    }

    public function destroy(BlogTag $blogTag): RedirectResponse
    {
        $blogTag->delete();

        return back()->with('success', 'Blog tag deleted.');
    }
}
