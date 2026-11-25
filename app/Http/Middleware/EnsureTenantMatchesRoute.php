<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defense in depth behind the Policies: for any route-model-bound parameter
 * that carries a customer_id, a non-Super-Admin user may never pass through
 * to a record outside their own tenant — even if a policy check was missed.
 */
class EnsureTenantMatchesRoute
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($request->route()->parameters() as $parameter) {
            if ($parameter instanceof Model
                && array_key_exists('customer_id', $parameter->getAttributes())
                && (int) $parameter->getAttribute('customer_id') !== (int) $user->customer_id) {
                abort(404);
            }
        }

        return $next($request);
    }
}
