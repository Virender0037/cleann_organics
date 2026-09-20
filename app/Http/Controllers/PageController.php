<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
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

        // Address / email / phone come from Admin → Settings → General
        // (company_*), never hardcoded in the view.
        return view('contact', [
            'page' => $page,
            'company' => Setting::cached('general'),
        ]);
    }

    /**
     * Display the About Us page. Unlike show(), this renders the existing
     * custom aboutus.blade.php design rather than the generic page view —
     * the Page row (slug: about-us) supplies only the SEO metadata.
     */
    public function aboutUs(): View
    {
        $page = Page::query()
            ->where('slug', 'about-us')
            ->where('status', 'active')
            ->firstOrFail();

        $settings = app(\App\Services\Storefront\StorefrontSettings::class);

        return view('aboutus', [
            'page' => $page,
            // Real, admin-managed content only (Admin → CMS): the old template
            // stock team and feature cards are gone; empty sections are hidden.
            'teamMembers' => \App\Models\TeamMember::query()->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get(),
            'benefits' => \App\Models\HomeBanner::query()->section(\App\Models\HomeBanner::SECTION_BENEFIT)->active()->ordered()->get(),
            'freeShippingLabel' => $settings->formatMoney($settings->freeShippingThreshold()),
        ]);
    }
}
