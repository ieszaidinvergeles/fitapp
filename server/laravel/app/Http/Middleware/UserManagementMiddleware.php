<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts route access to member-management roles: admin, manager, or assistant.
 *
 * SRP: Solely responsible for member-management authorisation.
 */
class UserManagementMiddleware
{
    /**
     * Handles an incoming request and aborts with 403 if the user cannot manage members.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canManageMembers()) {
            return response()->json([
                'message' => 'Forbidden. Member-management role required.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
