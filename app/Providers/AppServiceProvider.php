<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Setting;
use App\Services\Storefront\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
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

        // Mini-cart data for the header, shared on every page (the header
        // renders everywhere). CartService is resolved fresh per composer
        // call — cheap for an empty cart (session-only read for guests, a
        // single query for authenticated users) — and never cached
        // globally, since it's inherently per-visitor.
        View::composer(
            'components.layouts.header',
            function ($view) {
                $cart = app(CartService::class);
                $view->with('cartLines', $cart->lines());
                $view->with('cartSubtotal', $cart->subtotal());
                $view->with('cartItemCount', $cart->itemCount());
            }
        );

        // Guest -> authenticated cart merge. Fires for both login and
        // registration (RegisteredUserController also calls Auth::login()),
        // so this single listener covers Phase G section 5 without any
        // change to Breeze's own controllers.
        Event::listen(Login::class, MergeGuestCartOnLogin::class);
    }
}
