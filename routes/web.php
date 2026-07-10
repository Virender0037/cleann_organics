<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/aboutus', function () {
    return view('aboutus');
})->name('aboutus');

Route::get('/bloglist', function () {
    return view('bloglist');
})->name('bloglist');

Route::get('/singleblog', function () {
    return view('singleblog');
})->name('singleblog');

Route::get('/user-dashboard', function () {
    return view('user-dashboard');
})->name('user-dashboard');

Route::get('/account-setting', function () {
    return view('account-setting');
})->name('account-setting');

Route::get('/order-history', function () {
    return view('order-history');
})->name('order-history');

Route::get('/order-details', function () {
    return view('order-details');
})->name('order-details');

Route::get('/product-details', function () {
    return view('product-details');
})->name('product-details');

Route::get('/wishlist', function () {
    return view('wishlist');
})->name('wishlist');

Route::get('/shopping-cart', function () {
    return view('shopping-cart');
})->name('shopping-cart');

Route::get('/sign-in', function () {
    return view('sign-in');
})->name('sign-in');

Route::get('/create-account', function () {
    return view('create-account');
})->name('create-account');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/404', function () {
    return view('404');
})->name('404');

Route::get('/shop', function () {
    return view('shop');
})->name('shop');

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin-dist.pages.login')->name('admin');
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        // Catalog routes...
        Route::prefix('catalog')->name('catalog.')->group(function () {
            // Categories
            Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::put('/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });
            // Products
            Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::put('/{product}', 'update')->name('update');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });
            // Variants
            Route::prefix('variants')->name('variants.')->controller(ProductVariantController::class)->group(function () {
                Route::get('/', 'index')->name('index');
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
        });
        // Inventory routes...
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::view('/stock-levels', 'admin.inventory.stock-levels.index')->name('stock-levels.index');
            Route::view('/low-stock', 'admin.inventory.low-stock.index')->name('low-stock.index');
            Route::view('/out-of-stock', 'admin.inventory.out-of-stock.index')->name('out-of-stock.index');
        });
        // Sales routes...
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::view('/orders', 'admin.sales.orders.index')->name('orders.index');
            Route::view('/orders/show', 'admin.sales.orders.show')->name('orders.show');
            Route::view('/payments', 'admin.sales.payments.index')->name('payments.index');
            Route::view('/payments/show', 'admin.sales.payments.show')->name('payments.show');
            Route::view('/coupons', 'admin.sales.coupons.index')->name('coupons.index');
            Route::view('/coupons/create', 'admin.sales.coupons.create')->name('coupons.create');
            Route::view('/coupons/edit', 'admin.sales.coupons.edit')->name('coupons.edit');
            Route::view('/returns', 'admin.sales.returns.index')->name('returns.index');
            Route::view('/returns/show', 'admin.sales.returns.show')->name('returns.show');
        });
        // Customer routes...
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::view('/', 'admin.customers.index')->name('index');
            Route::view('/show', 'admin.customers.show')->name('show');
            Route::view('/edit', 'admin.customers.edit')->name('edit');
            Route::view('/addresses', 'admin.customers.addresses.index')->name('addresses.index');
            Route::view('/wishlists', 'admin.customers.wishlists.index')->name('wishlists.index');
            Route::view('/addresses/create', 'admin.customers.addresses.create')->name('addresses.create');
            Route::view('/addresses/edit', 'admin.customers.addresses.edit')->name('addresses.edit');
        });
        // Shipping routes...
        Route::prefix('shipping')->name('shipping.')->group(function () {
            // Zones
            Route::view('/zones', 'admin.shipping.zones.index')->name('zones.index');
            Route::view('/zones/create', 'admin.shipping.zones.create')->name('zones.create');
            Route::view('/zones/edit', 'admin.shipping.zones.edit')->name('zones.edit');
            // Methods
            Route::view('/methods', 'admin.shipping.methods.index')->name('methods.index');
            Route::view('/methods/create', 'admin.shipping.methods.create')->name('methods.create');
            Route::view('/methods/edit', 'admin.shipping.methods.edit')->name('methods.edit');
            // Rates
            Route::view('/rates', 'admin.shipping.rates.index')->name('rates.index');
            Route::view('/rates/create', 'admin.shipping.rates.create')->name('rates.create');
            Route::view('/rates/edit', 'admin.shipping.rates.edit')->name('rates.edit');
        });

        // CMS routes...
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::prefix('pages')->name('pages.')->group(function () {
                Route::view('/', 'admin.cms.pages.index')->name('index');
                Route::view('/create', 'admin.cms.pages.create')->name('create');
                Route::view('/edit', 'admin.cms.pages.edit')->name('edit');
            });
            Route::prefix('blogs')->name('blogs.')->group(function () {
                Route::view('/', 'admin.cms.blogs.index')->name('index');
                Route::view('/create', 'admin.cms.blogs.create')->name('create');
                Route::view('/edit', 'admin.cms.blogs.edit')->name('edit');
            });
            Route::prefix('blog-categories')->name('blog-categories.')->group(function () {
                Route::view('/', 'admin.cms.blog-categories.index')->name('index');
                Route::view('/create', 'admin.cms.blog-categories.create')->name('create');
                Route::view('/edit', 'admin.cms.blog-categories.edit')->name('edit');
            });
            Route::prefix('blog-tags')->name('blog-tags.')->group(function () {
                Route::view('/', 'admin.cms.blog-tags.index')->name('index');
                Route::view('/create', 'admin.cms.blog-tags.create')->name('create');
                Route::view('/edit', 'admin.cms.blog-tags.edit')->name('edit');
            });
            Route::prefix('faqs')->name('faqs.')->group(function () {
                Route::view('/', 'admin.cms.faqs.index')->name('index');
                Route::view('/create', 'admin.cms.faqs.create')->name('create');
                Route::view('/edit', 'admin.cms.faqs.edit')->name('edit');
            });
            Route::prefix('team-members')->name('team-members.')->group(function () {
                Route::view('/', 'admin.cms.team-members.index')->name('index');
                Route::view('/create', 'admin.cms.team-members.create')->name('create');
                Route::view('/edit', 'admin.cms.team-members.edit')->name('edit');
            });
            Route::prefix('testimonials')->name('testimonials.')->group(function () {
                Route::view('/', 'admin.cms.testimonials.index')->name('index');
                Route::view('/create', 'admin.cms.testimonials.create')->name('create');
                Route::view('/edit', 'admin.cms.testimonials.edit')->name('edit');
            });
            Route::prefix('contact-messages')->name('contact-messages.')->group(function () {
                Route::view('/', 'admin.cms.contact-messages.index')->name('index');
                Route::view('/show', 'admin.cms.contact-messages.show')->name('show');
            });
        });
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::view('/sales', 'admin.reports.sales.index')->name('sales.index');
            Route::view('/orders', 'admin.reports.orders.index')->name('orders.index');
            Route::view('/products', 'admin.reports.products.index')->name('products.index');
            Route::view('/customers', 'admin.reports.customers.index')->name('customers.index');
            Route::view('/inventory', 'admin.reports.inventory.index')->name('inventory.index');
            Route::view('/payments', 'admin.reports.payments.index')->name('payments.index');
            Route::view('/coupons', 'admin.reports.coupons.index')->name('coupons.index');
            Route::view('/returns', 'admin.reports.returns.index')->name('returns.index');
        });
        Route::prefix('administration')->name('administration.')->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Route::view('/', 'admin.administration.users.index')->name('index');
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
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::view('/general', 'admin.settings.general.index')->name('general.index');
            Route::view('/seo', 'admin.settings.seo.index')->name('seo.index');
            Route::view('/email', 'admin.settings.email.index')->name('email.index');
        });
    });

require __DIR__.'/auth.php';
