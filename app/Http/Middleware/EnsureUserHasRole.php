<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage: ->middleware('role:super_admin') or ->middleware('role:customer_admin,customer_staff')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($user->role, $roles, true)) {
            abort(403);
        }

        if (! $user->is_active) {
            abort(403, 'This account has been deactivated.');
        }

        return $next($request);
    }
}
