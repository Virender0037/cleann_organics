<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerAddressController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerWishlistController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\SalesPaymentController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FaqPageController;
use App\Http\Controllers\PageController as PublicPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController as StorefrontCategoryController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use App\Http\Controllers\Storefront\ShopController;
use Illuminate\Support\Facades\Route;

// Breeze's scaffold dashboard is not the storefront's customer dashboard: the
// Shopery page at /user-dashboard is. This route is kept (rather than removed)
// because route('dashboard') is Breeze's conventional post-auth target and is
// still referenced by EmailVerificationPromptController. Middleware is
// unchanged, so guests continue to be sent to /sign-in.
Route::get('/dashboard', function () {
    return redirect()->route('user-dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return view('home');
})->name('home');

// Canonical Contact Us URL. The old /contact path is kept working via a
// permanent redirect below rather than left as a second live 200 page, since
// it may already be bookmarked/linked externally.
//
// Uses redirect()->route() rather than Route::permanentRedirect() because the
// latter builds a root-relative Location header, which breaks when the app
// isn't served from the domain root (e.g. this env's APP_URL points at
// /cleann_organics/public) — redirect()->route() builds the destination via
// the url() helper instead, so it resolves correctly under any base path.
Route::get('/contact-us', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/contact', fn () => redirect()->route('contact', [], 301));

// Canonical About Us URL. Mirrors the /contact-us pattern: the old /aboutus
// path is kept working via a permanent redirect rather than left as a second
// live 200 page. redirect()->route() is used (not Route::permanentRedirect())
// so the Location header resolves correctly under any base path — see the
// /contact route above for the full explanation.
Route::get('/about-us', [PublicPageController::class, 'aboutUs'])->name('aboutus');
Route::get('/aboutus', fn () => redirect()->route('aboutus', [], 301));

Route::get('/bloglist', function () {
    return view('bloglist');
})->name('bloglist');

Route::get('/singleblog', function () {
    return view('singleblog');
})->name('singleblog');

// Customer-only storefront pages. Grouped under auth so a guest is redirected
// to /sign-in instead of seeing another customer's account UI. order-details
// stays out of this group deliberately: it has no {order} parameter yet, so
// gating it here would protect the page without protecting a specific order —
// that needs a proper /orders/{order} route with ownership authorization,
// which is a separate task.
Route::middleware('auth')->group(function () {
    Route::get('/user-dashboard', function () {
        return view('user-dashboard');
    })->name('user-dashboard');

    Route::get('/account-setting', function () {
        return view('account-setting');
    })->name('account-setting');

    Route::get('/order-history', function () {
        return view('order-history');
    })->name('order-history');

    Route::get('/wishlist', function () {
        return view('wishlist');
    })->name('wishlist');
});

Route::get('/order-details', function () {
    return view('order-details');
})->name('order-details');

// The old static product page is superseded by /products/{slug} (see
// StorefrontProductController below). It's kept as a redirect rather than
// removed outright since it may already be bookmarked/linked externally,
// and it has no product slug of its own to redirect to a specific product.
Route::get('/product-details', fn () => redirect()->route('shop'))->name('product-details');

// Real cart (Phase G). Open to guests and authenticated customers alike —
// CartService resolves a session-based cart for guests and the
// carts/cart_items tables for authenticated users, so no auth middleware
// gates these. Ownership of an authenticated customer's cart_items is
// re-verified inside the service on every mutation, never assumed from the
// route parameter alone.
Route::get('/shopping-cart', [CartController::class, 'index'])->name('shopping-cart');
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/mini', [CartController::class, 'mini'])->name('mini');
    Route::post('/items', [CartController::class, 'store'])->name('items.store');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
});

Route::get('/sign-in', function () {
    return view('sign-in');
})->middleware('guest')->name('sign-in');

Route::get('/create-account', function () {
    return view('create-account');
})->middleware('guest')->name('create-account');

Route::get('/faq', [FaqPageController::class, 'index'])->name('faq');

