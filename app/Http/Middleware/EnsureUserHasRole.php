<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard routes by one or more allowed roles.
 *
 * Usage in route definitions:
 *   ->middleware('role:instruktur')         // single role
 *   ->middleware('role:admin,instruktur')   // multiple roles (OR logic)
 *
 * Any authenticated user whose role is NOT in the allowed set receives
 * a 403 Forbidden response.  Unauthenticated users will be caught by
 * the 'auth' middleware that must always be applied before this one.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (auth()->check() && in_array($request->user()->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized Access: Insufficient role privileges.');
    }
}
