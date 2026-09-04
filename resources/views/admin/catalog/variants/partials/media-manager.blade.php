@php
    $existingMedia = ($variant?->images ?? collect())->map(fn ($image) => [
        'id' => $image->id,
        'type' => $image->media_type,
        'url' => \Illuminate\Support\Facades\Storage::url($image->image),
        'isPrimary' => (bool) $image->is_primary,
    ])->values();

    $mediaConfig = config('media.variant');

    $jsConfig = [
        'maxImages' => $mediaConfig['max_images'],
        'maxVideos' => $mediaConfig['max_videos'],
        'maxImageKb' => $mediaConfig['max_image_kb'],
        'maxVideoKb' => $mediaConfig['max_video_kb'],
        'imageExtensions' => $mediaConfig['image_mimes'],
        'videoExtensions' => $mediaConfig['video_mimes'],
    ];
@endphp

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Variant Media</h5>
        <div class="vmm-counters">
            <span>Images: <strong id="vmmImageCount">0</strong> / {{ $mediaConfig['max_images'] }}</span>
            <span>Videos: <strong id="vmmVideoCount">0</strong> / {{ $mediaConfig['max_videos'] }}</span>
        </div>
    </div>

    <div class="card-body">
        <div
            id="variantMediaManager"
            data-existing-media='{{ $existingMedia->toJson() }}'
            data-config='@json($jsConfig)'
            @if ($variant)
                data-delete-url-template="{{ route('admin.catalog.variants.images.destroy', [$variant, '__IMAGE_ID__']) }}"
            @endif
        >
            <div class="vmm-dropzone" id="vmmDropzone" tabindex="0" role="button" aria-label="Upload variant images or videos">
                <i class="ph ph-upload-simple"></i>
                <p>Drag &amp; drop images or videos here, or click to browse</p>
                <small>
                    Images: jpeg, jpg, png, webp (max {{ (int) ($mediaConfig['max_image_kb'] / 1024) }}MB) &middot;
                    Videos: mp4, webm (max {{ (int) ($mediaConfig['max_video_kb'] / 1024) }}MB)
                </small>
            </div>

            <input type="file" id="vmmFileInput" name="new_media[]" accept=".jpeg,.jpg,.png,.webp,.mp4,.webm" multiple style="display:none">

            <div id="vmmError" class="text-danger small mt-2" style="display:none"></div>
            @error('new_media') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            @error('primary_selector') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

            <div class="vmm-gallery" id="vmmGallery"></div>
            <p class="vmm-empty" id="vmmEmptyState" style="display:none">No media uploaded for this variant yet.</p>

            <input type="hidden" name="media_order" id="vmmMediaOrder">
            <input type="hidden" name="primary_selector" id="vmmPrimarySelector">
        </div>
    </div>
</div>
