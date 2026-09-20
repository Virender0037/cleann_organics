<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveReelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:30720'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $reel = $this->route('reel');

            $hasVideo = $this->hasFile('video') || filled($this->input('video_url')) || filled($reel?->video_path) || filled($reel?->video_url);

            if (! $hasVideo && ! filled($this->input('instagram_url'))) {
                $validator->errors()->add('video', 'Provide a video file, a video URL, or an Instagram URL.');
            }

            if ($this->filled('product_variant_id') && $this->filled('product_id')) {
                $belongs = \App\Models\ProductVariant::query()
                    ->whereKey($this->integer('product_variant_id'))
                    ->where('product_id', $this->integer('product_id'))
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add('product_variant_id', 'That variant does not belong to the selected product.');
                }
            }
        });
    }
}
