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
     * Parse mobile number to extract country code, country name, and base mobile.
     *
     * @param string $mobile
     * @return array ['country_code' => string, 'country_name' => string, 'base_mobile' => string]
     */
    private function parseMobileNumber($mobile)
    {
        // Remove + if present
        $cleanMobile = ltrim($mobile, '+');
        
        // Common country codes mapping (most common ones)
        $countryCodes = [
            '1' => ['name' => 'United States/Canada', 'min_length' => 10, 'max_length' => 10],
            '91' => ['name' => 'India', 'min_length' => 10, 'max_length' => 10],
            '44' => ['name' => 'United Kingdom', 'min_length' => 10, 'max_length' => 10],
            '86' => ['name' => 'China', 'min_length' => 11, 'max_length' => 11],
            '81' => ['name' => 'Japan', 'min_length' => 10, 'max_length' => 10],
            '49' => ['name' => 'Germany', 'min_length' => 10, 'max_length' => 11],
            '33' => ['name' => 'France', 'min_length' => 9, 'max_length' => 9],
            '39' => ['name' => 'Italy', 'min_length' => 9, 'max_length' => 10],
            '34' => ['name' => 'Spain', 'min_length' => 9, 'max_length' => 9],
            '61' => ['name' => 'Australia', 'min_length' => 9, 'max_length' => 9],
            '7' => ['name' => 'Russia/Kazakhstan', 'min_length' => 10, 'max_length' => 10],
            '971' => ['name' => 'United Arab Emirates', 'min_length' => 9, 'max_length' => 9],
            '966' => ['name' => 'Saudi Arabia', 'min_length' => 9, 'max_length' => 9],
            '65' => ['name' => 'Singapore', 'min_length' => 8, 'max_length' => 8],
            '60' => ['name' => 'Malaysia', 'min_length' => 9, 'max_length' => 10],
            '62' => ['name' => 'Indonesia', 'min_length' => 9, 'max_length' => 11],
            '84' => ['name' => 'Vietnam', 'min_length' => 9, 'max_length' => 10],
            '66' => ['name' => 'Thailand', 'min_length' => 9, 'max_length' => 9],
            '63' => ['name' => 'Philippines', 'min_length' => 10, 'max_length' => 10],
            '82' => ['name' => 'South Korea', 'min_length' => 9, 'max_length' => 10],
            '852' => ['name' => 'Hong Kong', 'min_length' => 8, 'max_length' => 8],
            '853' => ['name' => 'Macau', 'min_length' => 8, 'max_length' => 8],
            '886' => ['name' => 'Taiwan', 'min_length' => 9, 'max_length' => 9],
            '880' => ['name' => 'Bangladesh', 'min_length' => 10, 'max_length' => 10],
            '92' => ['name' => 'Pakistan', 'min_length' => 10, 'max_length' => 10],
            '94' => ['name' => 'Sri Lanka', 'min_length' => 9, 'max_length' => 9],
            '977' => ['name' => 'Nepal', 'min_length' => 10, 'max_length' => 10],
            '95' => ['name' => 'Myanmar', 'min_length' => 8, 'max_length' => 10],
            '855' => ['name' => 'Cambodia', 'min_length' => 8, 'max_length' => 9],
            '856' => ['name' => 'Laos', 'min_length' => 8, 'max_length' => 10],
            '673' => ['name' => 'Brunei', 'min_length' => 7, 'max_length' => 7],
        ];
        
        // Try to match country code (check longer codes first)
        $countryCode = null;
        $countryName = null;
        $baseMobile = null;
        
        // Sort by length (descending) to check longer codes first
        $sortedCodes = array_keys($countryCodes);
        usort($sortedCodes, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($sortedCodes as $code) {
            if (strpos($cleanMobile, $code) === 0) {
                $countryCode = $code;
                $countryName = $countryCodes[$code]['name'];
                $baseMobile = substr($cleanMobile, strlen($code));
                break;
            }
        }
        
        // If no country code found, try to extract (assume first 1-3 digits are country code)
        if (!$countryCode && strlen($cleanMobile) >= 10) {
            // Try 1-digit country code (e.g., USA/Canada)
            if (strlen($cleanMobile) >= 11 && $cleanMobile[0] === '1') {
                $countryCode = '1';
                $countryName = 'United States/Canada';
                $baseMobile = substr($cleanMobile, 1);
            }
            // Try 2-digit country code
            elseif (strlen($cleanMobile) >= 12) {
                $countryCode = substr($cleanMobile, 0, 2);
                $countryName = 'Unknown';
                $baseMobile = substr($cleanMobile, 2);
            }
            // Try 3-digit country code
            elseif (strlen($cleanMobile) >= 13) {
                $countryCode = substr($cleanMobile, 0, 3);
                $countryName = 'Unknown';
                $baseMobile = substr($cleanMobile, 3);
            }
            // Default: assume first 2 digits
            else {
                $countryCode = substr($cleanMobile, 0, 2);
                $countryName = 'Unknown';
                $baseMobile = substr($cleanMobile, 2);
            }
        }
        
        return [
            'country_code' => $countryCode ?? null,
            'country_name' => $countryName ?? 'Unknown',
            'base_mobile' => $baseMobile ?? $cleanMobile,
        ];
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
            'mobile' => [
                'required',
                'string',
                'regex:/^(\+?[1-9]\d{1,14}|\d{10,15})$/',
                function ($attribute, $value, $fail) {
                    // Remove + if present for validation
                    $cleanMobile = ltrim($value, '+');
                    
                    // Check if it's just a local number (starts with 0 or doesn't have country code)
                    if (preg_match('/^0\d+$/', $cleanMobile) || (strlen($cleanMobile) < 10)) {
                        $fail('The mobile number must include country code. Format: +[country code][number] or [country code][number] (e.g., +911234567890 or 911234567890)');
                    }
                    
                    // Check if it looks like a local number without country code (10 digits starting with non-zero)
                    if (preg_match('/^[1-9]\d{9}$/', $cleanMobile)) {
                        $fail('The mobile number must include country code. Format: +[country code][number] or [country code][number] (e.g., +911234567890 or 911234567890)');
                    }
                },
            ],
        ]);

        $tour_code = $request->input('tour_code');
        $mobile = $request->input('mobile');

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        // Normalize mobile number (remove + if present, keep as is)
        $normalizedMobile = ltrim($mobile, '+');
        
        // Parse mobile number to extract country code, country name, and base mobile
        $parsedMobile = $this->parseMobileNumber($mobile);

        // Generate OTP (e.g., 6 digits)
        $otp = rand(100000, 999999);
        
        // Save to DB
        // Check if entry exists for this tour and mobile
        $validation = \App\Models\TourMobileValidation::where('tour_id', $tour->id)
            ->where('mobile', $normalizedMobile)
            ->first();

        if ($validation) {
            $validation->update([
                'base_mobile' => $parsedMobile['base_mobile'],
                'country_code' => $parsedMobile['country_code'],
                'country_name' => $parsedMobile['country_name'],
                'otp' => $otp,
                'otp_expired_at' => now()->addMinutes(10),
            ]);
        } else {
            \App\Models\TourMobileValidation::create([
                'tour_id' => $tour->id,
                'mobile' => $normalizedMobile,
                'base_mobile' => $parsedMobile['base_mobile'],
                'country_code' => $parsedMobile['country_code'],
                'country_name' => $parsedMobile['country_name'],
                'otp' => $otp,
                'otp_expired_at' => now()->addMinutes(10),
            ]);
        }

        // Log history: sent
        TourMobileValidationHistory::create([
            'tour_id' => $tour->id,
            'mobile' => $normalizedMobile,
            'action' => 'sent',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Send OTP via SMS (mobile already includes country code)
        $templateKey = 'login_otp'; // Using login_otp template as preferred for tour access

        try {
            $this->smsService->send(
                $normalizedMobile,
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
            // 'debug_otp' => $otp // REMOVE IN PRODUCTION
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
            'mobile' => [
                'required',
                'string',
                'regex:/^(\+?[1-9]\d{1,14}|\d{10,15})$/',
                function ($attribute, $value, $fail) {
                    // Remove + if present for validation
                    $cleanMobile = ltrim($value, '+');
                    
                    // Check if it's just a local number (starts with 0 or doesn't have country code)
                    if (preg_match('/^0\d+$/', $cleanMobile) || (strlen($cleanMobile) < 10)) {
                        $fail('The mobile number must include country code. Format: +[country code][number] or [country code][number] (e.g., +911234567890 or 911234567890)');
                    }
                    
                    // Check if it looks like a local number without country code (10 digits starting with non-zero)
                    if (preg_match('/^[1-9]\d{9}$/', $cleanMobile)) {
                        $fail('The mobile number must include country code. Format: +[country code][number] or [country code][number] (e.g., +911234567890 or 911234567890)');
                    }
                },
            ],
            'otp' => 'required|string|size:6',
        ]);

        $tour_code = $request->input('tour_code');
        $mobile = $request->input('mobile');
        $otp = $request->input('otp');

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        // Normalize mobile number (remove + if present, keep as is)
        $normalizedMobile = ltrim($mobile, '+');
        
        // Parse mobile number to extract country code, country name, and base mobile
        $parsedMobile = $this->parseMobileNumber($mobile);

        $validation = \App\Models\TourMobileValidation::where('tour_id', $tour->id)
            ->where('mobile', $normalizedMobile)
            ->where('otp', $otp)
            ->where('otp_expired_at', '>', now())
            ->first();

        if ($validation) {
             // Clear OTP after successful verification and update parsed mobile data if missing
             $updateData = [
                 'otp' => null, 
                 'otp_expired_at' => null
             ];
             
             // Update parsed mobile data if not already set (for backward compatibility)
             if (empty($validation->base_mobile) || empty($validation->country_code)) {
                 $updateData['base_mobile'] = $parsedMobile['base_mobile'];
                 $updateData['country_code'] = $parsedMobile['country_code'];
                 $updateData['country_name'] = $parsedMobile['country_name'];
             }
             
             $validation->update($updateData);

             // Log history: verified
             TourMobileValidationHistory::create([
                 'tour_id' => $tour->id,
                 'mobile' => $normalizedMobile,
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
     * Verify API token for security
     *
     * @param Request $request
     * @return bool
     */
    private function verifyApiToken(Request $request)
    {
        $providedToken = $request->header('X-API-Token') ?? $request->get('token');
        $expectedToken = env('API_SECRET_TOKEN', md5('proppik_api_secret_2026'));
        
        return $providedToken === $expectedToken;
    }

    /**
     * Get mobile validation history for a tour.
     *
     * @param Request $request
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMobileHistory(Request $request, $tour_code)
    {
        // Verify API token
        if (!$this->verifyApiToken($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Invalid token.'
            ], 403);
        }

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
     * Get all bookings list with basic information
     * tour_code is compulsory in response
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBookingsList(Request $request)
    {
        // Verify API token
        if (!$this->verifyApiToken($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Invalid token.'
            ], 403);
        }

        // Get bookings with basic information
        $bookings = Booking::with(['user:id,firstname,lastname,email,mobile'])
            ->select([
                'id',
                'user_id',
                'tour_code',
                'status',
                'payment_status',
                'booking_date',
                'created_at',
                'updated_at'
            ])
            ->whereNotNull('tour_code')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'booking_id' => $booking->id,
                    'tour_code' => $booking->tour_code,
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                    'user' => [
                        'id' => $booking->user->id ?? null,
                        'firstname' => $booking->user->firstname ?? '',
                        'lastname' => $booking->user->lastname ?? '',
                        'email' => $booking->user->email ?? '',
                        'mobile' => $booking->user->mobile ?? '',
                    ],
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $booking->updated_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'total' => $bookings->count(),
            'bookings' => $bookings
        ]);
    }

    /**
     * Get booking details by tour_code with additional information
     *
     * @param Request $request
     * @param string $tour_code
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingByTourCode(Request $request, $tour_code)
    {
        // Verify API token
        if (!$this->verifyApiToken($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Invalid token.'
            ], 403);
        }

        [$booking, $tour, $error] = $this->getBookingAndTour($tour_code);

        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 404);
        }

        // Eager load relationships to avoid N+1 queries
        $booking->load([
            'user:id,firstname,lastname,email,mobile',
            'propertyType:id,name',
            'propertySubType:id,name',
            'bhk:id,name',
            'city:id,name',
            'state:id,name',
            'qr:id,booking_id,code',
            'tours' => function($query) {
                $query->latest()->limit(1);
            }
        ]);

        // Get latest tour
        $latestTour = $booking->tours->first();

        // Build comprehensive booking data
        $bookingData = [
            'booking_id' => $booking->id,
            'tour_code' => $booking->tour_code,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'base_url' => $booking->base_url,
            'tour_final_link' => $booking->tour_final_link,
            'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
            'booking_time' => $booking->booking_time,
            'booking_notes' => $booking->booking_notes,
            'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $booking->updated_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $booking->user->id ?? null,
                'firstname' => $booking->user->firstname ?? '',
                'lastname' => $booking->user->lastname ?? '',
                'email' => $booking->user->email ?? '',
                'mobile' => $booking->user->mobile ?? '',
            ],
            'property' => [
                'property_type' => $booking->propertyType->name ?? null,
                'property_sub_type' => $booking->propertySubType->name ?? null,
                'bhk' => $booking->bhk->name ?? null,
                'area' => $booking->area,
                'price' => $booking->price,
                'furniture_type' => $booking->furniture_type,
                'address' => [
                    'house_no' => $booking->house_no,
                    'building' => $booking->building,
                    'society_name' => $booking->society_name,
                    'address_area' => $booking->address_area,
                    'landmark' => $booking->landmark,
                    'full_address' => $booking->full_address,
                    'pin_code' => $booking->pin_code,
                    'city' => $booking->city->name ?? null,
                    'state' => $booking->state->name ?? null,
                ],
            ],
            'tour' => null,
        ];

        // Add tour information if available
        if ($latestTour) {
            $bookingData['tour'] = [
                'tour_id' => $latestTour->id,
                'tour_name' => $latestTour->name,
                'tour_title' => $latestTour->title,
                'tour_slug' => $latestTour->slug,
                'location' => $latestTour->location,
                'status' => $latestTour->status,
                'is_active' => (bool) $latestTour->is_active,
                'is_credentials' => (bool) $latestTour->is_credentials,
                'is_mobile_validation' => (bool) $latestTour->is_mobile_validation,
                'is_hosted' => (bool) $latestTour->is_hosted,
                'hosted_link' => $latestTour->hosted_link,
                'tour_live_url' => $latestTour->getTourLiveUrl(),
                'created_at' => $latestTour->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $latestTour->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // Add QR code if available
        if ($booking->qr) {
            $bookingData['qr_code'] = $booking->qr->code;
        }

        return response()->json([
            'success' => true,
            'booking' => $bookingData
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

        // Get tour by slug and eager load booking with only tour_code field
        $tour = Tour::with(['booking' => function($query) {
            $query->select('id', 'tour_code');
        }])->where('slug', $tour_code)->first();
        $tour_code = $tour?->booking?->tour_code;

        

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
