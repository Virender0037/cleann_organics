<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The packed parcel, as measured by the admin. Units match NimbusPost's create-shipment API: grams and centimetres.
 * NimbusPost v2 requires all three dimensions to book, so they are required here.
 */
class CreateShipmentRequest extends FormRequest
{
    protected $errorBag = 'shipment';

    public function rules(): array
    {
        return [
            'package_weight_grams' => ['required', 'integer', 'min:1', 'max:100000'],
            'package_length_cm' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'package_width_cm' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'package_height_cm' => ['required', 'numeric', 'min:0.1', 'max:500'],
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
