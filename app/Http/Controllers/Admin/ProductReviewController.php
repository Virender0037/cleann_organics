<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductReviewStatusRequest;
use App\Models\ProductReview;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = ProductReview::with(['product', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->whereHas('product', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.catalog.reviews.index', compact('reviews'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $reviews = ProductReview::with(['product', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($query) use ($search) {
                    $query->whereHas('product', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $request->integer('rating')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->lazy(200);

        $headers = ['id', 'product_name', 'customer_name', 'customer_email', 'rating', 'review', 'status', 'date'];

        $rows = $reviews->map(fn (ProductReview $review) => [
            $review->id,
            $review->product->name ?? null,
            $review->user->name ?? null,
            $review->user->email ?? null,
            $review->rating,
            $review->review,
            $review->status,
            $review->created_at->format('d M Y'),
        ]);

        return $exporter->stream('product-reviews.csv', $headers, $rows);
    }

    public function updateStatus(UpdateProductReviewStatusRequest $request, ProductReview $review): RedirectResponse
    {
        $review->update(['status' => $request->status]);

        return back()->with('success', 'Review marked as '.$request->status.'.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
