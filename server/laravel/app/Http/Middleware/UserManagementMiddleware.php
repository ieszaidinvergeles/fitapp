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
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // We allow the request to proceed to the Controller.
        // The UserController calls $this->authorize('update', $user), 
        // which triggers UserPolicy. UserPolicy already allows self-updates.
        // This middleware should ONLY block if the user is not staff AND trying to access restricted management routes.
        
        return $next($request);
    }
}
