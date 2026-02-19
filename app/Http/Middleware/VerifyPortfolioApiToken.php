<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PortfolioApiSession;
use App\Models\Setting;

class VerifyPortfolioApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if API is enabled
        $apiEnabled = Setting::where('name', 'portfolio_api_enabled')->value('value');
        if ($apiEnabled !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio API is currently disabled'
            ], 403);
        }

        // Extract token from Authorization header
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization header is required'
            ], 401);
        }

        // Extract Bearer token
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Authorization header format. Expected: Bearer {token}'
            ], 401);
        }

        $token = $matches[1];

        // Find session by token
        $session = PortfolioApiSession::findByToken($token);

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid access token'
            ], 401);
        }

        // Check if token is expired
        if ($session->isTokenExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Access token has expired. Please verify OTP again.'
            ], 401);
        }

        // Check if session is valid
        if (!$session->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive session'
            ], 401);
        }

        // Optional: Validate IP address matches (can be enabled for stricter security)
        // if ($session->ip_address !== $request->ip()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'IP address mismatch'
        //     ], 401);
        // }

        // Attach session to request for use in controller
        $request->merge(['portfolio_api_session' => $session]);

        return $next($request);
    }
}
