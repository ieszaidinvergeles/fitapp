<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts route access to users holding the admin role.
 *
 * SRP: Solely responsible for admin role authorisation.
 * OCP: New role checks are added via separate middleware, not by modifying this class.
 */
class AdminMiddleware
{
    /**
     * Handles an incoming request and aborts with 403 if the user is not admin.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden. Admin role required.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
