<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Validates a single uploaded variant media file: must be a genuine image
 * (jpeg/jpg/png/webp) or video (mp4/webm) by actual detected content type,
 * not just its extension, and within the size limit for its type.
 */
class VariantMediaFile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('The :attribute must be a valid uploaded file.');

            return;
        }

        $config = config('media.variant');
        $extension = strtolower((string) $value->getClientOriginalExtension());
        $mime = (string) $value->getMimeType();

        if (in_array($extension, $config['image_mimes'], true)) {
            if (! str_starts_with($mime, 'image/')) {
                $fail('The :attribute does not appear to be a genuine image file.');

                return;
            }

            if ($value->getSize() > $config['max_image_kb'] * 1024) {
                $fail('The :attribute image must not be larger than '.$config['max_image_kb'].'KB.');
            }

            return;
        }

        if (in_array($extension, $config['video_mimes'], true)) {
            if (! in_array($mime, $config['video_mimetypes'], true)) {
                $fail('The :attribute does not appear to be a genuine video file.');

                return;
            }

            if ($value->getSize() > $config['max_video_kb'] * 1024) {
                $fail('The :attribute video must not be larger than '.$config['max_video_kb'].'KB.');
            }

            return;
        }

        $fail('The :attribute must be an image (jpeg, jpg, png, webp) or a video (mp4, webm).');
    }
}
