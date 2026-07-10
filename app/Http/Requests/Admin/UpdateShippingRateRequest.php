<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ChecksShippingRateOverlap;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRateRequest extends FormRequest
{
    use ChecksShippingRateOverlap;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_zone_id' => ['required', 'integer', 'exists:shipping_zones,id'],
            'min_weight' => ['required', 'numeric', 'min:0'],
            'max_weight' => ['nullable', 'numeric', 'min:0', 'gte:min_weight'],
            'shipping_charge' => ['required', 'numeric', 'min:0'],
            'free_shipping_above' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->checkForOverlappingRange($validator, $this->route('rate')?->id);
        });
    }
}
