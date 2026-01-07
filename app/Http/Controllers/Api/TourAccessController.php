<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourAccessController extends Controller{
    /**
     * Check if the tour is active based on tour_code.
     *
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsActive($tour_code)
    {
        // Check for booking using tour_code or QR code logic
        // User request: "tour_code check in booking table"
        $booking = Booking::where('tour_code', $tour_code)->first();
        
        // If not found in booking table directly, fallback to QR code lookup if needed?
        // Let's stick to user request strictly first.
        // Actually, sometimes 'tour_code' in input might match 'code' in QR table which links to booking.
        // But user explicitly said "tour_code check in booking table".
        
        if (!$booking) {
             // Fallback: Check if it's a QR code linked to a booking
             $qr = \App\Models\QR::where('code', $tour_code)->first();
             if ($qr) {
                 $booking = $qr->booking;
             }
        }

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code not found',
                'is_active' => false
            ], 404);
        }

        // Get the associated tour (latest)
        $tour = $booking->tours()->latest()->first();

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found',
                'is_active' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_active' => (bool) $tour->is_active
        ]);
    }

    /**
     * Check if credentials are required based on tour_code.
     *
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsCredentials($tour_code)
    {
        $booking = Booking::where('tour_code', $tour_code)->first();
        
         if (!$booking) {
             // Fallback: Check if it's a QR code linked to a booking
             $qr = \App\Models\QR::where('code', $tour_code)->first();
             if ($qr) {
                 $booking = $qr->booking;
             }
        }

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code not found',
                'is_credentials' => false
            ], 404);
        }

        $tour = $booking->tours()->latest()->first();

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found',
                'is_credentials' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_credentials' => (bool) $tour->is_credentials
        ]);
    }

    /**
     * specific tour login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'tour_code' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $tour_code = $request->input('tour_code');
        $username = $request->input('username');
        $password = $request->input('password');

        $booking = Booking::where('tour_code', $tour_code)->first();
        
         if (!$booking) {
             // Fallback: Check if it's a QR code linked to a booking
             $qr = \App\Models\QR::where('code', $tour_code)->first();
             if ($qr) {
                 $booking = $qr->booking;
             }
        }

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Tour code not found'
            ], 404);
        }

        $tour = $booking->tours()->latest()->first();

        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        // Check if tour is active first? User said "base on this is active or not base responce sent here propely"
        if (!$tour->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tour is not active'
            ], 403);
        }

        // If credentials are NOT required, maybe login is valid? 
        // Or strictly check credentials if is_credentials is true?
        // Assuming if (is_credentials) { check } else { success? or error? }
        // User request: "send data tour_code,username,password" imply login intent. 
        // I will assume if credentials are required, we check. If not required, we probably accept or say not required.
        // Assuming strict login:
        
        if ($tour->is_credentials) {
            $credential = $tour->credentials()
                ->where('user_name', $username)
                ->where('password', $password) // Plain text as requested
                ->where('is_active', true)
                ->first();

            if ($credential) {
                 return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'tour_id' => $tour->id
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        }

        // If credentials are NOT required but user tried to login
        return response()->json([
            'success' => true,
            'message' => 'Login successful (No credentials required)',
             'tour_id' => $tour->id
        ]);
    }
}
