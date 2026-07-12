<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique(Product::class)->ignore($this->route('product'))],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tax_rate_id' => ['nullable', 'integer', 'exists:tax_rates,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,active,inactive'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_returnable' => ['required', 'boolean'],
            'return_days' => ['nullable', 'integer', 'min:0', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['required', 'boolean'],
            'is_latest' => ['required', 'boolean'],
            'is_best_seller' => ['required', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'specifications.*.title' => ['nullable', 'string', 'max:255'],
            'specifications.*.value' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')->whereNull('deleted_at')],
        ];
    }
}
