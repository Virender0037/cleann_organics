<?php

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

        Route::view('/', 'admin-dist.pages.login')
            ->name('admin');

        Route::view('/dashboard', 'admin.dashboard')
            ->name('dashboard');

        Route::view('/catalog/categories', 'admin.catalog.categories.index')
            ->name('catalog.categories.index');
        
        Route::view('/catalog/categories/edit', 'admin.catalog.categories.edit')
            ->name('catalog.categories.edit');

        Route::view('/catalog/categories/create', 'admin.catalog.categories.create')
            ->name('catalog.categories.create');

        Route::view('/catalog/products', 'admin.catalog.products.index')
            ->name('catalog.products.index');

        Route::view('/catalog/products/create', 'admin.catalog.products.create')
            ->name('catalog.products.create');

        Route::view('/catalog/products/edit', 'admin.catalog.products.edit')
            ->name('catalog.products.edit');

        Route::view('/catalog/variants', 'admin.catalog.variants.index')
            ->name('catalog.variants.index');

        Route::view('/catalog/variants/create', 'admin.catalog.variants.create')
            ->name('catalog.variants.create');

        Route::view('/catalog/variants/edit', 'admin.catalog.variants.edit')
            ->name('catalog.variants.edit');

        Route::view('/catalog/reviews', 'admin.catalog.reviews.index')
            ->name('catalog.reviews.index');

        Route::view('/catalog/tax-rates', 'admin.catalog.tax-rates.index')
            ->name('catalog.tax-rates.index');
        
        Route::view('/catalog/tax-rates/create', 'admin.catalog.tax-rates.create')
            ->name('catalog.tax-rates.create');

        Route::view('/catalog/tax-rates/edit', 'admin.catalog.tax-rates.edit')
            ->name('catalog.tax-rates.edit');
    });

require __DIR__.'/auth.php';
