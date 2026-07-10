<?php

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerAddressController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerWishlistController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\SalesPaymentController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\ShippingZoneController;
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
        Route::prefix('inventory')->name('inventory.')->controller(InventoryController::class)->group(function () {
            Route::get('/stock-levels', 'stockLevels')->name('stock-levels.index');
            Route::get('/low-stock', 'lowStock')->name('low-stock.index');
            Route::get('/out-of-stock', 'outOfStock')->name('out-of-stock.index');
        });
        // Sales routes...
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/orders', [SalesOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
            Route::get('/payments', [SalesPaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [SalesPaymentController::class, 'show'])->name('payments.show');
            Route::prefix('coupons')->name('coupons.')->controller(CouponController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{coupon}/edit', 'edit')->name('edit');
                Route::put('/{coupon}', 'update')->name('update');
                Route::delete('/{coupon}', 'destroy')->name('destroy');
            });
            Route::prefix('returns')->name('returns.')->controller(ReturnController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{return}', 'show')->name('show');
                Route::patch('/{return}/status', 'updateStatus')->name('status');
            });
        });
        // Customer routes...
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
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
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{zone}/edit', 'edit')->name('edit');
                Route::put('/{zone}', 'update')->name('update');
                Route::delete('/{zone}', 'destroy')->name('destroy');
            });
            // Methods
            Route::view('/methods', 'admin.shipping.methods.index')->name('methods.index');
            Route::view('/methods/create', 'admin.shipping.methods.create')->name('methods.create');
            Route::view('/methods/edit', 'admin.shipping.methods.edit')->name('methods.edit');
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
            Route::prefix('blogs')->name('blogs.')->group(function () {
                Route::view('/', 'admin.cms.blogs.index')->name('index');
                Route::view('/create', 'admin.cms.blogs.create')->name('create');
                Route::view('/edit', 'admin.cms.blogs.edit')->name('edit');
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
