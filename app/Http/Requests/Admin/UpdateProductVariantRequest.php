<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_name' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique(ProductVariant::class)->ignore($this->route('variant'))],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique(ProductVariant::class)->ignore($this->route('variant'))],
            'unit' => ['nullable', 'in:kg,gram,litre,piece,pack'],
            'size' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:255'],
            'pack_quantity' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'enable_tiered_pricing' => ['required', 'boolean'],
            'single_quantity' => ['nullable', 'integer', 'min:0'],
            'single_price' => ['nullable', 'numeric', 'min:0'],
            'standard_quantity' => ['nullable', 'integer', 'min:0', 'required_if:enable_tiered_pricing,1'],
            'standard_price' => ['nullable', 'numeric', 'min:0', 'required_if:enable_tiered_pricing,1'],
            'discount_quantity' => ['nullable', 'integer', 'min:0', 'required_if:enable_tiered_pricing,1'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'required_if:enable_tiered_pricing,1'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', 'in:in_stock,out_of_stock'],
            'is_default' => ['required', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
            'primary_image' => [
                'nullable',
                'integer',
                Rule::exists('product_variant_images', 'id')->where('product_variant_id', $this->route('variant')->id),
            ],
        ];
    }
}
