<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreProductReviewRequest;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Submission only — moderation (approve/reject/delete) stays entirely with
 * the existing admin ProductReviewController; nothing here ever changes a
 * review's status once created.
 */
class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request): RedirectResponse
    {
        ProductReview::create([
            // user_id and status are never taken from the request — the
            // form doesn't even offer them as fields, but this is the line
            // that actually matters: even a hand-crafted request body
            // cannot make this anything other than the logged-in user's own
            // pending review.
            'user_id' => Auth::id(),
            'product_id' => $request->validated('product_id'),
            'rating' => $request->validated('rating'),
            'title' => $request->validated('title'),
            'review' => $request->validated('review'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thanks for your review! It will appear once our team has reviewed it.');
    }
}
