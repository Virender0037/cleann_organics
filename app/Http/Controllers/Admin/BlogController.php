<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $blogs = Blog::with('category')
            ->when($request->filled('search'), fn ($query) => $query->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->string('search').'%')
                    ->orWhere('slug', 'like', '%'.$request->string('search').'%');
            }))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('blog_category_id'), fn ($query) => $query->where('blog_category_id', $request->integer('blog_category_id')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.cms.blogs.index', compact('blogs', 'categories'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('admin.cms.blogs.create', compact('categories', 'tags'));
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('tags');
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['user_id'] = $request->user()->id;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog = Blog::create($data);
        $blog->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.cms.blogs.index')->with('success', 'Blog created.');
    }

    public function edit(Blog $blog): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        $blog->load('tags');

        return view('admin.cms.blogs.edit', compact('blog', 'categories', 'tags'));
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        $data = $request->safe()->except('tags');
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')->store('blogs', 'public');
        }

        $blog->update($data);
        $blog->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.cms.blogs.index')->with('success', 'Blog updated.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return back()->with('success', 'Blog deleted.');
    }
}
