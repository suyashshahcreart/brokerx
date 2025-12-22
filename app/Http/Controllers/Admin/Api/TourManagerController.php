<?php

namespace App\Http\Controllers\Admin\Api;


use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class TourManagerController extends Controller
{
    /**
     * Handle the login request for tour manager (static response for now)
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generate a token (using Laravel Sanctum if available)
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken('tour_manager_api')->plainTextToken;
        } else {
            $token = base64_encode(bin2hex(random_bytes(32)));
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Get all users with role 'customer'
     */
    public function getCustomers(Request $request)
    {
        $customers = User::role('customer')->get(['id', 'firstname','lastname','email','mobile']);
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Get all tours for a given customer (user_id) via bookings
     */
    public function getToursByCustomer(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);
        // Get bookings for this user
        $bookingIds = \App\Models\Booking::where('user_id', $data['user_id'])->pluck('id');
        // Get tours for these bookings
        $tours = Tour::whereIn('booking_id', $bookingIds)->get();
        return response()->json([
            'success' => true,
            'tours' => $tours
        ]);
    }
}
