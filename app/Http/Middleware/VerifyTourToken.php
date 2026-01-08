<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTourToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get tour_code from route OR request body
        $tour_code = $request->route('tour_code') ?? $request->input('tour_code');

        if (!$tour_code) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code is required'
            ], 400);
        }

        // 2. Fetch the booking and latest tour
        $booking = \App\Models\Booking::where('tour_code', $tour_code)->first();
        if (!$booking) {
             $qr = \App\Models\QR::where('code', $tour_code)->first();
             if ($qr && $qr->booking) {
                 $booking = $qr->booking;
             }
        }

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid tour code'
            ], 404);
        }

        $tour = $booking->tours()->latest()->first();
        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour configuration not found'
            ], 404);
        }

        // 3. Generate expected token
        // Formula: proppik + tour_code + created_at(Ymd\THis000000\Z)
        $formattedDate = $tour->created_at->format('Ymd\THis000000\Z');
        $expectedToken = 'proppik' . $tour_code . $formattedDate;

        // 4. Validate X-Tour-Token header
        $providedToken = $request->header('X-Tour-Token');

        if (!$providedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Invalid or missing X-Tour-Token.'
            ], 403);
        }

        return $next($request);
    }
}
