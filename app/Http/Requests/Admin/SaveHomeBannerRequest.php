<?php

namespace App\Http\Requests\Admin;

use App\Models\HomeBanner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveHomeBannerRequest extends FormRequest
{
    public function rules(): array
    {
        $isCreate = $this->isMethod('post');
        $existing = $this->route('banner');
        $section = $this->input('section', $existing?->section ?? HomeBanner::SECTION_HERO);
        $isHero = $section === HomeBanner::SECTION_HERO;

        // Hero slides need a picture (unless one is already stored and not
        // being removed); benefit cards may be icon-only.
        $imageRule = ($isHero && ($isCreate || ! $existing?->image || $this->boolean('remove_image')))
            ? 'required'
            : 'nullable';

        return [
            'section' => ['required', Rule::in(HomeBanner::SECTIONS)],
            'title' => [$isHero ? 'nullable' : 'required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'image' => [$imageRule, 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_mobile_image' => ['nullable', 'boolean'],
            'icon' => ['nullable', Rule::in(array_keys(HomeBanner::ICONS))],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'link_type' => ['required', Rule::in(HomeBanner::LINK_TYPES)],
            'product_id' => ['nullable', 'required_if:link_type,product', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'required_if:link_type,category', 'integer', 'exists:categories,id'],
            'tag_id' => ['nullable', 'required_if:link_type,tag', 'integer', 'exists:tags,id'],
            // A path ("/shop?max_price=99") or a full URL — never javascript:.
            'link_url' => ['nullable', 'required_if:link_type,url', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/i'],
            'opens_new_tab' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $existing = $this->route('banner');

            // A benefit card needs *something* visual: an icon or an image.
            if ($this->input('section') === HomeBanner::SECTION_BENEFIT) {
                $hasImage = $this->hasFile('image') || ($existing?->image && ! $this->boolean('remove_image'));

                if (! $this->filled('icon') && ! $hasImage) {
                    $validator->errors()->add('icon', 'Choose an icon or upload an image.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'link_url.regex' => 'The link must start with "/" (a page on this site) or "https://".',
        ];
    }
}
