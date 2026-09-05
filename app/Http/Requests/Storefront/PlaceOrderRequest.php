<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Authentication is enforced by the `auth` middleware. Address
     * ownership is deliberately re-checked here via the `exists` rule's
     * where() clause AND again inside CheckoutService::placeOrder() — two
     * independent checks rather than trusting either alone.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where('user_id', Auth::id()),
            ],
            'payment_method' => ['required', 'in:cod,upi,bank_transfer'],
        ];
    }
}
