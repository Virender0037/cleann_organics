<?php

use Illuminate\Support\Facades\File;

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
