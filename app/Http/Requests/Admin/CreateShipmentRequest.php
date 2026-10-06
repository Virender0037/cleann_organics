<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The packed parcel, as measured by the admin. Units match NimbusPost's create-shipment API: grams and centimetres.
 * Dimensions are optional there, but all three or none are accepted (a partial box size is meaningless).
 */
class CreateShipmentRequest extends FormRequest
{
    protected $errorBag = 'shipment';

    public function rules(): array
    {
        return [
            'package_weight_grams' => ['required', 'integer', 'min:1', 'max:100000'],
            'package_length_cm' => ['nullable', 'numeric', 'min:0.1', 'max:500', 'required_with:package_width_cm,package_height_cm'],
            'package_width_cm' => ['nullable', 'numeric', 'min:0.1', 'max:500', 'required_with:package_length_cm,package_height_cm'],
            'package_height_cm' => ['nullable', 'numeric', 'min:0.1', 'max:500', 'required_with:package_length_cm,package_width_cm'],
        ];
    }

    public function attributes(): array
    {
        return [
            'package_weight_grams' => 'package weight (g)',
            'package_length_cm' => 'length (cm)',
            'package_width_cm' => 'width (cm)',
            'package_height_cm' => 'height (cm)',
        ];
    }
}
