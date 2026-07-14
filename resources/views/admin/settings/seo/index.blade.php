<x-admin-layout title="SEO Settings">

<main class="pc-container-edit">

    <x-admin.page-header title="SEO Settings" subtitle="Manage website search engine and tracking settings" />

    <x-admin.breadcrumb :items="[['label' => 'Settings'], ['label' => 'SEO']]" />

    @include('admin.partials.alerts')

    <x-admin.form-card title="SEO Information" action="{{ route('admin.settings.seo.update') }}" method="PUT" enctype="multipart/form-data">
        <x-slot:actions>
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-floppy-disk me-1"></i>
                Save SEO Settings
            </button>
        </x-slot:actions>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}">
                @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Canonical URL</label>
                <input type="text" name="canonical_url" class="form-control @error('canonical_url') is-invalid @enderror" value="{{ old('canonical_url', $settings['canonical_url'] ?? '') }}" placeholder="https://example.com">
                @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}" placeholder="organic products, honey, oil">
                @error('meta_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Google Analytics Code</label>
                <input type="text" name="google_analytics_code" class="form-control @error('google_analytics_code') is-invalid @enderror" value="{{ old('google_analytics_code', $settings['google_analytics_code'] ?? '') }}" placeholder="G-XXXXXXXXXX">
                @error('google_analytics_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Google Tag Manager</label>
                <input type="text" name="google_tag_manager" class="form-control @error('google_tag_manager') is-invalid @enderror" value="{{ old('google_tag_manager', $settings['google_tag_manager'] ?? '') }}" placeholder="GTM-XXXXXXX">
                @error('google_tag_manager') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Facebook Pixel ID</label>
                <input type="text" name="facebook_pixel_id" class="form-control @error('facebook_pixel_id') is-invalid @enderror" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}" placeholder="Pixel ID">
                @error('facebook_pixel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Open Graph Image</label>
                <input type="file" name="og_image" class="form-control @error('og_image') is-invalid @enderror" accept="image/*">
                @error('og_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if (! empty($settings['og_image']))
                    <div class="mt-2">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['og_image']) }}" class="rounded border" width="80" height="80" alt="OG Image">
                    </div>
                @endif
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label">Robots.txt</label>
                <textarea name="robots_txt" class="form-control @error('robots_txt') is-invalid @enderror" rows="4">{{ old('robots_txt', $settings['robots_txt'] ?? '') }}</textarea>
                @error('robots_txt') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </x-admin.form-card>

</main>

</x-admin-layout>
