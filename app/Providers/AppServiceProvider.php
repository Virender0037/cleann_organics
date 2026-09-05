<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination::bootstrap-5');

        // Shared once per request (not per include) across the storefront's
        // header/footer, which both render on every page — cached across
        // requests via Setting::cached(), invalidated on admin save via
        // Setting::forget() in SettingController::updateGeneral().
        View::composer(
            ['components.layouts.header', 'components.layouts.footer'],
            function ($view) {
                $view->with('generalSettings', Setting::cached('general'));
            }
        );

        // SEO settings fallback (og:image, meta description) used by every
        // storefront page's <head> — same caching approach as above,
        // invalidated on admin save via Setting::forget() in
        // SettingController::updateSeo().
        View::composer(
            'components.layouts.header',
            function ($view) {
                $view->with('seoSettings', Setting::cached('seo'));
            }
        );
    }
}
