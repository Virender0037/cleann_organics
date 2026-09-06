<?php

namespace App\Providers;

use App\Listeners\MergeGuestCartOnLogin;
use App\Models\Category;
use App\Models\Setting;
use App\Services\Storefront\CartService;
use App\Services\Storefront\WishlistService;
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
        // Singleton, not the CartService default of resolve-fresh: product
        // cards call WishlistService::isWishlisted() once per card (shop
        // grid, related products), and its internal once()-memoized id
        // lookup only avoids N+1 if every call in the request shares the
        // same instance — a fresh instance per app() call would re-run the
        // query once per card.
        $this->app->singleton(WishlistService::class);
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

        // Header wishlist count (Phase H) — 0 for guests, one COUNT query
        // for an authenticated customer, shared via the singleton above so
        // this and any product-card on the same page hit the database once.
        View::composer(
            'components.layouts.header',
            function ($view) {
                $view->with('wishlistCount', app(WishlistService::class)->count());
            }
        );

        // Footer "Categories" column (storefront real-data integration) —
        // real active categories replacing the old hardcoded 4-item list,
        // same active()/ordered() rule the Shop sidebar already uses. One
        // bounded, indexed query per page load via this composer, same cost
        // class as the wishlist count above; not cached like generalSettings
        // since categories change far less often than they're read anyway
        // and this keeps the same pattern simple.
        View::composer(
            'components.layouts.footer',
            function ($view) {
                $view->with('footerCategories', Category::query()->active()->ordered()->limit(4)->get());
            }
        );
    }
}
