<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\ProductVariant;
use App\Rules\VariantMediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

/**
 * Shared by StoreProductVariantRequest and UpdateProductVariantRequest: the
 * per-file rules are identical, and the max-images/max-videos count check
 * (existing DB rows + newly submitted files) only differs in what "existing"
 * means for a create (always zero) vs an update (the route-bound variant).
 */
trait ValidatesVariantMediaLimits
{
    /**
     * @return array<string, mixed>
     */
    protected function variantMediaRules(): array
    {
        return [
            'new_media' => ['nullable', 'array'],
            'new_media.*' => [new VariantMediaFile],
            'media_order' => ['nullable', 'string'],
            'primary_selector' => ['nullable', 'string', 'regex:/^(existing:\d+|new:\d+)$/'],
        ];
    }

    protected function withValidatorMediaLimits(Validator $validator, ?ProductVariant $variant): void
    {
        $validator->after(function (Validator $validator) use ($variant) {
            $config = config('media.variant');

            $newFiles = array_filter(
                (array) $this->file('new_media', []),
                fn ($file) => $file instanceof UploadedFile && $file->isValid()
            );

            $newImageCount = 0;
            $newVideoCount = 0;

            foreach ($newFiles as $file) {
                $extension = strtolower((string) $file->getClientOriginalExtension());

                if (in_array($extension, $config['image_mimes'], true)) {
                    $newImageCount++;
                } elseif (in_array($extension, $config['video_mimes'], true)) {
                    $newVideoCount++;
                }
            }

            $existingImageCount = $variant
                ? $variant->images()->where('media_type', 'image')->count()
                : 0;

            $existingVideoCount = $variant
                ? $variant->images()->where('media_type', 'video')->count()
                : 0;

            $totalImages = $existingImageCount + $newImageCount;
            $totalVideos = $existingVideoCount + $newVideoCount;

            if ($totalImages > $config['max_images']) {
                $allowed = max(0, $config['max_images'] - $existingImageCount);
                $validator->errors()->add(
                    'new_media',
                    "This variant already has {$existingImageCount} of {$config['max_images']} images allowed — only {$allowed} more may be uploaded."
                );
            }

            if ($totalVideos > $config['max_videos']) {
                $allowed = max(0, $config['max_videos'] - $existingVideoCount);
                $validator->errors()->add(
                    'new_media',
                    "This variant already has {$existingVideoCount} of {$config['max_videos']} videos allowed — only {$allowed} more may be uploaded."
                );
            }

            $this->validatePrimarySelector($validator, $variant, $config);
        });
    }

    /**
     * A video can never be primary: reject a primary_selector that points at
     * a video, whether it's an already-saved image row or a pending upload.
     *
     * @param  array<string, mixed>  $config
     */
    private function validatePrimarySelector(Validator $validator, ?ProductVariant $variant, array $config): void
    {
        $selector = $this->input('primary_selector');

        if (blank($selector)) {
            return;
        }

        if (str_starts_with($selector, 'existing:')) {
            $id = (int) substr($selector, strlen('existing:'));
            $image = $variant?->images->firstWhere('id', $id);

            if (! $image || $image->product_variant_id !== $variant?->id) {
                $validator->errors()->add('primary_selector', 'The selected primary image does not belong to this variant.');
            } elseif (! $image->isImage()) {
                $validator->errors()->add('primary_selector', 'A video cannot be set as the primary image.');
            }

            return;
        }

        if (str_starts_with($selector, 'new:')) {
            $index = (int) substr($selector, strlen('new:'));
            // Index into the raw submitted array (not the isValid()-filtered
            // one) so it matches the client's visual ordering exactly.
            $file = $this->file("new_media.{$index}");

            if (! $file) {
                $validator->errors()->add('primary_selector', 'The selected primary image was not found among the uploaded files.');

                return;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());

            if (! in_array($extension, $config['image_mimes'], true)) {
                $validator->errors()->add('primary_selector', 'A video cannot be set as the primary image.');
            }
        }
    }
}
