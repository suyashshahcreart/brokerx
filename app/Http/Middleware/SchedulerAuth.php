<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * SchedulerAuth Middleware
 * 
 * Protects routes that require scheduler authentication.
 * Schedulers use session-based authentication (scheduler_id in session)
 * instead of the standard Laravel auth system.
 */
class SchedulerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if scheduler is authenticated via session
        if (!Session::has('scheduler_id')) {
            // Redirect to scheduler login if not authenticated
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login as a scheduler.'
                ], 401);
            }

            return redirect()->route('schedulers.login')
                ->with('error', 'Please login to continue.');
        }

        return $next($request);
    }
}
