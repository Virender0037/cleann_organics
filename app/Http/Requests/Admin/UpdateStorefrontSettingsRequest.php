<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStorefrontSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'free_shipping_threshold' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'flat_shipping_charge' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'gift_voucher_threshold' => ['required', 'numeric', 'min:0', 'max:1000000', 'gte:free_shipping_threshold'],
            'voucher_value' => ['required', 'numeric', 'min:1', 'max:100000'],
            'voucher_min_order' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'voucher_validity_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'gift_voucher_threshold.gte' => 'The gift & voucher threshold must be at least the free-shipping threshold.',
        ];
    }
}
