<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Ownership of the specific item is verified inside CartService (a
     * guest can only ever reach their own session cart; an authenticated
     * customer's item lookup is always scoped to Auth::id()) — this only
     * confirms the request shape is valid, not who the item belongs to.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
