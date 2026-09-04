<?php

return [

    /*
    |--------------------------------------------------------------------
    | Product Variant Media
    |--------------------------------------------------------------------
    |
    | Limits for the variant media manager (Admin > Catalog > Variants).
    | Image size follows the existing 2MB convention already used across
    | the app (product images, settings logo/favicon/OG image). Video has
    | no prior convention in this codebase, so a configurable default is
    | provided here rather than hardcoded in the Form Requests.
    |
    */

    'variant' => [
        'max_images' => (int) env('VARIANT_MEDIA_MAX_IMAGES', 10),
        'max_videos' => (int) env('VARIANT_MEDIA_MAX_VIDEOS', 5),

        'max_image_kb' => (int) env('VARIANT_MEDIA_MAX_IMAGE_KB', 2048),
        'max_video_kb' => (int) env('VARIANT_MEDIA_MAX_VIDEO_KB', 20480),

        'image_mimes' => ['jpeg', 'jpg', 'png', 'webp'],
        'video_mimes' => ['mp4', 'webm'],
        'video_mimetypes' => ['video/mp4', 'video/webm'],
    ],

];
