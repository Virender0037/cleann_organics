<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a published CMS page.
     *
     * A single query covers every requirement: matches the slug, restricts to
     * active pages, and — via the model's SoftDeletes global scope — excludes
     * trashed rows. firstOrFail() therefore returns 404 for a page that is
     * missing, inactive, or soft-deleted alike, without leaking which.
     */
    public function show(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return view('page', compact('page'));
    }

    /**
     * Display the Contact Us page. Unlike show(), this renders the existing
     * custom contact.blade.php design rather than the generic page view —
     * the Page row (slug: contact-us) supplies only the SEO metadata.
     */
    public function contact(): View
    {
        $page = Page::query()
            ->where('slug', 'contact-us')
            ->where('status', 'active')
            ->firstOrFail();

        return view('contact', compact('page'));
    }
}
