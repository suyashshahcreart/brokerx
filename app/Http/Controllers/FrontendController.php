<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\BHK;
use App\Models\PropertyType;
use App\Models\PropertySubType;
use App\Models\Setting;
use App\Services\CashfreeService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class FrontendController extends Controller
{
    protected CashfreeService $cashfree;
    protected SmsService $smsService;

    public function __construct(CashfreeService $cashfree, SmsService $smsService)
    {
        $this->cashfree = $cashfree;
        $this->smsService = $smsService;
    }
    public function index()
    {
        // Get base price for display on landing page
        $basePrice = (int) (Setting::where('name', 'base_price')->value('value') ?? 599);
        
        return view('frontend.index', compact('basePrice'));
    }

    public function setup(Request $request)
    {
        // If user is logged in and has bookings, always redirect to booking dashboard first
        // They can only access setup page from the "New Booking" button in dashboard
        if (Auth::check()) {
            $hasBookings = Booking::where('user_id', Auth::id())->exists();
            if ($hasBookings && !$request->has('force_new')) {
                return redirect()->route('frontend.booking-dashboard');
            }
        }
        
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id','name','icon']);
        $states = State::with(['cities:id,state_id,name'])->get(['id','name','code']);
        $cities = City::get(['id','name','state_id']);
        $bhk = BHK::all();
        $hasBookings = Auth::check() ? Booking::where('user_id', Auth::id())->exists() : false;
        return view('frontend.setup', [
            'propTypes' => $types,
            'states' => $states,
            'cities' => $cities,
            'bhk' => $bhk,
            'hasBookings' => $hasBookings,
        ]);
    }

    /**
     * Check if user exists and send OTP
     * Creates new user if doesn't exist, generates and saves OTP to user table
     */
    public function checkUserAndSendOtp(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
                'name' => ['required', 'string', 'max:255'],
            ]);

            $mobile = $validated['mobile'];
            $name = trim($validated['name']);

            // Check if user exists by mobile
            $user = User::where('mobile', $mobile)->first();
            $isNewUser = false;
            $userStatus = 'existing';

            if (!$user) {
                // User doesn't exist - create new user
                $isNewUser = true;
                $userStatus = 'new';
                
                // Parse full name into firstname and lastname
                $nameParts = explode(' ', $name, 2);
                $firstname = $nameParts[0];
                $lastname = $nameParts[1] ?? '';

                // Create user with minimal data
                $user = User::create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'mobile' => $mobile,
                ]);

                // Assign customer role
                $customerRole = Role::firstOrCreate(['name' => 'customer']);
                $user->assignRole($customerRole);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Save OTP to user table with 5 minute expiry
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            // Send OTP via SMS
            // Format mobile with country code (91 for India)
            $mobileWithCountryCode = '91' . $mobile;
            
            // Determine template: registration_otp for new users, login_otp for existing
            $templateKey = $isNewUser ? 'registration_otp' : 'login_otp';
            
            // Track whether SMS was sent successfully
            $smsSent = false;
            
            // Try to send OTP via SMS (silently fail if gateway is disabled)
            try {
                $this->smsService->send(
                    $mobileWithCountryCode,
                    $templateKey,
                    ['OTP' => $otp],
                    [
                        'type' => 'manual',
                        'reference_type' => \App\Models\User::class,
                        'reference_id' => $user->id,
                        'notes' => $isNewUser ? 'Registration OTP' : 'Login OTP - Setup Page'
                    ]
                );
                
                $smsSent = true;
                
                Log::info('✅ OTP SMS sent successfully', [
                    'mobile' => $mobile,
                    'template' => $templateKey,
                    'is_new_user' => $isNewUser,
                    'user_id' => $user->id,
                ]);
            } catch (\RuntimeException $e) {
                // Check if error is about SMS gateway not being enabled
                $errorMessage = $e->getMessage();
                if (stripos($errorMessage, 'not enabled') !== false || 
                    stripos($errorMessage, 'not configured') !== false ||
                    stripos($errorMessage, 'no active') !== false) {
                    
                    // SMS gateway is disabled - log silently and continue workflow
                    Log::info('⚠️ SMS Gateway not enabled - OTP not sent via SMS', [
                        'mobile' => $mobile,
                        'user_id' => $user->id,
                        'otp' => $otp, // Log OTP for reference
                    ]);
                    
                    // In development, log OTP for testing
                    if (config('app.debug')) {
                        Log::info('📱 OTP (SMS Gateway Disabled - Development): ' . $otp);
                    }
                } else {
                    // Other SMS errors - log but continue workflow
                    Log::warning('⚠️ Failed to send OTP SMS - continuing without SMS', [
                        'mobile' => $mobile,
                        'template' => $templateKey,
                        'error' => $errorMessage,
                        'user_id' => $user->id,
                    ]);
                    
                    // In development, log OTP for testing
                    if (config('app.debug')) {
                        Log::info('📱 OTP (SMS Failed - Development): ' . $otp);
                    }
                }
            } catch (\Exception $e) {
                // Log unexpected errors but continue workflow
                Log::warning('⚠️ Unexpected error sending OTP SMS - continuing without SMS', [
                    'mobile' => $mobile,
                    'template' => $templateKey,
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                
                // In development, log OTP for testing
                if (config('app.debug')) {
                    Log::info('📱 OTP (Unexpected Error - Development): ' . $otp);
                }
            }

            // Return success response with SMS status
            return response()->json([
                'success' => true,
                'message' => $isNewUser 
                    ? 'Account created! OTP sent to your mobile number.' 
                    : 'OTP sent to your registered mobile number.',
                'sms_sent' => $smsSent,
                'data' => [
                    'is_new_user' => $isNewUser,
                    'user_status' => $userStatus,
                    'sms_sent' => $smsSent,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'mobile' => $user->mobile,
                        'mobile_verified' => !is_null($user->mobile_verified_at),
                    ],
                    // Remove 'otp' field in production for security
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ VALIDATION ERROR in checkUserAndSendOtp', [
                'timestamp' => now()->toDateTimeString(),
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ ERROR in checkUserAndSendOtp', [
                'timestamp' => now()->toDateTimeString(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Verify OTP for user
     */
    public function verifyUserOtp(Request $request)
    {
        $validated = $request->validate([
            'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $mobile = $validated['mobile'];
        $otp = $validated['otp'];

        // Find user by mobile
        $user = User::where('mobile', $mobile)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found. Please request OTP first.',
            ], 404);
        }
        
        // Check if OTP exists
        if (!$user->otp) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP found. Please request a new one.',
            ], 410);
        }

        // Check if OTP is expired
        if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ], 410);
        }

        // Verify OTP
        if ($user->otp !== $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 422);
        }
        
        // OTP is valid - clear it, mark mobile as verified, and log user in
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ]);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $newCsrfToken = csrf_token();

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->firstname . ' ' . $user->lastname,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'mobile_verified' => true,
            ],
            'authenticated' => Auth::check(),
            'csrf_token' => $newCsrfToken,
        ]);
    }

    /**
     * Show frontend login page
     */
    public function login()
    {
        // If already logged in, redirect to booking dashboard
        if (Auth::check()) {
            return redirect()->route('frontend.booking-dashboard');
        }
        return view('frontend.login');
    }

    /**
     * Send OTP for login (mobile only, creates user if doesn't exist)
     */
    public function sendLoginOtp(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'mobile' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            ]);

            $mobile = $validated['mobile'];

            // Check if user exists by mobile
            $user = User::where('mobile', $mobile)->first();
            $isNewUser = false;

            if (!$user) {
                // Create new user with mobile number only
                $isNewUser = true;
                $user = User::create([
                    'mobile' => $mobile,
                    'firstname' => 'User',
                    'lastname' => '',
                    'email' => 'user_' . $mobile . '@temp.com', // Temporary email
                    'password' => bcrypt(Str::random(32)), // Random password
                    'role_type' => 'user',
                ]);

                Log::info('✅ New user created during login', [
                    'mobile' => $mobile,
                    'user_id' => $user->id,
                ]);
            }

            // Generate 6-digit OTP
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Save OTP to user (expires in 10 minutes)
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            // Send OTP via SMS
            // Format mobile with country code (91 for India)
            $mobileWithCountryCode = '91' . $mobile;
            
            // Determine template: registration_otp for new users, login_otp for existing
            $templateKey = $isNewUser ? 'registration_otp' : 'login_otp';
            
            // Track whether SMS was sent successfully
            $smsSent = false;
            
            // Try to send OTP via SMS (silently fail if gateway is disabled)
            try {
                $this->smsService->send(
                    $mobileWithCountryCode,
                    $templateKey,
                    ['OTP' => $otp],
                    [
                        'type' => 'manual',
                        'reference_type' => \App\Models\User::class,
                        'reference_id' => $user->id,
                        'notes' => $isNewUser ? 'Registration OTP - Login Page' : 'Login OTP'
                    ]
                );
                
                $smsSent = true;
                
                Log::info('✅ Login OTP SMS sent successfully', [
                    'mobile' => $mobile,
                    'template' => $templateKey,
                    'is_new_user' => $isNewUser,
                    'user_id' => $user->id,
                ]);
            } catch (\RuntimeException $e) {
                // Check if error is about SMS gateway not being enabled
                $errorMessage = $e->getMessage();
                if (stripos($errorMessage, 'not enabled') !== false || 
                    stripos($errorMessage, 'not configured') !== false ||
                    stripos($errorMessage, 'no active') !== false) {
                    
                    // SMS gateway is disabled - log silently and continue workflow
                    Log::info('⚠️ SMS Gateway not enabled - OTP not sent via SMS', [
                        'mobile' => $mobile,
                        'user_id' => $user->id,
                        'otp' => $otp, // Log OTP for reference
                    ]);
                    
                    // In development, log OTP for testing
                    if (config('app.debug')) {
                        Log::info('📱 Login OTP (SMS Gateway Disabled - Development): ' . $otp);
                    }
                } else {
                    // Other SMS errors - log but continue workflow
                    Log::warning('⚠️ Failed to send Login OTP SMS - continuing without SMS', [
                        'mobile' => $mobile,
                        'template' => $templateKey,
                        'error' => $errorMessage,
                        'user_id' => $user->id,
                    ]);
                    
                    // In development, log OTP for testing
                    if (config('app.debug')) {
                        Log::info('📱 Login OTP (SMS Failed - Development): ' . $otp);
                    }
                }
            } catch (\Exception $e) {
                // Log unexpected errors but continue workflow
                Log::warning('⚠️ Unexpected error sending Login OTP SMS - continuing without SMS', [
                    'mobile' => $mobile,
                    'template' => $templateKey,
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);
                
                // In development, log OTP for testing
                if (config('app.debug')) {
                    Log::info('📱 Login OTP (Unexpected Error - Development): ' . $otp);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'sms_sent' => $smsSent,
                'data' => [
                    'mobile' => $mobile,
                    'is_new_user' => $isNewUser,
                    'is_existing_user' => !$isNewUser,
                    'sms_sent' => $smsSent,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ ERROR in sendLoginOtp', [
                'timestamp' => now()->toDateTimeString(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function storeBooking(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'owner_type' => 'required|string|in:Owner,Broker',
            'main_property_type' => 'required|string|in:Residential,Commercial,Other',
            'house_number' => 'required|string|max:255',
            'building_name' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'city' => 'required|string',
            'full_address' => 'required|string',
            'payment_method' => 'required|string|in:card,upi,netbanking',
            'amount' => 'required|numeric|min:0',
        ]);

        // Find or create user (guest user for now)
        $user = User::firstOrCreate(
            ['phone' => $validated['phone']],
            [
                'name' => $validated['name'],
                'email' => strtolower(str_replace(' ', '', $validated['name'])) . '@guest.com',
                'password' => bcrypt('guest123'),
                'role_type' => 'user'
            ]
        );

        // Get city (default to Ahmedabad)
        $city = City::where('name', $validated['city'])->first();
        if (!$city) {
            $state = State::firstOrCreate(['name' => 'Gujarat']);
            $city = City::create([
                'name' => $validated['city'],
                'state_id' => $state->id
            ]);
        }

        // Determine property details based on main type
        $propertyData = $this->extractPropertyData($request, $validated['main_property_type']);

        // Create booking (aligned to bookings schema)
        $booking = Booking::create([
            'user_id' => $user->id,
            'property_type_id' => $propertyData['property_type_id'],
            'property_sub_type_id' => $propertyData['property_sub_type_id'],
            'owner_type' => $validated['owner_type'],
            'bhk_id' => $propertyData['bhk_id'],
            'city_id' => $city->id,
            'state_id' => $city->state_id,
            'booking_date' => now(),
            'status' => 'pending',
            'payment_status' => 'paid',
            'price' => (int) $validated['amount'],
            'furniture_type' => $propertyData['furniture_type'] ?? null,
            'area' => $propertyData['area'] ?? 0,
            'house_no' => $validated['house_number'],
            'building' => $validated['building_name'],
            'pin_code' => $validated['pincode'],
            'full_address' => $validated['full_address'],
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create a tour for this booking
        Tour::create([
            'booking_id' => $booking->id,
            'name' => 'Tour for Booking #' . $booking->id,
            'title' => 'Property Tour - ' . ($validated['name'] ?? 'Property'),
            'status' => 'draft',
            'revision' => 1,
        ]);

        // Redirect with success message
        return redirect()->route('frontend.index')->with('success', 'Booking submitted successfully! Our team will contact you soon.');
    }

    /**
     * Step 2 -> Save property data (draft booking creation or update)
     */
    public function savePropertyStep(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|digits:10',
            'owner_type' => 'required|string|in:Owner,Broker',
            'main_property_type' => 'required|string|in:Residential,Commercial,Other',
            // Residential
            'residential_property_type' => 'nullable|string',
            'residential_furnish' => 'nullable|string',
            'residential_size' => 'nullable|string',
            'residential_area' => 'nullable|numeric|min:1',
            // Commercial
            'commercial_property_type' => 'nullable|string',
            'commercial_furnish' => 'nullable|string',
            'commercial_area' => 'nullable|numeric|min:1',
            // Other
            'other_looking' => 'nullable|string',
            'other_option_details' => 'nullable|string',
            'other_area' => 'nullable|numeric|min:1',
            'firm_name' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:50',
            'booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        // Find user by mobile or phone field
        $user = User::where('mobile', $validated['phone'])
            ->orWhere('mobile', $validated['phone'])
            ->first();
        if (!$user) {
            // Create minimal user (guest) - keep mobile field for consistency
            $nameParts = explode(' ', $validated['name'], 2);
            $user = User::create([
                'firstname' => $nameParts[0],
                'lastname' => $nameParts[1] ?? '',
                'mobile' => $validated['phone'],
            ]);
            $customerRole = Role::firstOrCreate(['name' => 'customer']);
            $user->assignRole($customerRole);
        }

        // Resolve property mapping
        $mapping = $this->mapPropertyData($validated);

        // Create or update draft booking
        $booking = null;
        if (!empty($validated['booking_id'])) {
            $booking = Booking::find($validated['booking_id']);
        }
        if (!$booking) {
            $booking = new Booking();
            $booking->status = 'draft_property';
            $booking->payment_status = 'unpaid';
            // Assign Carbon date instance
            $booking->created_by = $user->id;
        }

        $booking->user_id = $user->id;
        $booking->property_type_id = $mapping['property_type_id'];
        $booking->property_sub_type_id = $mapping['property_sub_type_id'];
        $booking->owner_type = $validated['owner_type'];
        $booking->bhk_id = $mapping['bhk_id'];
        $booking->furniture_type = $mapping['furniture_type'];
        $booking->other_option_details = $validated['other_option_details'] ?? null;
        $booking->firm_name = $validated['firm_name'] ?? null;
        $booking->gst_no = $validated['gst_no'] ?? null;
        $booking->area = $mapping['area'] ?? 0;
        $booking->price = $this->calculateEstimate($mapping['area'] ?? 0);
        $booking->payment_status = $booking->payment_status ?: 'pending';
        $booking->status = 'pending';
        $booking->updated_by = $user->id;
        $booking->save();

        // Create tour only if this is a new booking (not an update)
        if (!$validated['booking_id']) {
            \App\Models\Tour::create([
                'booking_id' => $booking->id,
                'name' => 'Tour for Booking #' . $booking->id,
                'title' => 'Property Tour - ' . ($validated['name'] ?? 'Property'),
                'status' => 'draft',
                'revision' => 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property details saved.',
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Step 3 -> Save address data
     */
    public function saveAddressStep(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'house_number' => 'required|string|max:255',
            'building_name' => 'required|string|max:255',
            'pincode' => 'required|digits:6',
            'city' => 'required|string',
            'full_address' => 'required|string',
        ]);

        $booking = Booking::find($validated['booking_id']);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        // Resolve city & state
        $city = City::where('name', $validated['city'])->first();
        if (!$city) {
            $state = State::firstOrCreate(['name' => 'Gujarat']);
            $city = City::create([
                'name' => $validated['city'],
                'state_id' => $state->id
            ]);
        }

        $booking->house_no = $validated['house_number'];
        $booking->building = $validated['building_name'];
        $booking->pin_code = $validated['pincode'];
        $booking->full_address = $validated['full_address'];
        $booking->city_id = $city->id;
        $booking->state_id = $city->state_id;
        $booking->status = 'pending';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Address details saved.',
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Update booking from edit modal (combines property and address updates)
     */
    public function updateBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:bookings,id',
                'owner_type' => 'nullable|string|in:Owner,Broker',
                'main_property_type' => 'nullable|string|in:Residential,Commercial,Other',
                'residential_property_type' => 'nullable|string',
                'residential_furnish' => 'nullable|string',
                'residential_size' => 'nullable|integer',
                'residential_area' => 'nullable|numeric|min:0',
                'commercial_property_type' => 'nullable|string',
                'commercial_furnish' => 'nullable|string',
                'commercial_area' => 'nullable|numeric|min:0',
                'other_looking' => 'nullable|string',
                'other_option_details' => 'nullable|string',
                'other_area' => 'nullable|numeric|min:0',
                'firm_name' => 'nullable|string|max:255',
                'gst_no' => 'nullable|string|max:50',
                'house_number' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'pincode' => 'nullable|string|regex:/^[0-9]{6}$/',
                'city' => 'nullable|string|max:255',
                'full_address' => 'nullable|string',
                'scheduled_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'booking_notes' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
            }

            $booking = Booking::find($validated['booking_id']);
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            // Check if user owns this booking
            if ($booking->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            // If payment is completed, only allow schedule updates
            if ($booking->payment_status === 'paid') {
                // Only update schedule-related fields
                if (isset($validated['scheduled_date'])) {
                    // Store scheduled date in booking_date field
                    $booking->booking_date = $validated['scheduled_date'];
                    $booking->status = 'scheduled'; // Auto-set status to scheduled when date is set
                }
                // Store notes in booking_notes field
                if (isset($validated['notes']) || isset($validated['booking_notes'])) {
                    $booking->booking_notes = $validated['notes'] ?? $validated['booking_notes'] ?? null;
                }
                $booking->updated_by = $user->id;
                $booking->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Schedule updated successfully. Property and address details are locked after payment.',
                    'booking_id' => $booking->id,
                ]);
            }

            // Payment not done - allow full updates
            // Check if this is a price-only update (for payment flow)
            $isPriceOnlyUpdate = isset($validated['price']) && 
                                 empty($validated['owner_type']) && 
                                 empty($validated['main_property_type']);
            
            if ($isPriceOnlyUpdate) {
                // Price-only update: just update price and area if provided
                if (isset($validated['price']) && $validated['price'] > 0) {
                    $booking->price = (int) $validated['price'];
                }
                
                // Update area if provided
                if (isset($validated['residential_area']) && $validated['residential_area'] > 0) {
                    $booking->area = (int) $validated['residential_area'];
                } elseif (isset($validated['commercial_area']) && $validated['commercial_area'] > 0) {
                    $booking->area = (int) $validated['commercial_area'];
                } elseif (isset($validated['other_area']) && $validated['other_area'] > 0) {
                    $booking->area = (int) $validated['other_area'];
                }
                
                $booking->updated_by = $user->id;
                $booking->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Price updated successfully.',
                    'booking_id' => $booking->id,
                ]);
            }
            
            // Full update - validate required fields for property and address
            if (empty($validated['owner_type']) || empty($validated['main_property_type']) || 
                empty($validated['house_number']) || empty($validated['building_name']) || 
                empty($validated['pincode']) || empty($validated['full_address'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'All required fields must be filled.',
                ], 422);
            }

            // Map property data (same logic as setup page)
            try {
                $propertyData = $this->mapPropertyData($validated);
            } catch (\Exception $e) {
                \Log::error('Error mapping property data: ' . $e->getMessage(), [
                    'data' => $validated
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid property data. Please check your selections and try again.',
                ], 422);
            }

            // Get city - handle null/empty city gracefully
            $cityName = $validated['city'] ?? 'Ahmedabad';
            if (empty($cityName)) {
                $cityName = 'Ahmedabad';
            }
            
            try {
                $city = City::where('name', $cityName)->first();
                if (!$city) {
                    $city = City::firstOrCreate(['name' => $cityName], ['state_id' => 1]); // Default state
                }
            } catch (\Exception $e) {
                \Log::error('Error finding/creating city: ' . $e->getMessage(), [
                    'city_name' => $cityName
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing city information. Please try again.',
                ], 422);
            }

            // Update booking with null checks
            try {
                // Use update() method which respects fillable and handles mass assignment
                $updateData = [];
                
                // Property fields
                if (isset($validated['owner_type'])) {
                    $updateData['owner_type'] = $validated['owner_type'];
                }
                if (isset($propertyData['property_type_id'])) {
                    $updateData['property_type_id'] = $propertyData['property_type_id'];
                }
                if (isset($propertyData['property_sub_type_id'])) {
                    $updateData['property_sub_type_id'] = $propertyData['property_sub_type_id'];
                }
                if (isset($propertyData['bhk_id'])) {
                    $updateData['bhk_id'] = $propertyData['bhk_id'];
                }
                if (isset($propertyData['furniture_type'])) {
                    $updateData['furniture_type'] = $propertyData['furniture_type'];
                }
                if (isset($validated['other_option_details'])) {
                    $updateData['other_option_details'] = $validated['other_option_details'];
                }
                if (isset($validated['firm_name'])) {
                    $updateData['firm_name'] = $validated['firm_name'];
                }
                if (isset($validated['gst_no'])) {
                    $updateData['gst_no'] = $validated['gst_no'];
                }
                // Note: tour_code and tour_final_link are admin-only fields, not editable by frontend users
                if (isset($propertyData['area'])) {
                    $updateData['area'] = (int) $propertyData['area'];
                } elseif (isset($validated['residential_area'])) {
                    $updateData['area'] = (int) $validated['residential_area'];
                } elseif (isset($validated['commercial_area'])) {
                    $updateData['area'] = (int) $validated['commercial_area'];
                } elseif (isset($validated['other_area'])) {
                    $updateData['area'] = (int) $validated['other_area'];
                }
                
                // Address fields
                if (isset($validated['house_number'])) {
                    $updateData['house_no'] = $validated['house_number'];
                }
                if (isset($validated['building_name'])) {
                    $updateData['building'] = $validated['building_name'];
                }
                if (isset($validated['pincode'])) {
                    $updateData['pin_code'] = $validated['pincode'];
                }
                if (isset($validated['full_address'])) {
                    $updateData['full_address'] = $validated['full_address'];
                }
                if (isset($city->id)) {
                    $updateData['city_id'] = $city->id;
                }
                if (isset($city->state_id)) {
                    $updateData['state_id'] = $city->state_id;
                }
                
                // Schedule fields - save scheduled_date to booking_date
                if (isset($validated['scheduled_date'])) {
                    $updateData['booking_date'] = $validated['scheduled_date'];
                    $updateData['status'] = 'scheduled'; // Auto-set status to scheduled when date is set
                }
                
                // Booking notes - save notes to booking_notes field
                if (isset($validated['notes']) || isset($validated['booking_notes'])) {
                    $updateData['booking_notes'] = $validated['notes'] ?? $validated['booking_notes'] ?? null;
                }
                
                // Price
                if (isset($validated['price']) && $validated['price'] > 0) {
                    $updateData['price'] = (int) $validated['price'];
                }
                
                // Updated by
                $updateData['updated_by'] = $user->id;
                
                // Update the booking
                $booking->update($updateData);
                
            } catch (\Illuminate\Database\QueryException $e) {
                \Log::error('Database error saving booking: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Database error while saving booking. Please check your data and try again.',
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Error saving booking: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving booking data: ' . (config('app.debug') ? $e->getMessage() : 'Please try again.'),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully.',
                'booking_id' => $booking->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating booking: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating booking. Please try again.',
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Step 4 -> Get booking summary
     */
    public function getBookingSummary(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:bookings,id',
            ]);
            
            $booking = Booking::with(['propertyType','propertySubType','bhk','city','state','user'])->find($validated['booking_id']);
            
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }
            
            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'status' => $booking->status,
                    'owner_type' => $booking->owner_type ?? null,
                    'property_category' => $booking->propertyType?->name,
                    'property_type' => $booking->propertyType?->name,
                    'property_sub_type' => $booking->propertySubType?->name,
                'furniture_type' => $booking->furniture_type,
                'bhk' => $booking->bhk?->name,
                'bhk_id' => $booking->bhk_id,
                'area' => $booking->area,
                    'other_option_details' => $booking->other_option_details ?? null,
                    'other_details' => $booking->other_option_details ?? null, // Keep for backward compatibility
                    'firm_name' => $booking->firm_name ?? null,
                    'gst_no' => $booking->gst_no ?? null,
                    'tour_code' => $booking->tour_code ?? null,
                    'tour_final_link' => $booking->tour_final_link ?? null,
                    'house_number' => $booking->house_no,
                    'building_name' => $booking->building ?? null,
                    'city' => $booking->city?->name ?? 'Ahmedabad',
                    'pincode' => $booking->pin_code,
                    'full_address' => $booking->full_address,
                    'scheduled_date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : ($booking->booking_date instanceof \Carbon\Carbon ? $booking->booking_date->format('Y-m-d') : \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d'))) : null,
                    'scheduled_time' => null, // scheduled_time column doesn't exist in database
                    'booking_notes' => $booking->booking_notes ?? null,
                    'price_estimate' => $this->calculateEstimate($booking->area),
                    'price' => $booking->price ?? null,
                    'payment_status' => $booking->payment_status ?? 'pending',
                    'payment_amount' => $booking->cashfree_payment_amount ?? $booking->price ?? null,
                    'cashfree_order_id' => $booking->cashfree_order_id ?? null,
                    'cashfree_payment_session_id' => $booking->cashfree_payment_session_id ?? null,
                    // Validation flags for payment button visibility
                    'has_complete_property_data' => $booking->hasCompletePropertyData(),
                    'has_complete_address_data' => $booking->hasCompleteAddressData(),
                    'is_ready_for_payment' => $booking->isReadyForPayment(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error fetching booking summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching booking details. Please try again.'
            ], 500);
        }
    }

    /**
     * Step 5 -> Finalize payment & confirm booking
     */
    public function finalizePaymentStep(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'payment_method' => 'required|string|in:card,upi,netbanking',
            'amount' => 'required|numeric|min:0',
        ]);
        $booking = Booking::find($validated['booking_id']);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }
        $booking->payment_status = 'paid';
        $booking->status = 'pending';
        $booking->price = (int) $validated['amount'];
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment completed. Booking confirmed.',
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * List bookings for the authenticated user (property grid)
     */
    public function listUserBookings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $bookings = Booking::with(['propertyType', 'propertySubType', 'bhk', 'city'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(fn ($booking) => $this->formatBookingForGrid($booking)),
        ]);
    }

    /**
     * Internal helper: map property request data to DB ids
     */
    protected function mapPropertyData(array $data): array
    {
        $main = $data['main_property_type'] ?? null;
        
        if (empty($main)) {
            throw new \InvalidArgumentException('Main property type is required');
        }
        
        $propertyType = PropertyType::where('name', $main)->first();
        $propertyTypeId = $propertyType?->id;

        if (!$propertyTypeId) {
            throw new \InvalidArgumentException("Property type '{$main}' not found");
        }

        $subTypeName = null;
        $furnish = null;
        $bhkId = null;
        $area = null;
        $otherDetails = null;

        if ($main === 'Residential') {
            $subTypeName = $data['residential_property_type'] ?? null;
            $furnish = $data['residential_furnish'] ?? null;
            $size = $data['residential_size'] ?? null;
            if ($size) {
                $bhk = BHK::where('id', $size)->first();
                $bhkId = $bhk?->id;
            }
            $area = $data['residential_area'] ?? null;
        } elseif ($main === 'Commercial') {
            $subTypeName = $data['commercial_property_type'] ?? null;
            $furnish = $data['commercial_furnish'] ?? null;
            $area = $data['commercial_area'] ?? null;
        } else { // Other
            $subTypeName = $data['other_looking'] ?? null;
            $otherDetails = $data['other_option_details'] ?? $data['other_description'] ?? null;
            $area = $data['other_area'] ?? null;
        }

        $subTypeId = null;
        if ($subTypeName && $propertyTypeId) {
            $subType = PropertySubType::where('property_type_id', $propertyTypeId)
                ->where('name', $subTypeName)
                ->first();
            $subTypeId = $subType?->id;
        }

        return [
            'property_type_id' => $propertyTypeId,
            'property_sub_type_id' => $subTypeId,
            'bhk_id' => $bhkId,
            'furniture_type' => $furnish,
            'area' => $area ? (float) $area : null,
            'other_details' => $otherDetails,
            'other_option_details' => $otherDetails,
        ];
    }

    /**
     * Simple dynamic price estimator (same logic as frontend)
     * Uses settings from database for dynamic pricing
     */
    protected function calculateEstimate($area): int
    {
        $areaVal = (int) $area;
        if ($areaVal <= 0) return 0;
        
        // Fetch price settings from database with defaults
        $settings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();
        
        // Use settings from database or fallback to defaults
        $baseArea = (int) ($settings['base_area'] ?? 1500);
        $basePrice = (int) ($settings['base_price'] ?? 599);
        $extraArea = (int) ($settings['extra_area'] ?? 500);
        $extraAreaPrice = (int) ($settings['extra_area_price'] ?? 200);
        
        $price = $basePrice;
        if ($areaVal > $baseArea) {
            $extra = $areaVal - $baseArea;
            $blocks = (int) ceil($extra / $extraArea);
            $price += $blocks * $extraAreaPrice;
        }
        
        return $price;
    }

    /**
     * Step 5 -> Create Cashfree order & session id
     */
    public function createCashfreeSession(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|integer|exists:bookings,id',
            ]);

            // Reload booking to get latest data (especially after price updates)
            $booking = Booking::with('user')->findOrFail($validated['booking_id']);
            $booking->refresh(); // Ensure we have the latest data from database

            // Check if user owns this booking
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please login to proceed with payment.',
                ], 401);
            }

            if ($booking->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only make payment for your own bookings.',
                ], 403);
            }

            if ($booking->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking is already paid.',
                ], 422);
            }

            // Get the latest price - prioritize booking price, then calculate from area
            $amount = $booking->price;
            if (!$amount || $amount <= 0) {
                $amount = $this->calculateEstimate($booking->area);
            }
            
            // Ensure amount is valid
            $amount = (float) $amount;
            $amount = round($amount, 2);

            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to calculate payment amount for this booking. Please ensure the area is set correctly.',
                ], 422);
            }

            $customer = $booking->user;
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer information not found for this booking.',
                ], 422);
            }

            $customerName = trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) ?: 'Customer';
            $customerEmail = $customer->email ?: 'customer' . $booking->id . '@example.com';
            $customerPhone = $customer->mobile ?: $request->input('phone');

            if (empty($customerPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer phone number is required for payment. Please update your profile with a valid phone number.',
                ], 422);
            }

            // Check if we have an existing valid payment session
            // BUT: If the amount has changed, we need to create a new session
            if ($booking->cashfree_payment_session_id && $booking->cashfree_order_id) {
                $existingAmount = $booking->cashfree_payment_amount ?: $booking->price;
                $amountChanged = abs($existingAmount - $amount) > 0.01; // Allow small floating point differences
                
                // Only reuse session if payment is still pending AND amount hasn't changed
                if (!$amountChanged && $booking->payment_status === 'pending' && $booking->cashfree_payment_status !== 'PAID') {
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'order_id' => $booking->cashfree_order_id,
                            'payment_session_id' => $booking->cashfree_payment_session_id,
                            'amount' => $booking->cashfree_payment_amount ?: $amount,
                            'currency' => $booking->cashfree_payment_currency ?: 'INR',
                            'mode' => $this->cashfree->mode(),
                            'return_url' => config('cashfree.return_url') ?: route('frontend.cashfree.callback'),
                        ],
                    ]);
                }
            }

            // Generate a unique order ID - if order exists, append timestamp to make it unique
            $baseOrderId = 'bk_' . $booking->id;
            if ($booking->cashfree_order_id) {
                // Order already exists, create a new unique one with timestamp
                $orderId = $baseOrderId . '_' . time() . '_' . Str::upper(Str::random(4));
            } else {
                $orderId = $baseOrderId . '_' . Str::upper(Str::random(6));
            }
            
            $customerId = 'cust_' . ($customer?->id ?? $booking->id);
            $returnUrl = config('cashfree.return_url') ?: route('frontend.cashfree.callback');

            // Ensure amount is a valid number and properly formatted
            $orderAmount = (float) $amount;
            $orderAmount = round($orderAmount, 2);
            
            if ($orderAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment amount. Please ensure the area and price are set correctly.',
                ], 422);
            }
            
            $payload = [
                'order_id' => $orderId,
                'order_amount' => $orderAmount,
                'order_currency' => 'INR',
                'order_note' => 'Virtual tour booking #' . $booking->id,
                'customer_details' => [
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone,
                ],
                'order_meta' => [
                    'return_url' => rtrim($returnUrl, '/') . '?order_id={order_id}',
                ],
            ];

            try {
                $response = $this->cashfree->createOrder($payload);
            } catch (\Throwable $e) {
                Log::error('Cashfree order creation failed', [
                    'booking_id' => $booking->id,
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Cashfree. Please try again later.',
                ], 500);
            }

            $statusCode = $response['status_code'] ?? 500;
            $body = $response['json'] ?? null;
            
            // Handle duplicate order ID error - retry with new ID
            if ($statusCode === 422 && isset($body['message']) && str_contains(strtolower($body['message']), 'order with same id')) {
                // Generate a completely new order ID with timestamp
                $orderId = $baseOrderId . '_' . time() . '_' . Str::upper(Str::random(6));
                $payload['order_id'] = $orderId;
                
                try {
                    $response = $this->cashfree->createOrder($payload);
                    $statusCode = $response['status_code'] ?? 500;
                    $body = $response['json'] ?? null;
                } catch (\Throwable $e) {
                    Log::error('Cashfree order creation retry failed', [
                        'booking_id' => $booking->id,
                        'message' => $e->getMessage(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create payment session. Please try again later.',
                    ], 500);
                }
            }
            
            if ($statusCode < 200 || $statusCode >= 300 || empty($body['payment_session_id'])) {
                Log::error('Cashfree order creation error', [
                    'booking_id' => $booking->id,
                    'status_code' => $statusCode,
                    'response' => $response['body'] ?? null,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $body['message'] ?? 'Unable to create payment session. Please try again.',
                ], 422);
            }

        $booking->cashfree_order_id = $body['order_id'] ?? $orderId;
        $booking->cashfree_payment_session_id = $body['payment_session_id'];
        $booking->cashfree_payment_status = $body['order_status'] ?? 'CREATED';
        $booking->cashfree_payment_amount = (int) round($body['order_amount'] ?? $amount);
        $booking->cashfree_payment_currency = $body['order_currency'] ?? 'INR';
        $booking->cashfree_payment_meta = [
            'customer_id' => $customerId,
        ];
        $booking->cashfree_last_response = $body;
        $booking->price = $booking->price ?: (int) round($amount);
        $booking->payment_status = 'pending';
        $booking->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $booking->cashfree_order_id,
                    'payment_session_id' => $booking->cashfree_payment_session_id,
                    'amount' => $booking->cashfree_payment_amount,
                    'currency' => $booking->cashfree_payment_currency,
                    'mode' => $this->cashfree->mode(),
                    'return_url' => $payload['order_meta']['return_url'],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating Cashfree session: ' . $e->getMessage(), [
                'booking_id' => $request->input('booking_id'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating payment session. Please try again or contact support.',
            ], 500);
        }
    }

    /**
     * Poll Cashfree for latest order status and sync booking
     */
    public function refreshCashfreeStatus(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::with('user')->findOrFail($validated['booking_id']);

        if (!$booking->cashfree_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment session not created yet.',
            ], 422);
        }

        try {
            $response = $this->cashfree->fetchOrder($booking->cashfree_order_id);
        } catch (\Throwable $e) {
            Log::error('Cashfree status fetch failed', [
                'booking_id' => $booking->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch payment status from Cashfree.',
            ], 500);
        }

        $statusCode = $response['status_code'] ?? 500;
        $orderData = $response['json'] ?? [];

        if ($statusCode < 200 || $statusCode >= 300 || empty($orderData)) {
            return response()->json([
                'success' => false,
                'message' => 'Cashfree did not return order details yet. Please retry.',
            ], 422);
        }

        $summary = $this->syncBookingWithCashfreeOrder($booking, $orderData);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Return URL handler for Cashfree redirect fallback
     */
    public function cashfreeCallback(Request $request)
    {
        $orderId = $request->query('order_id');

        if (!$orderId) {
            return view('frontend.cashfree-callback', [
                'orderId' => null,
                'status' => 'UNKNOWN',
                'message' => 'Missing order reference.',
                'details' => null,
            ]);
        }

        $booking = Booking::where('cashfree_order_id', $orderId)->first();

        if (!$booking) {
            return view('frontend.cashfree-callback', [
                'orderId' => $orderId,
                'status' => 'UNKNOWN',
                'message' => 'We could not find this booking. Please contact support.',
                'details' => null,
            ]);
        }

        try {
            $response = $this->cashfree->fetchOrder($orderId);
        } catch (\Throwable $e) {
            return view('frontend.cashfree-callback', [
                'orderId' => $orderId,
                'status' => 'UNKNOWN',
                'message' => 'Unable to fetch payment status. Please contact support with your order ID.',
                'details' => null,
            ]);
        }

        $orderData = $response['json'] ?? [];
        $summary = $this->syncBookingWithCashfreeOrder($booking, $orderData);

        return view('frontend.cashfree-callback', [
            'orderId' => $summary['order_id'],
            'status' => $summary['order_status'],
            'message' => $summary['status_message'],
            'details' => $summary,
        ]);
    }

    protected function syncBookingWithCashfreeOrder(Booking $booking, array $orderData): array
    {
        $orderStatus = strtoupper($orderData['order_status'] ?? 'UNKNOWN');
        $payments = $orderData['payments'] ?? [];
        if (count($payments) > 1) {
            usort($payments, function ($a, $b) {
                $timeA = isset($a['payment_time']) ? strtotime($a['payment_time']) : 0;
                $timeB = isset($b['payment_time']) ? strtotime($b['payment_time']) : 0;
                return $timeB <=> $timeA;
            });
        }
        $latestPayment = $payments[0] ?? null;

        $booking->cashfree_payment_status = $orderStatus;
        $booking->cashfree_payment_amount = (int) round($orderData['order_amount'] ?? $booking->cashfree_payment_amount);
        $booking->cashfree_payment_currency = $orderData['order_currency'] ?? $booking->cashfree_payment_currency ?? 'INR';
        $booking->cashfree_last_response = $orderData;

        if ($latestPayment) {
            $booking->cashfree_payment_method = $latestPayment['payment_method'] ?? null;
            $booking->cashfree_reference_id = $latestPayment['cf_payment_id'] ?? ($latestPayment['payment_reference_id'] ?? null);
            $booking->cashfree_payment_message = $latestPayment['payment_message'] ?? null;
            if (!empty($latestPayment['payment_time'])) {
                $booking->cashfree_payment_at = Carbon::parse($latestPayment['payment_time']);
            }
        }

        if ($orderStatus === 'PAID') {
            $booking->payment_status = 'paid';
            $booking->status = 'confirmed';
            $booking->cashfree_payment_message = $booking->cashfree_payment_message ?: 'Payment successful';
        } elseif (in_array($orderStatus, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED'])) {
            $booking->payment_status = 'failed';
            $booking->cashfree_payment_message = $booking->cashfree_payment_message ?: 'Payment failed';
        } else {
            $booking->payment_status = 'pending';
        }

        $booking->save();

        return [
            'booking_id' => $booking->id,
            'order_id' => $booking->cashfree_order_id,
            'order_status' => $orderStatus,
            'amount' => $booking->cashfree_payment_amount,
            'currency' => $booking->cashfree_payment_currency,
            'payment_method' => $booking->cashfree_payment_method,
            'reference_id' => $booking->cashfree_reference_id,
            'payment_at' => optional($booking->cashfree_payment_at)->toDateTimeString(),
            'status_message' => $booking->cashfree_payment_message,
            'raw' => $orderData,
        ];
    }

    protected function formatBookingForGrid(Booking $booking): array
    {
        $mainType = $booking->propertyType?->name ?? 'Residential';
        return [
            'id' => $booking->id,
            'owner_type' => $booking->owner_type,
            'main_property_type' => $mainType,
            'property_sub_type' => $booking->propertySubType?->name,
            'furniture_type' => $booking->furniture_type,
            'bhk_id' => $booking->bhk_id,
            'bhk_label' => $booking->bhk?->name,
            'area' => $booking->area,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'price' => $booking->price,
            'updated_at' => optional($booking->updated_at)->toDateTimeString(),
            'city' => $booking->city?->name,
            'other_details' => $booking->other_details ?? null,
            'address' => [
                'house_number' => $booking->house_no,
                'building_name' => $booking->building,
                'pincode' => $booking->pin_code,
                'full_address' => $booking->full_address,
                'city' => $booking->city?->name,
            ],
        ];
    }

    /**
     * Display the booking dashboard
     */
    public function bookingDashboard()
    {
        // Fetch authenticated user's bookings with related data
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get property types and BHK for edit modal (same as setup page)
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id','name','icon']);
        $bhk = BHK::all();
        
        // Get price settings for dynamic pricing
        $priceSettings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();
        
        return view('frontend.booking-dashboard', compact('bookings', 'types', 'bhk', 'priceSettings'));
    }

    /**
     * Display the privacy policy page
     */
    public function privacyPolicy()
    {
        return view('frontend.privacy-policy');
    }

    /**
     * Display the refund policy page
     */
    public function refundPolicy()
    {
        return view('frontend.refund-policy');
    }

    /**
     * Display the terms and conditions page
     */
    public function termsConditions()
    {
        return view('frontend.terms-conditions');
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->first();

        if (!$booking) {
            abort(404, 'Booking not found');
        }

        if ($booking->payment_status !== 'paid') {
            abort(403, 'Receipt is only available for paid bookings');
        }

        $receiptData = [
            'booking' => $booking,
            'order_id' => $booking->cashfree_order_id,
            'amount' => $booking->cashfree_payment_amount ?? $booking->price,
            'currency' => $booking->cashfree_payment_currency ?? 'INR',
            'payment_method' => $booking->cashfree_payment_method ?? 'Online Payment',
            'reference_id' => $booking->cashfree_reference_id,
            'payment_at' => $booking->cashfree_payment_at ?? $booking->updated_at,
            'user' => $booking->user,
            'property_type' => $booking->propertyType?->name ?? 'N/A',
            'property_sub_type' => $booking->propertySubType?->name ?? 'N/A',
            'furniture_type' => $booking->furniture_type ?? 'N/A',
            'bhk' => $booking->bhk?->name ?? 'N/A',
            'area' => $booking->area ?? 'N/A',
            'city' => $booking->city?->name ?? 'N/A',
            'state' => $booking->state?->name ?? 'N/A',
            'address' => $booking->full_address ?? 'N/A',
        ];

        return view('frontend.receipt', $receiptData);
    }
}
