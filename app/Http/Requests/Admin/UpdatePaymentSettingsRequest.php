<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_currency' => ['required', 'in:INR,USD,EUR'],
            'currency_symbol_position' => ['required', 'in:before,after'],
            'enable_razorpay' => ['nullable', 'boolean'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_secret_key' => ['nullable', 'string', 'max:255'],
            'enable_stripe' => ['nullable', 'boolean'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'enable_cod' => ['nullable', 'boolean'],
            'enable_upi' => ['nullable', 'boolean'],
            'upi_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
