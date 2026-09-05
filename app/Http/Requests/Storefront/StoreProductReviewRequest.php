<?php

namespace App\Http\Requests\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProductReviewRequest extends FormRequest
{
    /**
     * Authentication is enforced by the `auth` middleware on the route.
     * user_id and status are never accepted as input at all (see rules()) —
     * the controller sets both explicitly, so there is nothing here for a
     * client to spoof even if it tried.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                // Reuses Product::scopePublic() — the same active-product +
                // active-category rule the storefront already applies
                // everywhere else — rather than a second definition of
                // "valid product" living here.
                function ($attribute, $value, $fail) {
                    if (! Product::query()->public()->whereKey($value)->exists()) {
                        $fail('This product is not available for review.');
                    }
                },
                // One active review per customer per product (matches the
                // existing unique(user_id, product_id) DB constraint) —
                // surfaced as a normal validation message rather than a
                // constraint-violation exception.
                Rule::unique('product_reviews', 'product_id')->where(
                    fn ($query) => $query->where('user_id', Auth::id())
                ),
            ],
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'review' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique' => 'You have already reviewed this product.',
        ];
    }
}
