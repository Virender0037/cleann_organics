<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_image_url')) {
    /**
     * Resolves a `public` disk path (product image, blog featured image,
     * category/testimonial/logo upload, etc.) to its public URL — but only
     * when the file actually exists on disk. A database row can reference a
     * path whose file was never uploaded here (a DB restored/copied without
     * its media, or a file deleted without cleaning up the row); without
     * this check that produces a URL that 404s and renders as a broken-image
     * icon in the browser. $fallback (an asset() path, e.g. a bundled
     * placeholder image) is returned instead whenever $path is empty or the
     * file is missing.
     */
    function storage_image_url(?string $path, string $fallback): string
    {
        if (! $path) {
            return $fallback;
        }

        return Storage::disk('public')->exists($path)
            ? Storage::url($path)
            : $fallback;
    }
}

if (! function_exists('admin_asset')) {
    /**
     * asset() for the admin panel's static CSS/JS, with a filemtime()-based
     * cache-busting query string so browsers refetch the file whenever its
     * content changes on disk (rather than trusting the long Cache-Control
     * max-age served for /public files). Falls back to a plain asset() URL
     * if the file is missing so a bad path never raises a PHP warning.
     */
    function admin_asset(string $path): string
    {
        $absolute = public_path($path);

        if (! File::exists($absolute)) {
            return asset($path);
        }

        return asset($path).'?v='.File::lastModified($absolute);
    }
}
