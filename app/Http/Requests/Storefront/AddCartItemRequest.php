<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    /**
     * Both guests and authenticated customers may add to cart — ownership
     * has no meaning yet at this point (the item doesn't exist), so this is
     * always authorized. CartService independently re-verifies the variant
     * is real, active, and in stock regardless of what's submitted here.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
