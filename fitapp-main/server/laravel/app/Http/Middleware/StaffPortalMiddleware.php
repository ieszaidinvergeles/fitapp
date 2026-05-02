<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts route access to staff-portal roles: admin, manager, assistant, or staff.
 *
 * SRP: Solely responsible for staff portal authorisation.
 */
class StaffPortalMiddleware
{
    /**
     * Handles an incoming request and aborts with 403 if the user cannot access the staff portal.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canAccessStaffPortal()) {
            return response()->json([
                'message' => 'Forbidden. Staff portal role required.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
