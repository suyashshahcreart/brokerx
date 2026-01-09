<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\TourMobileValidationHistory;
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

        // Log history: sent
        TourMobileValidationHistory::create([
            'tour_id' => $tour->id,
            'mobile' => $mobile,
            'action' => 'sent',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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

             // Log history: verified
             TourMobileValidationHistory::create([
                 'tour_id' => $tour->id,
                 'mobile' => $mobile,
                 'action' => 'verified',
                 'ip_address' => $request->ip(),
                 'user_agent' => $request->userAgent(),
             ]);

             return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'tour_id' => $tour->id
            ]);
        } else {
             // Log history: failed
             TourMobileValidationHistory::create([
                 'tour_id' => $tour->id,
                 'mobile' => $mobile,
                 'action' => 'failed',
                 'ip_address' => $request->ip(),
                 'user_agent' => $request->userAgent(),
             ]);

             return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }
    }

    /**
     * Get mobile validation history for a tour.
     *
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMobileHistory($tour_code)
    {
        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        // Detailed grouping
        $detailedHistory = TourMobileValidationHistory::where('tour_id', $tour->id)
            ->select(
                'mobile',
                \Illuminate\Support\Facades\DB::raw("COUNT(CASE WHEN action = 'sent' THEN 1 END) as sent_count"),
                \Illuminate\Support\Facades\DB::raw("COUNT(CASE WHEN action = 'verified' THEN 1 END) as verified_count"),
                \Illuminate\Support\Facades\DB::raw("COUNT(CASE WHEN action = 'failed' THEN 1 END) as failed_count"),
                \Illuminate\Support\Facades\DB::raw("MAX(created_at) as last_action_at")
            )
            ->groupBy('mobile')
            ->get();

        return response()->json([
            'success' => true,
            'tour_id' => $tour->id,
            'tour_code' => $tour_code,
            'history' => $detailedHistory
        ]);
    }

    /**
     * Get all necessary data for the tour index.php page (SEO, GTM, JSON, etc.)
     *
     * @param string $tour_code
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTourPageData(Request $request, $tour_code)
    {
        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error || !$tour) {
            return response()->json([
                'success' => false,
                'message' => $error ?: 'Tour not found'
            ], 404);
        }

        // Basic Security Check: Verify token
        // Token is generated based on tour slug and created_at timestamp
        $providedToken = $request->get('token');
        $expectedToken = md5($tour->slug . $tour->created_at . 'tour_secret_2026');

        if ($providedToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Return a structured response optimized for the remote index.php
        return response()->json([
            'success'       => true,
            'bookingStatus' => $booking->status,
            'baseUrl'       => $booking->base_url,
            'tourData'      => [
                'id' => $tour->id,
                'slug' => $tour->slug,
                'is_active' => $tour->is_active,
                'gtm_tag' => $tour->gtm_tag,
                'structured_data' => $tour->structured_data,
                'header_code' => $tour->header_code,
                'footer_code' => $tour->footer_code,
            ],
            'bookingData'   => [
                'id' => $booking->id,
                'status' => $booking->status,
                'base_url' => $booking->base_url,
                'firstname' => $booking->user->firstname ?? '',
                'lastname' => $booking->user->lastname ?? '',
                'qr_code' => $booking->qr->code ?? '',
            ],
            'meta' => [
                'title'       => $tour->meta_title,
                'description' => $tour->meta_description,
                'keywords'    => $tour->meta_keywords,
                'robots'      => $tour->meta_robots,
                'canonical'   => $tour->canonical_url,
                'ogTitle'     => $tour->og_title ?: $tour->meta_title,
                'ogDesc'      => $tour->og_description ?: $tour->meta_description,
                'ogImage'     => $tour->og_image,
                'twitterTitle'=> $tour->twitter_title ?: ($tour->og_title ?: $tour->meta_title),
                'twitterDesc' => $tour->twitter_description ?: ($tour->og_description ?: $tour->meta_description),
                'twitterImage'=> $tour->twitter_image ?: $tour->og_image,
                'gtmCode'     => $tour->gtm_tag,
                'headerCode'  => $tour->header_code,
                'footerCode'  => $tour->footer_code
            ]
        ]);
    }
}
