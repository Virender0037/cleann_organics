<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Support\Marketplaces;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Create / update one marketplace listing from the product edit page's dialog.
 * Errors use the named bag "marketplace" so they never mix with the product form's own errors.
 */
class SaveMarketplacePriceRequest extends FormRequest
{
    protected $errorBag = 'marketplace';

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'marketplace' => ['required', 'string', Rule::in(array_keys(Marketplaces::options()))],
            'product_variant_id' => ['nullable', 'integer'],
            'marketplace_product_name' => ['nullable', 'string', 'max:255'],
            'selling_price' => ['nullable', 'required_if:is_active,1', 'numeric', 'min:0', 'max:9999999.99'],
            'mrp' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            // http/https only: javascript:, data:, file:, ftp: … all fail "url:http,https".
            'product_url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'affiliate_url' => ['nullable', 'string', 'max:2048', 'url:http,https'],
            'is_active' => ['required', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'last_checked_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'selling_price.required_if' => 'A selling price is required while the listing is active.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active') ? 1 : 0,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Product $product */
            $product = $this->route('product');
            $current = $this->route('marketplacePrice');
            $marketplace = (string) $this->input('marketplace');

            $variantId = $this->input('product_variant_id');
            $variantId = ($variantId === null || $variantId === '') ? null : (int) $variantId;

            if ($variantId !== null && ! $product->variants()->whereKey($variantId)->exists()) {
                $validator->errors()->add('product_variant_id', "Choose one of this product's own variants.");
            }

            // One listing per product + variant + marketplace.
            $duplicate = ProductMarketplacePrice::query()
                ->where('product_id', $product->id)
                ->where('variant_scope', (int) ($variantId ?? 0))
                ->where('marketplace', $marketplace)
                ->when($current instanceof ProductMarketplacePrice, fn ($query) => $query->whereKeyNot($current->id))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('marketplace', Marketplaces::label($marketplace).' already has a listing for '.($variantId ? 'that variant' : 'this product (product-level)').'. Edit it instead.');
            }

            // A product URL should be on the marketplace it is filed under. (The affiliate URL is exempt: affiliate
            // networks use their own tracking domains.)
            $url = trim((string) $this->input('product_url'));
            if ($url !== '' && ! $validator->errors()->has('product_url') && Marketplaces::exists($marketplace) && ! Marketplaces::urlBelongsTo($url, $marketplace)) {
                $validator->errors()->add('product_url', 'The product URL must be on '.implode(' / ', Marketplaces::domains($marketplace)).'. Use the affiliate URL field for tracking links on other domains.');
            }
        });
    }
}
