<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the entire /admin route group. Must run after the `auth` middleware
 * (which already redirects guests) — this only rejects an authenticated
 * user whose role isn't superadmin, sending them back to the admin login
 * with a generic message rather than a 403 that could imply the route
 * exists to unauthorized users differently than a normal auth redirect.
 */
class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'superadmin') {
            return redirect()
                ->route('admin.login')
                ->with('error', 'You do not have access to the admin panel.');
        }

        return $next($request);
    }
}
