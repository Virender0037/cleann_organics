<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stops the browser from bfcache-restoring a protected admin page after
 * logout (e.g. pressing Back would otherwise show a stale, client-side
 * snapshot of the last authenticated page — no live data is re-fetched
 * and no session is reused, but the cached HTML is still visible until
 * this header forces a fresh request instead of a cache restore).
 */
class PreventAdminResponseCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
