<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\TrialController;

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


//Route::get('/try', [TrialController::class, 'index']);