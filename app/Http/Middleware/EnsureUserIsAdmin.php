<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard all admin routes.
 *
 * Allows only authenticated users whose role column equals 'admin'.
 * Any other authenticated user receives a 403 Forbidden response.
 * Unauthenticated users will be caught by the 'auth' middleware that
 * must always be applied before (or alongside) this one.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && $request->user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized Access: Admin privileges required.');
    }
}
