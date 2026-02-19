<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockCustomerRole
{
    /**
     * Handle an incoming request.
     * Block only users who have ONLY the customer role (no other roles).
     * Users with customer role + other roles (like admin) can still access.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $userRoles = $user->roles->pluck('name')->toArray();
            
            // Block only if user has ONLY customer role (no other roles)
            if (count($userRoles) === 1 && in_array('customer', $userRoles)) {
                return redirect()->route('frontend.index');
            }
        }

        return $next($request);
    }
}

