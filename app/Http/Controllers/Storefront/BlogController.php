<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The public blog — real Blog rows only (Blog::published(), see the model),
 * matching the same "storefront never sees draft/inactive rows" rule
 * Product/Category already apply. Sidebar widgets (categories, tags, recent
 * posts) are intentionally derived from the same published set rather than
 * counting drafts, so a number shown to a visitor is never higher than what
 * they can actually click through to.
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $blogs = Blog::query()
            ->published()
            ->with(['category', 'author'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($q) => $q->where('slug', $request->string('tag')));
            })
            ->latest('published_at')
            ->paginate(6)
            ->withQueryString();

        return view('bloglist', [
            'blogs' => $blogs,
            'categories' => $this->sidebarCategories(),
            'tags' => $this->sidebarTags(),
            'recentBlogs' => $this->recentBlogs(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(string $slug): View
    {
        $blog = Blog::query()
            ->published()
            ->with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        $blog->increment('view_count');

        $relatedBlogs = Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->where('blog_category_id', $blog->blog_category_id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('singleblog', [
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'categories' => $this->sidebarCategories(),
            'tags' => $this->sidebarTags(),
            'recentBlogs' => $this->recentBlogs(),
            'metaTitle' => $blog->meta_title ?: $blog->title,
            'metaDescription' => $blog->meta_description ?: $blog->short_description,
            'canonicalUrl' => $blog->canonical_url ?: route('singleblog', $blog->slug),
            'ogImage' => $blog->featured_image,
        ]);
    }

    /** @return Collection<int, BlogCategory> */
    private function sidebarCategories(): Collection
    {
        // Filtered in PHP rather than SQL HAVING — SQLite (used in tests)
        // rejects a HAVING clause on a withCount() alias, and the sidebar
        // category list is small enough that this costs nothing extra.
        return BlogCategory::query()
            ->active()
            ->withCount(['blogs' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get()
            ->filter(fn (BlogCategory $category) => $category->blogs_count > 0)
            ->values();
    }

    /** @return Collection<int, BlogTag> */
    private function sidebarTags(): Collection
    {
        return BlogTag::query()
            ->active()
            ->whereHas('blogs', fn ($q) => $q->published())
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    /** @return Collection<int, Blog> */
    private function recentBlogs(): Collection
    {
        return Blog::query()
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();
    }
}
