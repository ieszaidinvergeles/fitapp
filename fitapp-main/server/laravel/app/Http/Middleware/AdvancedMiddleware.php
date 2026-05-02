<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts route access to users holding an advanced role: admin, manager, or staff.
 *
 * SRP: Solely responsible for advanced role authorisation.
 * OCP: New role checks are added via separate middleware, not by modifying this class.
 */
class AdvancedMiddleware
{
    /**
     * Handles an incoming request and aborts with 403 if the user is not advanced.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdvanced()) {
            return response()->json([
                'message' => 'Forbidden. Advanced role required.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
