<?php

namespace App\Http\Controllers\Admin\Api;


use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $customers = User::role('customer')->get(['id', 'firstname', 'lastname', 'email', 'mobile']);
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
        $bookingIds = Booking::where('user_id', $data['user_id'])->pluck('id');
        // Get tours for these bookings
        $tours = Tour::whereIn('booking_id', $bookingIds)->with('booking')->get();
        // Map tours to include full logo URLs
        $tours = $tours->map(function ($tour) {
            // QR Code
            $tour->qr_code = $tour->booking ? $tour->booking->tour_code : null;
            $tour->qr_link = $tour->booking ? $tour->booking->tour_code ? "https://qr.proppik.com/" . $tour->qr_code : null : null;
            $tour->s3_link = $tour->booking ? $tour->booking->tour_code ? "https://creartimages.s3.ap-south-1.amazonaws.com/tours/" . $tour->qr_code . "/" : null : null;
            
            $tour->top_image = $tour->footer_logo;
            $tour->contact_number  = $tour->footer_mobile;
            $tour->top_title  = $tour->footer_name;
            $tour->contact_email  = $tour->footer_email;
            $tour->top_sub_title  = $tour->footer_subtitle;
            $tour->top_description  = $tour->footer_decription;

            $tour->is_hosted = $tour->is_hosted ?? false;
            $tour->hosted_link = $tour->hosted_link ?? null;
            
            
            $tour->makeHidden(['booking']);
            $tour->makeVisible(['qr_code']);
            $tourArr = $tour->toArray();
            // Add full URLs for custom logos
            $tourArr['custom_logo_sidebar_url'] = $tour->custom_logo_sidebar ? Storage::disk('s3')->url($tour->custom_logo_sidebar) : null;
            $tourArr['custom_logo_footer_url'] = $tour->custom_logo_footer ? Storage::disk('s3')->url($tour->custom_logo_footer) : null;
            return $tourArr;
        });
        return response()->json([
            'success' => true,
            'tours' => $tours
        ]);
    }

    /**
     * Update working_json field for a specific tour (stores as JSON)
     */
    public function updateWorkingJson(Request $request, $tour_id)
    {
        // Read raw content and parse JSON
        $rawContent = $request->getContent();
        $workingJson = null;
        $userId = null;
        
        if (!empty($rawContent)) {
            $parsedContent = json_decode($rawContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                $workingJson = $parsedContent['working_json'] ?? null;
                $userId = $parsedContent['working_json_last_update_user'] ?? null;
            }
        }
        
        // Fallback: try regular input
        if ($workingJson === null) {
            $workingJson = $request->input('working_json');
        }
        if ($userId === null) {
            $userId = $request->input('working_json_last_update_user');
        }
        
        // Validate both fields are required
        $errors = [];
        if ($workingJson === null || $workingJson === '') {
            $errors['working_json'] = ['The working json field is required.'];
        }
        if ($userId === null || $userId === '') {
            $errors['working_json_last_update_user'] = ['The working json last update user field is required.'];
        }
        
        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }
        
        // Validate user exists
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => [
                    'working_json_last_update_user' => ['The specified user does not exist.']
                ]
            ], 422);
        }
        
        // Find tour
        $tour = Tour::find($tour_id);
        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }
        
        // If working_json is a string, try to decode it as JSON
        if (is_string($workingJson)) {
            $decoded = json_decode($workingJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $workingJson = $decoded;
            }
        }
        
        // Update both fields
        $tour->working_json = $workingJson;
        $tour->working_json_last_update_user = $userId;
        $tour->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Working JSON updated successfully',
            'tour' => [
                'id' => $tour->id,
                'working_json' => $tour->working_json,
                'working_json_last_update_user' => $tour->working_json_last_update_user
            ]
        ]);
    }
}
