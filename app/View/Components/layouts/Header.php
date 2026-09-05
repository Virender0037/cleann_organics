<?php

namespace App\View\Components\layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Header extends Component
{
    /**
     * Optional per-page SEO overrides. Every storefront page that passes
     * nothing keeps the previous behaviour: the default title and no
     * description/canonical tags.
     */
    public function __construct(
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?string $canonicalUrl = null,
        public ?string $ogImage = null,
        // Phase J: emit <meta name="robots" content="noindex, nofollow">
        // for authenticated account/order pages. A class-based component
        // only exposes constructor parameters to its view, so this must be
        // declared here (same reason $ogImage had to be — Phase F QA).
        public bool $noindex = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.header');
    }
}