// Public CMS pages. Prefixed with /page/ so it can never shadow an existing
// or future top-level route (/shop, /faq, /contact, ...). The controller
// restricts to status=active and SoftDeletes excludes trashed rows, so a
// missing, inactive or deleted page all 404 alike.
//
// contact-us has its own canonical route (see /contact-us above) backed by a
// custom Blade design rather than the generic page.blade.php, so /page/{slug}
// must not also serve it — that would be a second indexable 200 page with
// different content for the same topic. Redirecting it here, before the
// wildcard, closes that off without special-casing the slug inside the
// PublicPageController::show() query used by every other managed page.
// See the /contact route above for why redirect()->route() is used instead
// of Route::permanentRedirect().
Route::get('/page/contact-us', fn () => redirect()->route('contact', [], 301));

// Same reasoning as /page/contact-us above: about-us has its own canonical
// route with a custom Blade design, so /page/{slug} must not also serve it.
Route::get('/page/about-us', fn () => redirect()->route('aboutus', [], 301));

// Canonical root-level URLs for the 7 managed CMS pages that use the
// generic page.blade.php template (unlike about-us/contact-us, which have
// custom Blade designs). Each route reuses PublicPageController::show()
// unchanged — Route::defaults() injects the slug even though the URI has
// no {slug} placeholder — so there's no per-page controller method, and
// page.blade.php / the pages table stay the single source of truth for
// content and metadata. The old /page/{slug} URL for each is redirected
// here, before the wildcard, so it never becomes a second indexable copy.
foreach ([
    'privacy-policy',
    'terms-and-conditions',
    'refund-return-policy',
    'shipping-policy',
    'our-mission',
    'our-story',
    'disclaimer',
] as $managedPageSlug) {
    Route::get("/{$managedPageSlug}", [PublicPageController::class, 'show'])
        ->name($managedPageSlug)
        ->defaults('slug', $managedPageSlug);

    Route::get("/page/{$managedPageSlug}", fn () => redirect()->route($managedPageSlug, [], 301));
}

Route::get('/page/{slug}', [PublicPageController::class, 'show'])->name('page.show');

Route::get('/404', function () {
    return view('404');
})->name('404');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');

// Canonical category browsing URL. Category navigation/filters must link
// here directly rather than to /shop?category=slug, so a category never has
// two indexable URLs. Looked up manually inside the controller (not via
// {category:slug} implicit binding) — see CategoryController::show().
Route::get('/category/{slug}', [StorefrontCategoryController::class, 'show'])->name('category.show');

// Looked up manually inside the controller (not via {product:slug} implicit
// binding) — see StorefrontProductController::show().
Route::get('/products/{slug}', [StorefrontProductController::class, 'show'])->name('products.show');

