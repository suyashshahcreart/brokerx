<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Services\SmsService;

class TourAccessController extends Controller{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    /**
     * Find booking and its latest tour by tour_code.
     *
     * @param string $tour_code
     * @return array An array containing [Booking $booking|null, Tour $tour|null, string $error_message|null]
     */
    private function getBookingAndTour($tour_code)
    {
        $booking = Booking::where('tour_code', $tour_code)->first();
        
        if (!$booking) {
            $qr = \App\Models\QR::where('code', $tour_code)->first();
            if ($qr) {
                $booking = $qr->booking;
            }
        }

        if (!$booking) {
            return [null, null, 'Tour code not found'];
        }

        $tour = $booking->tours()->latest()->first();

        if (!$tour) {
            return [$booking, null, 'Tour not found'];
        }

        return [$booking, $tour, null];
    }

    /**
     * Check if the tour is active based on tour_code.
     *
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsActive($tour_code)
    {
        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
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
        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
                'is_credentials' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_credentials' => (bool) $tour->is_credentials
        ]);
    }

    /**
     * Check if mobile validation is required based on tour_code.
     *
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIsMobileValidation($tour_code)
    {
        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
                'is_mobile_validation' => false
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_mobile_validation' => (bool) $tour->is_mobile_validation
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

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
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

        return response()->json([
            'success' => true,
            'message' => 'Login successful (No credentials required)',
             'tour_id' => $tour->id
        ]);
    }

    /**
     * Send OTP for mobile validation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'tour_code' => 'required|string',
            'mobile' => 'required|string', // Basic validation, add regex if needed
        ]);

        $tour_code = $request->input('tour_code');
        $mobile = $request->input('mobile');

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

       

        // Generate OTP (e.g., 4 digits)
        $otp = rand(100000, 999999);
        
        // Save to DB
        // Check if entry exists for this tour and mobile
        $validation = \App\Models\TourMobileValidation::where('tour_id', $tour->id)
            ->where('mobile', $mobile)
            ->first();

        if ($validation) {
            $validation->update([
                'otp' => $otp,
                'otp_expired_at' => now()->addMinutes(10),
            ]);
        } else {
            \App\Models\TourMobileValidation::create([
                'tour_id' => $tour->id,
                'mobile' => $mobile,
                'otp' => $otp,
                'otp_expired_at' => now()->addMinutes(10),
            ]);
        }

        // Send OTP via SMS
        $mobileWithCountryCode = '91' . $mobile;
        $templateKey = 'login_otp'; // Using login_otp template as preferred for tour access

        try {
            $this->smsService->send(
                $mobileWithCountryCode,
                $templateKey,
                ['OTP' => $otp],
                [
                    'type' => 'manual',
                    'reference_type' => Tour::class,
                    'reference_id' => $tour->id,
                    'notes' => 'Tour Mobile Validation OTP'
                ]
            );
            $smsSent = true;
        } catch (\Exception $e) {
            \Log::error('Failed to send Tour OTP SMS: ' . $e->getMessage());
            $smsSent = false;
        }

        return response()->json([
            'success' => true, 
            'message' => 'OTP sent successfully',
            'sms_sent' => $smsSent,
            'debug_otp' => $otp // REMOVE IN PRODUCTION
        ]);
    }

    /**
     * Verify OTP for mobile validation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'tour_code' => 'required|string',
            'mobile' => 'required|string',
            'otp' => 'required|string',
        ]);

        $tour_code = $request->input('tour_code');
        $mobile = $request->input('mobile');
        $otp = $request->input('otp');

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        $validation = \App\Models\TourMobileValidation::where('tour_id', $tour->id)
            ->where('mobile', $mobile)
            ->where('otp', $otp)
            ->where('otp_expired_at', '>', now())
            ->first();

        if ($validation) {
             // Clear OTP after successful verification? 
             // Or keep it to allow re-entry for a session duration?
             // Usually we generate a token. But for now just success.
             $validation->update(['otp' => null, 'otp_expired_at' => null]);

             return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'tour_id' => $tour->id
            ]);
        } else {
             return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }
    }
}
