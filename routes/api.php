<?php

use App\Http\Controllers\Api\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;

// Razorpay authenticates this request itself via the X-Razorpay-Signature
// header (see RazorpayWebhookController) — no Laravel auth/CSRF applies,
// which is exactly what the stateless `api` middleware group gives it.
Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle'])->name('webhooks.razorpay');
