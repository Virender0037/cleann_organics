<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class AddWishlistItemRequest extends FormRequest
{
    /**
     * Authentication itself is enforced by the `auth` middleware on the
     * route, not here — WishlistService independently re-verifies the
     * product is a real, currently-public product regardless of what's
     * submitted.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ];
    }
}