// Admin login/logout are intentionally outside the protected group below —
// the login form must be reachable by guests, and logout only needs `auth`.
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])
            ->middleware('guest')
            ->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])
            ->middleware('guest')
            ->name('login.store');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])
            ->middleware('auth')
            ->name('logout');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'superadmin', 'no-admin-cache'])
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('admin');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Catalog routes...
        Route::prefix('catalog')->name('catalog.')->group(function () {
            // Categories
            Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::put('/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
                Route::delete('/{category}/image', 'destroyImage')->name('image.destroy');
            });
            // Products
            Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::put('/{product}', 'update')->name('update');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });
            // Variants
            Route::prefix('variants')->name('variants.')->controller(ProductVariantController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{variant}/edit', 'edit')->name('edit');
                Route::put('/{variant}', 'update')->name('update');
                Route::delete('/{variant}', 'destroy')->name('destroy');
                Route::delete('/{variant}/images/{image}', 'destroyImage')->name('images.destroy');
            });
            // Reviews
            Route::prefix('reviews')->name('reviews.')->controller(ProductReviewController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::patch('/{review}/status', 'updateStatus')->name('status');
                Route::delete('/{review}', 'destroy')->name('destroy');
            });
            // Tax Rates
            Route::prefix('tax-rates')->name('tax-rates.')->controller(TaxRateController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{taxRate}/edit', 'edit')->name('edit');
                Route::put('/{taxRate}', 'update')->name('update');
                Route::delete('/{taxRate}', 'destroy')->name('destroy');
            });
            // Tags
            Route::prefix('tags')->name('tags.')->controller(TagController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{tag}/edit', 'edit')->name('edit');
                Route::put('/{tag}', 'update')->name('update');
                Route::delete('/{tag}', 'destroy')->name('destroy');
            });
        });
        // Inventory routes...
        Route::prefix('inventory')->name('inventory.')->controller(InventoryController::class)->group(function () {
            Route::get('/stock-levels', 'stockLevels')->name('stock-levels.index');
            Route::get('/stock-levels/export', 'exportStockLevels')->name('stock-levels.export');
            Route::get('/low-stock', 'lowStock')->name('low-stock.index');
            Route::get('/low-stock/export', 'exportLowStock')->name('low-stock.export');
            Route::get('/out-of-stock', 'outOfStock')->name('out-of-stock.index');
            Route::get('/out-of-stock/export', 'exportOutOfStock')->name('out-of-stock.export');
        });
        // Sales routes...
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/orders', [SalesOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/export', [SalesOrderController::class, 'export'])->name('orders.export');
            Route::get('/orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
            Route::get('/payments', [SalesPaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/export', [SalesPaymentController::class, 'export'])->name('payments.export');
            Route::get('/payments/{payment}', [SalesPaymentController::class, 'show'])->name('payments.show');
            Route::prefix('coupons')->name('coupons.')->controller(CouponController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{coupon}/edit', 'edit')->name('edit');
                Route::put('/{coupon}', 'update')->name('update');
                Route::delete('/{coupon}', 'destroy')->name('destroy');
            });
            Route::prefix('returns')->name('returns.')->controller(ReturnController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/{return}', 'show')->name('show');
                Route::patch('/{return}/status', 'updateStatus')->name('status');
            });
        });
        // Customer routes...
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/export', [CustomerController::class, 'export'])->name('export');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');

            Route::prefix('{customer}/addresses')->name('addresses.')->controller(CustomerAddressController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{address}/edit', 'edit')->name('edit');
                Route::put('/{address}', 'update')->name('update');
                Route::delete('/{address}', 'destroy')->name('destroy');
            });

            Route::prefix('{customer}/wishlists')->name('wishlists.')->controller(CustomerWishlistController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::delete('/{wishlist}', 'destroy')->name('destroy');
            });
        });
        // Shipping routes...
        Route::prefix('shipping')->name('shipping.')->group(function () {
            // Zones
            Route::prefix('zones')->name('zones.')->controller(ShippingZoneController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
                Route::get('/import', 'importForm')->name('import');
                Route::post('/import', 'import')->name('import.store');
                Route::get('/import/template', 'downloadTemplate')->name('import.template');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{zone}/edit', 'edit')->name('edit');
                Route::put('/{zone}', 'update')->name('update');
                Route::delete('/{zone}', 'destroy')->name('destroy');
            });
            // Methods
            Route::prefix('methods')->name('methods.')->controller(ShippingMethodController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{method}/edit', 'edit')->name('edit');
                Route::put('/{method}', 'update')->name('update');
                Route::delete('/{method}', 'destroy')->name('destroy');
            });
            // Rates
            Route::prefix('rates')->name('rates.')->controller(ShippingRateController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{rate}/edit', 'edit')->name('edit');
                Route::put('/{rate}', 'update')->name('update');
                Route::delete('/{rate}', 'destroy')->name('destroy');
            });
        });

        // CMS routes...
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::prefix('pages')->name('pages.')->controller(PageController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{page}/edit', 'edit')->name('edit');
                Route::put('/{page}', 'update')->name('update');
                Route::delete('/{page}', 'destroy')->name('destroy');
            });
            Route::prefix('blogs')->name('blogs.')->controller(BlogController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{blog}/edit', 'edit')->name('edit');
                Route::put('/{blog}', 'update')->name('update');
                Route::delete('/{blog}', 'destroy')->name('destroy');
            });
            Route::prefix('blog-categories')->name('blog-categories.')->controller(BlogCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{blogCategory}/edit', 'edit')->name('edit');
                Route::put('/{blogCategory}', 'update')->name('update');
                Route::delete('/{blogCategory}', 'destroy')->name('destroy');
            });
            Route::prefix('blog-tags')->name('blog-tags.')->controller(BlogTagController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{blogTag}/edit', 'edit')->name('edit');
                Route::put('/{blogTag}', 'update')->name('update');
                Route::delete('/{blogTag}', 'destroy')->name('destroy');
            });
            Route::prefix('faqs')->name('faqs.')->controller(FaqController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{faq}/edit', 'edit')->name('edit');
                Route::put('/{faq}', 'update')->name('update');
                Route::delete('/{faq}', 'destroy')->name('destroy');
            });
            Route::prefix('team-members')->name('team-members.')->controller(TeamMemberController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{teamMember}/edit', 'edit')->name('edit');
                Route::put('/{teamMember}', 'update')->name('update');
                Route::delete('/{teamMember}', 'destroy')->name('destroy');
            });
            Route::prefix('testimonials')->name('testimonials.')->controller(TestimonialController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{testimonial}/edit', 'edit')->name('edit');
                Route::put('/{testimonial}', 'update')->name('update');
                Route::delete('/{testimonial}', 'destroy')->name('destroy');
            });
            Route::prefix('contact-messages')->name('contact-messages.')->controller(ContactMessageController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{message}', 'show')->name('show');
                Route::put('/{message}/status', 'updateStatus')->name('update-status');
                Route::delete('/{message}', 'destroy')->name('destroy');
            });
        });
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::view('/sales', 'admin.reports.sales.index')->name('sales.index');
            Route::view('/orders', 'admin.reports.orders.index')->name('orders.index');
            Route::view('/products', 'admin.reports.products.index')->name('products.index');
            Route::get('/customers', [CustomerController::class, 'report'])->name('customers.index');
            Route::get('/customers/export', [CustomerController::class, 'exportReport'])->name('customers.export');
            Route::view('/inventory', 'admin.reports.inventory.index')->name('inventory.index');
            Route::view('/payments', 'admin.reports.payments.index')->name('payments.index');
            Route::view('/coupons', 'admin.reports.coupons.index')->name('coupons.index');
            Route::get('/returns', [ReturnController::class, 'report'])->name('returns.index');
            Route::get('/returns/export', [ReturnController::class, 'exportReport'])->name('returns.export');
        });
        Route::prefix('administration')->name('administration.')->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::view('/create', 'admin.administration.users.create')->name('create');
                Route::view('/edit', 'admin.administration.users.edit')->name('edit');
            });
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::view('/', 'admin.administration.roles.index')->name('index');
                Route::view('/create', 'admin.administration.roles.create')->name('create');
                Route::view('/edit', 'admin.administration.roles.edit')->name('edit');
            });
            Route::view('/permissions', 'admin.administration.permissions.index')
                ->name('permissions.index');
            Route::view('/activity-logs', 'admin.administration.activity-logs.index')
                ->name('activity-logs.index');
        });
        Route::prefix('settings')->name('settings.')->controller(SettingController::class)->group(function () {
            Route::get('/general', 'general')->name('general.index');
            Route::put('/general', 'updateGeneral')->name('general.update');
            Route::get('/seo', 'seo')->name('seo.index');
            Route::put('/seo', 'updateSeo')->name('seo.update');
            Route::get('/email', 'email')->name('email.index');
            Route::put('/email', 'updateEmail')->name('email.update');
            Route::get('/payment', 'payment')->name('payment.index');
            Route::put('/payment', 'updatePayment')->name('payment.update');
        });
        Route::prefix('profile')->name('profile.')->controller(AdminProfileController::class)->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
            Route::put('/password', 'updatePassword')->name('password');
            Route::delete('/avatar', 'destroyAvatar')->name('avatar.destroy');
        });
    });

require __DIR__.'/auth.php';
