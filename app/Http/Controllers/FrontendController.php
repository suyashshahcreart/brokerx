<?php

namespace App\Http\Controllers;

use App\Models\PhotographerVisitJob;
use App\Models\Tour;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingAssignee;
use App\Models\BookingHistory;
use App\Models\PaymentHistory;
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

class FrontendController extends Controller{

    protected CashfreeService $cashfree;
    protected SmsService $smsService;

    public function __construct(CashfreeService $cashfree, SmsService $smsService){
        $this->cashfree = $cashfree;
        $this->smsService = $smsService;
    }

    public function index(){
        // Get base price for display on landing page
        $basePrice = (int) (Setting::where('name', 'base_price')->value('value') ?? 599);

        return view('frontend.index', compact('basePrice'));
    }

    public function setup(Request $request){

        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id', 'name', 'icon']);
        $states = State::with(['cities:id,state_id,name'])->get(['id', 'name', 'code']);
        $cities = City::get(['id', 'name', 'state_id']);
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
    public function checkUserAndSendOtp(Request $request){
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

                // Create user with minimal data (email is null)
                $user = User::create([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'mobile' => $mobile,
                    'email' => null, // Email is null for setup page users
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
                        'reference_type' => User::class,
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
                // Create new user with mobile number only (email is null)
                $isNewUser = true;
                $user = User::create([
                    'mobile' => $mobile,
                    'firstname' => 'User - ' . $mobile,
                    'lastname' => '',
                    'email' => null, // Email is null for login page users
                    'password' => bcrypt(Str::random(32)), // Random password
                ]);

                // Assign customer role
                $customerRole = Role::firstOrCreate(['name' => 'customer']);
                $user->assignRole($customerRole);

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
                        'reference_type' => User::class,
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

        // Create initial booking history entry (customer self-service)
        // Prepare form data - only include non-null values
        $formData = array_filter([
            'name' => $validated['name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'owner_type' => $validated['owner_type'] ?? null,
            'main_property_type' => $validated['main_property_type'] ?? null,
            'house_number' => $validated['house_number'] ?? null,
            'building_name' => $validated['building_name'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
            'city' => $validated['city'] ?? null,
            'full_address' => $validated['full_address'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'amount' => isset($validated['amount']) ? (int) $validated['amount'] : null,
        ], function($value) {
            return !is_null($value) && $value !== '';
        });

        // Prepare booking details - only include non-null values
        $bookingDetails = array_filter([
            'property_type_id' => $booking->property_type_id,
            'property_sub_type_id' => $booking->property_sub_type_id,
            'bhk_id' => $booking->bhk_id,
            'furniture_type' => $booking->furniture_type,
            'area' => $booking->area,
            'price' => $booking->price,
            'city_id' => $booking->city_id,
            'state_id' => $booking->state_id,
        ], function($value) {
            return !is_null($value) && $value !== '';
        });
        
        BookingHistory::create([
            'booking_id' => $booking->id,
            'from_status' => null,
            'to_status' => 'pending',
            'changed_by' => $user->id,
            'notes' => 'Booking created by customer (self-service)',
            'metadata' => [
                'source' => 'customer_frontend',
                'form_data' => $formData,
                'booking_details' => $bookingDetails,
                'payment_status' => 'paid',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Create a tour for this booking
        Tour::create([
            'booking_id' => $booking->id,
            'name' => 'Tour for Booking #' . $booking->id,
            'title' => 'Property Tour - ' . ($validated['name'] ?? 'Property'),
            'slug' => 'tour-' . $booking->id . '-' . time(),
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
                'email' => 'user_' . $validated['phone'] . '@temp.com', // Temporary email (required by database)
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

        $isNewBooking = !$validated['booking_id'];
        
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

        // Create initial booking history entry for new bookings (customer self-service)
        if ($isNewBooking) {
            // Prepare form data - only include non-null values
            $formData = array_filter([
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'owner_type' => $validated['owner_type'] ?? null,
                'main_property_type' => $validated['main_property_type'] ?? null,
                // Residential fields
                'residential_property_type' => $validated['residential_property_type'] ?? null,
                'residential_furnish' => $validated['residential_furnish'] ?? null,
                'residential_size' => $validated['residential_size'] ?? null,
                'residential_area' => $validated['residential_area'] ?? null,
                // Commercial fields
                'commercial_property_type' => $validated['commercial_property_type'] ?? null,
                'commercial_furnish' => $validated['commercial_furnish'] ?? null,
                'commercial_area' => $validated['commercial_area'] ?? null,
                // Other fields
                'other_looking' => $validated['other_looking'] ?? null,
                'other_option_details' => $validated['other_option_details'] ?? null,
                'other_area' => $validated['other_area'] ?? null,
                // Billing details
                'firm_name' => $validated['firm_name'] ?? null,
                'gst_no' => $validated['gst_no'] ?? null,
            ], function($value) {
                return !is_null($value) && $value !== '';
            });

            // Prepare booking details - only include non-null values
            $bookingDetails = array_filter([
                'property_type_id' => $booking->property_type_id,
                'property_sub_type_id' => $booking->property_sub_type_id,
                'bhk_id' => $booking->bhk_id,
                'furniture_type' => $booking->furniture_type,
                'area' => $booking->area,
                'price' => $booking->price,
            ], function($value) {
                return !is_null($value) && $value !== '';
            });
            
            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $user->id,
                'notes' => 'Booking created by customer (self-service - step by step)',
                'metadata' => [
                    'source' => 'customer_frontend_stepwise',
                    'form_data' => $formData,
                    'booking_details' => $bookingDetails,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Create tour only if this is a new booking (not an update)
        if ($isNewBooking) {
            $tour = Tour::create([
                'booking_id' => $booking->id,
                'name' => 'Tour for Booking #' . $booking->id,
                'slug' => Str::slug('Tour for Booking #' . $booking->id),
                'title' => 'Property Tour - ' . ($validated['name'] ?? 'Property'),
                'slug' => 'tour-' . $booking->id . '-' . time(),
                'status' => 'draft',
                'revision' => 1,
            ]);
            activity('tours')
                ->performedOn($tour)
                ->causedBy($request->user())
                ->withProperties([
                    'event' => 'created',
                    'after' => $tour->toArray(),
                    'booking_id' => $booking->id
                ])
                ->log('Tour created for booking');
            // Create a photographer visit job for this booking
            $job = PhotographerVisitJob::create([
                'booking_id' => $booking->id,
                'tour_id' => $tour->id,
                'photographer_id' => null, // Will be assigned later
                'status' => 'pending',
                'priority' => 'normal',
                'scheduled_date' => $booking->booking_date ?? now()->addDays(1),
                'instructions' => 'Complete photography for property booking #' . $booking->id,
                'created_by' => $request->user()->id ?? null,
            ]);

            // Generate and assign a unique job code
            $job->job_code = 'JOB-' . str_pad($job->id, 6, '0', STR_PAD_LEFT);
            $job->save();

            activity('photographer_visit_jobs')
                ->performedOn($job)
                ->causedBy($request->user())
                ->withProperties([
                    'event' => 'created',
                    'after' => $job->toArray(),
                    'booking_id' => $booking->id
                ])
                ->log('Photographer visit job created for booking');
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
                'update_notes_only' => 'nullable|boolean',
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
                // Check if booking is blocked
                if ($booking->status === 'reschedul_blocked') {
                    $blockedNote = Setting::where('name', 'customer_attempt_note')->first();
                    return response()->json([
                        'success' => false,
                        'message' => $blockedNote?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.'
                    ], 422);
                }
                
                $oldStatus = $booking->status;
                $scheduleChanged = false;
                $updateNotesOnly = $validated['update_notes_only'] ?? false;
                
                // Only update schedule-related fields
                if (isset($validated['scheduled_date'])) {
                    $oldBookingDate = $booking->booking_date;
                    $newBookingDate = $validated['scheduled_date'];
                    
                    // Compare dates properly (handle Carbon dates)
                    $oldDateStr = $oldBookingDate ? (\Carbon\Carbon::parse($oldBookingDate)->format('Y-m-d')) : null;
                    $newDateStr = \Carbon\Carbon::parse($newBookingDate)->format('Y-m-d');
                    $dateChanged = $oldDateStr && $oldDateStr !== $newDateStr;
                    
                    // If update_notes_only is true, only update notes and don't change status/attempts
                    if ($updateNotesOnly && !$dateChanged) {
                        // Only update notes, no status change, no attempt count, no history
                        if (isset($validated['notes']) || isset($validated['booking_notes'])) {
                            $booking->booking_notes = $validated['notes'] ?? $validated['booking_notes'] ?? null;
                        }
                        $booking->updated_by = $user->id;
                        $booking->save();
                        
                        return response()->json([
                            'success' => true,
                            'message' => 'Notes updated successfully.',
                            'booking_id' => $booking->id,
                        ]);
                    }
                    
                    // Date changed or new schedule - proceed with full update
                    
                    // Check if there's an existing photographer assignment
                    // If rescheduling, we need to remove the assignment but keep history
                    $existingAssignees = BookingAssignee::where('booking_id', $booking->id)
                        ->with('user')
                        ->get()
                        ->filter(function($assignee) {
                            return $assignee->user && $assignee->user->hasRole('photographer');
                        });
                    
                    // Store old assignment info for history before deletion
                    $oldAssignmentInfo = null;
                    $assignmentRemoved = false;
                    if ($existingAssignees->isNotEmpty()) {
                        $oldAssignee = $existingAssignees->first();
                        $oldPhotographer = $oldAssignee->user;
                        $oldAssignmentInfo = [
                            'photographer_id' => $oldPhotographer->id ?? null,
                            'photographer_name' => $oldPhotographer->name ?? null,
                            'photographer_phone' => $oldPhotographer->mobile ?? null,
                            'old_assigned_date' => $oldAssignee->date ? \Carbon\Carbon::parse($oldAssignee->date)->format('Y-m-d') : null,
                            'old_assigned_time' => $oldAssignee->time ? \Carbon\Carbon::parse($oldAssignee->time)->format('H:i') : null,
                        ];
                        
                        // Delete all photographer assignments for this booking
                        // This removes the assignment but booking history remains intact
                        foreach ($existingAssignees as $assignee) {
                            $assignee->delete(); // Soft delete
                        }
                        $assignmentRemoved = true;
                    }
                    
                    // Also check and remove PhotographerVisitJob if exists
                    $visitJob = PhotographerVisitJob::where('booking_id', $booking->id)->first();
                    if ($visitJob) {
                        $visitJob->delete(); // Soft delete
                        $assignmentRemoved = true;
                    }
                    
                    // Clear booking_time when rescheduling (photographer assignment removed)
                    // Clear it whenever date changes or assignment is removed
                    // This ensures booking_time is empty when rescheduling
                    if ($dateChanged || $assignmentRemoved || empty($booking->booking_time)) {
                        $booking->booking_time = null;
                    }
                    
                    // Count customer's ACCEPTED schedule attempts (not pending)
                    $attemptCount = BookingHistory::where('booking_id', $booking->id)
                        ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                        ->count();
                    
                    // Get max attempts from settings (default 3)
                    $maxAttempts = Setting::where('name', 'customer_attempt')->first();
                    $maxAttemptsValue = $maxAttempts ? (int) $maxAttempts->value : 3;
                    
                    // Check if customer has exceeded attempts
                    if ($attemptCount >= $maxAttemptsValue) {
                        // Block the booking
                        $booking->status = 'reschedul_blocked';
                        $booking->save();
                        
                        // Create history for blocking
                        BookingHistory::create([
                            'booking_id' => $booking->id,
                            'from_status' => $oldStatus,
                            'to_status' => 'reschedul_blocked',
                            'changed_by' => $user->id,
                            'notes' => 'Booking blocked - Maximum schedule attempts reached',
                            'metadata' => [
                                'step' => 'schedule_blocked',
                                'attempt_count' => $attemptCount,
                                'max_attempts' => $maxAttemptsValue,
                                'blocked_at' => now()->toDateTimeString(),
                            ],
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                        
                        $blockedNote = Setting::where('name', 'customer_attempt_note')->first();
                        return response()->json([
                            'success' => false,
                            'message' => $blockedNote?->value ?? 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.',
                            'blocked' => true,
                            'attempts' => $attemptCount,
                            'max_attempts' => $maxAttemptsValue
                        ], 422);
                    }
                    
                    // Store scheduled date in booking_date field
                    $booking->booking_date = $validated['scheduled_date'];
                    $booking->status = 'schedul_pending'; // Set status to schedule pending
                    $scheduleChanged = true;
                }
                
                // Store notes in booking_notes field
                if (isset($validated['notes']) || isset($validated['booking_notes'])) {
                    $booking->booking_notes = $validated['notes'] ?? $validated['booking_notes'] ?? null;
                }
                $booking->updated_by = $user->id;
                $booking->save();

                // Create history entry for schedule request (only if schedule changed)
                if ($scheduleChanged) {
                    // Count ACCEPTED schedules for metadata
                    $attemptCount = BookingHistory::where('booking_id', $booking->id)
                        ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                        ->count();
                    
                    $maxAttempts = Setting::where('name', 'customer_attempt')->first();
                    $maxAttemptsValue = $maxAttempts ? (int) $maxAttempts->value : 3;
                    
                    // Determine if this is a reschedule (checking if old status was accepted or assigned)
                    $isReschedule = in_array($oldStatus, ['schedul_accepted', 'reschedul_accepted', 'schedul_assign']);
                    
                    // Build notes based on whether it's a reschedule
                    $historyNotes = $isReschedule ? 'Reschedule requested by customer' : 'Schedule requested by customer';
                    
                    // Build metadata
                    $metadata = [
                        'step' => $isReschedule ? 'reschedule_request' : 'schedule_request',
                        'scheduled_date' => $validated['scheduled_date'],
                        'old_booking_date' => $oldBookingDate ? (\Carbon\Carbon::parse($oldBookingDate)->format('Y-m-d')) : null,
                        'new_booking_date' => $booking->booking_date ? (\Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d')) : null,
                        'notes' => $booking->booking_notes,
                        'attempt_number' => $attemptCount,
                        'max_attempts' => $maxAttemptsValue,
                        'remaining_attempts' => $maxAttemptsValue - $attemptCount,
                    ];
                    
                    // Add old assignment info if it existed (for reschedule)
                    if ($isReschedule && $assignmentRemoved) {
                        if (isset($oldAssignmentInfo) && $oldAssignmentInfo) {
                            $metadata['old_assignment'] = $oldAssignmentInfo;
                            $photographerName = $oldAssignmentInfo['photographer_name'] ?? 'Unknown';
                            $historyNotes .= ' - Photographer assignment removed (Photographer: ' . $photographerName . ')';
                        } else {
                            $historyNotes .= ' - Previous photographer assignment removed due to date change';
                        }
                        $metadata['assignment_removed'] = true;
                        $metadata['booking_time_cleared'] = true;
                    }
                    
                    BookingHistory::create([
                        'booking_id' => $booking->id,
                        'from_status' => $oldStatus,
                        'to_status' => 'schedul_pending',
                        'changed_by' => $user->id,
                        'notes' => $historyNotes,
                        'metadata' => array_filter($metadata, function($value) {
                            return !is_null($value) && $value !== '';
                        }),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $scheduleChanged ? 'Schedule request submitted successfully. Awaiting admin approval.' : 'Notes updated successfully.',
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
            if (
                empty($validated['owner_type']) || empty($validated['main_property_type']) ||
                empty($validated['house_number']) || empty($validated['building_name']) ||
                empty($validated['pincode']) || empty($validated['full_address'])
            ) {
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

            $booking = Booking::with(['propertyType', 'propertySubType', 'bhk', 'city', 'state', 'user'])->find($validated['booking_id']);

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
                    'scheduled_date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : ($booking->booking_date instanceof Carbon ? $booking->booking_date->format('Y-m-d') : Carbon::parse($booking->booking_date)->format('Y-m-d'))) : null,
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
            'bookings' => $bookings->map(fn($booking) => $this->formatBookingForGrid($booking)),
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
        if ($areaVal <= 0)
            return 0;

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
                    // Check if payment history exists and is still pending
                    $existingPaymentHistory = PaymentHistory::where('booking_id', $booking->id)
                        ->where('gateway', 'cashfree')
                        ->where('gateway_order_id', $booking->cashfree_order_id)
                        ->whereIn('status', ['pending', 'processing'])
                        ->first();
                    
                    // If payment history exists and matches, return existing session
                    if ($existingPaymentHistory) {
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

            $orderIdFromResponse = $body['order_id'] ?? $orderId;
            $sessionId = $body['payment_session_id'];
            $orderAmount = (int) round($body['order_amount'] ?? $amount);
            
            // Update booking with Cashfree order details (backward compatibility)
            $booking->cashfree_order_id = $orderIdFromResponse;
            $booking->cashfree_payment_session_id = $sessionId;
            $booking->cashfree_payment_status = $body['order_status'] ?? 'CREATED';
            $booking->cashfree_payment_amount = $orderAmount;
            $booking->cashfree_payment_currency = $body['order_currency'] ?? 'INR';
            $booking->cashfree_payment_meta = [
                'customer_id' => $customerId,
            ];
            $booking->cashfree_last_response = $body;
            $booking->price = $booking->price ?: $orderAmount;
            $booking->payment_status = 'pending';
            $booking->save();

            // Create payment history entry for this payment attempt
            PaymentHistory::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'gateway' => 'cashfree',
                'gateway_order_id' => $orderIdFromResponse,
                'gateway_session_id' => $sessionId,
                'status' => 'pending',
                'amount' => $orderAmount * 100, // Convert to paise
                'currency' => $body['order_currency'] ?? 'INR',
                'gateway_response' => $body,
                'gateway_meta' => [
                    'customer_id' => $customerId,
                ],
                'initiated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'notes' => 'Payment session created',
            ]);

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

        $summary = $this->syncBookingWithCashfreeOrder($booking, $orderData, false);

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

    protected function syncBookingWithCashfreeOrder(Booking $booking, array $orderData, bool $sendSMS = true): array
    {
        $orderStatus = strtoupper($orderData['order_status'] ?? 'UNKNOWN');
        $orderId = $orderData['order_id'] ?? $booking->cashfree_order_id;
        $orderAmount = (float) ($orderData['order_amount'] ?? 0);
        $orderCurrency = $orderData['order_currency'] ?? 'INR';
        $payments = $orderData['payments'] ?? [];
        
        // Sort payments by payment time (latest first)
        if (count($payments) > 1) {
            usort($payments, function ($a, $b) {
                $timeA = isset($a['payment_time']) ? strtotime($a['payment_time']) : 0;
                $timeB = isset($b['payment_time']) ? strtotime($b['payment_time']) : 0;
                return $timeB <=> $timeA;
            });
        }
        
        $latestPayment = $payments[0] ?? null;
        $oldStatus = $booking->status;
        $oldPaymentStatus = $booking->payment_status;

        // Update backward compatibility fields (for existing code that relies on them)
        $booking->cashfree_payment_status = $orderStatus;
        $booking->cashfree_payment_amount = (int) round($orderAmount);
        $booking->cashfree_payment_currency = $orderCurrency;
        $booking->cashfree_last_response = $orderData;

        if ($latestPayment) {
            $booking->cashfree_payment_method = $latestPayment['payment_method'] ?? null;
            $booking->cashfree_reference_id = $latestPayment['cf_payment_id'] ?? ($latestPayment['payment_reference_id'] ?? null);
            $booking->cashfree_payment_message = $latestPayment['payment_message'] ?? null;
            if (!empty($latestPayment['payment_time'])) {
                $booking->cashfree_payment_at = Carbon::parse($latestPayment['payment_time']);
            }
        }

        // Process each payment from Cashfree response and create/update payment history entries
        $paymentHistoryEntries = [];
        
        foreach ($payments as $payment) {
            $paymentId = $payment['cf_payment_id'] ?? $payment['payment_reference_id'] ?? null;
            $paymentStatus = strtoupper($payment['payment_status'] ?? $orderStatus);
            $paymentAmount = (float) ($payment['payment_amount'] ?? $orderAmount);
            $paymentMethod = $payment['payment_method'] ?? null;
            $paymentMessage = $payment['payment_message'] ?? null;
            $paymentTime = !empty($payment['payment_time']) ? Carbon::parse($payment['payment_time']) : null;
            
            // Map Cashfree payment status to our payment history status
            $historyStatus = $this->mapCashfreeStatusToPaymentHistoryStatus($paymentStatus);
            
            // Find existing payment history entry by gateway_payment_id or create new one
            $paymentHistory = PaymentHistory::where('booking_id', $booking->id)
                ->where('gateway', 'cashfree')
                ->where(function($query) use ($paymentId, $orderId) {
                    if ($paymentId) {
                        $query->where('gateway_payment_id', $paymentId);
                    } else {
                        $query->where('gateway_order_id', $orderId);
                    }
                })
                ->first();
            
            if (!$paymentHistory) {
                // Create new payment history entry
                $paymentHistory = PaymentHistory::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'gateway' => 'cashfree',
                    'gateway_order_id' => $orderId,
                    'gateway_payment_id' => $paymentId,
                    'status' => $historyStatus,
                    'amount' => (int) round($paymentAmount * 100), // Convert to paise
                    'currency' => $orderCurrency,
                    'payment_method' => $paymentMethod,
                    'gateway_response' => $payment,
                    'gateway_message' => $paymentMessage,
                    'completed_at' => $historyStatus === 'completed' ? ($paymentTime ?? now()) : null,
                    'failed_at' => in_array($historyStatus, ['failed', 'cancelled']) ? ($paymentTime ?? now()) : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'notes' => 'Payment sync from Cashfree callback',
                ]);
            } else {
                // Update existing payment history entry
                $paymentHistory->status = $historyStatus;
                $paymentHistory->amount = (int) round($paymentAmount * 100); // Convert to paise
                $paymentHistory->currency = $orderCurrency;
                $paymentHistory->payment_method = $paymentMethod;
                $paymentHistory->gateway_response = array_merge($paymentHistory->gateway_response ?? [], $payment);
                $paymentHistory->gateway_message = $paymentMessage;
                
                if ($historyStatus === 'completed' && !$paymentHistory->completed_at) {
                    $paymentHistory->completed_at = $paymentTime ?? now();
                }
                if (in_array($historyStatus, ['failed', 'cancelled']) && !$paymentHistory->failed_at) {
                    $paymentHistory->failed_at = $paymentTime ?? now();
                }
                
                $paymentHistory->save();
            }
            
            $paymentHistoryEntries[] = $paymentHistory;
        }

        // If no payments array, but we have order status, update/create a single payment history entry
        if (empty($payments) && $orderId) {
            $paymentHistory = PaymentHistory::where('booking_id', $booking->id)
                ->where('gateway', 'cashfree')
                ->where('gateway_order_id', $orderId)
                ->first();
            
            $historyStatus = $this->mapCashfreeStatusToPaymentHistoryStatus($orderStatus);
            
            if (!$paymentHistory) {
                PaymentHistory::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'gateway' => 'cashfree',
                    'gateway_order_id' => $orderId,
                    'status' => $historyStatus,
                    'amount' => (int) round($orderAmount * 100), // Convert to paise
                    'currency' => $orderCurrency,
                    'gateway_response' => $orderData,
                    'completed_at' => $historyStatus === 'completed' ? now() : null,
                    'failed_at' => in_array($historyStatus, ['failed', 'cancelled']) ? now() : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'notes' => 'Payment sync from Cashfree callback (no payments array)',
                ]);
            } else {
                $paymentHistory->status = $historyStatus;
                $paymentHistory->amount = (int) round($orderAmount * 100);
                $paymentHistory->gateway_response = array_merge($paymentHistory->gateway_response ?? [], $orderData);
                
                if ($historyStatus === 'completed' && !$paymentHistory->completed_at) {
                    $paymentHistory->completed_at = now();
                }
                if (in_array($historyStatus, ['failed', 'cancelled']) && !$paymentHistory->failed_at) {
                    $paymentHistory->failed_at = now();
                }
                
                $paymentHistory->save();
            }
        }

        // Update booking payment status based on aggregated payment history
        $booking->refresh(); // Refresh to get latest payment history
        $booking->updatePaymentStatusFromHistory();

        // Determine final order status for return value
        $finalOrderStatus = $orderStatus;
        if ($latestPayment) {
            $finalOrderStatus = strtoupper($latestPayment['payment_status'] ?? $orderStatus);
        }

        // Create history entry for payment callback with complete booking data
        // Always create history when callback is triggered to track all payment attempts
        $statusChanged = ($oldPaymentStatus !== $booking->payment_status || $oldStatus !== $booking->status);
        
        // Check if history already exists for this exact callback to prevent duplicates from page refresh
        $existingHistory = BookingHistory::where('booking_id', $booking->id)
            ->where('notes', 'LIKE', '%' . $orderStatus . '%')
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();
        
        if (!$existingHistory) {
            // Payment data
            $paymentData = array_filter([
                'order_id' => $booking->cashfree_order_id,
                'order_status' => $orderStatus,
                'payment_status' => $booking->payment_status,
                'amount' => $booking->cashfree_payment_amount,
                'currency' => $booking->cashfree_payment_currency,
                'payment_method' => $booking->cashfree_payment_method,
                'reference_id' => $booking->cashfree_reference_id,
                'payment_message' => $booking->cashfree_payment_message,
                'payment_at' => optional($booking->cashfree_payment_at)->toDateTimeString(),
            ], function($value) {
                return !is_null($value) && $value !== '';
            });

            // Property data
            $propertyData = array_filter([
                'property_type' => $booking->propertyType?->name,
                'property_sub_type' => $booking->propertySubType?->name,
                'bhk' => $booking->bhk?->name,
                'furniture_type' => $booking->furniture_type,
                'area' => $booking->area,
                'price' => $booking->price,
                'owner_type' => $booking->owner_type,
                'firm_name' => $booking->firm_name,
                'gst_no' => $booking->gst_no,
            ], function($value) {
                return !is_null($value) && $value !== '';
            });

            // Address data
            $addressData = array_filter([
                'house_no' => $booking->house_no,
                'building' => $booking->building,
                'society_name' => $booking->society_name,
                'address_area' => $booking->address_area,
                'landmark' => $booking->landmark,
                'full_address' => $booking->full_address,
                'pin_code' => $booking->pin_code,
                'city' => $booking->city?->name,
                'state' => $booking->state?->name,
            ], function($value) {
                return !is_null($value) && $value !== '';
            });

            // Set appropriate message based on payment status
            if ($orderStatus === 'PAID') {
                $notes = 'Payment successful - Booking confirmed';
            } elseif (in_array($orderStatus, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED'])) {
                $notes = 'Payment failed - ' . ($booking->cashfree_payment_message ?: 'Transaction declined');
            } else {
                $notes = 'Payment pending - Awaiting payment confirmation';
            }

            BookingHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $oldStatus,
                'to_status' => $booking->status,
                'changed_by' => $booking->user_id,
                'notes' => $notes,
                'metadata' => [
                    'step' => 'payment_callback',
                    'payment_data' => $paymentData,
                    'property_data' => $propertyData,
                    'address_data' => $addressData,
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $booking->payment_status,
                    'payment_gateway_response' => $orderData, // Full Cashfree response
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Send SMS notification for successful payment (check if any payment was successful)
            $hasSuccessfulPayment = $booking->paymentHistories()->where('status', 'completed')->exists();
            if (($orderStatus === 'PAID' || $hasSuccessfulPayment) && $booking->user && $booking->user->mobile && $sendSMS) {
                try {
                    // PROPPIK: Your order is confirmed. You can now schedule your appointment on ##LINK##. – CREART
                    // Template ID: 69295ee79cb8142aae77f2a2
                    
                    $mobile = $booking->user->mobile;
                    
                    // Ensure mobile has country code (91 for India)
                    if (!str_starts_with($mobile, '91')) {
                        $mobile = '91' . $mobile;
                    }
                    
                    // Prepare SMS parameters for MSG91 template
                    $smsParams = [
                        'LINK' => 'https://proppik.com/'
                    ];
                    
                    // Send SMS using MSG91 order_confirmation template
                    $this->smsService->send(
                        $mobile,                        // Mobile number with country code
                        'order_confirmation',           // Template key from config/msg91.php
                        $smsParams,                     // Template parameters
                        [
                            'type' => 'manual',
                            'reference_type' => 'App\Models\Booking',
                            'reference_id' => $booking->id,
                            'notes' => 'Order confirmation SMS sent after successful payment'
                        ]
                    );
                    
                    \Log::info('Order confirmation SMS sent successfully', [
                        'booking_id' => $booking->id,
                        'mobile' => $mobile,
                        'template' => 'order_confirmation',
                        'template_id' => '69295ee79cb8142aae77f2a2',
                        'link' => 'https://proppik.com/'
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the payment process
                    \Log::error('Failed to send order confirmation SMS', [
                        'booking_id' => $booking->id,
                        'mobile' => $booking->user->mobile ?? 'N/A',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Send SMS notification for failed payment
            if (in_array($orderStatus, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED', 'ACTIVE']) && $booking->user && $booking->user->mobile && $sendSMS) {
                try {
                    // PROPPIK: Your payment could not be processed. Please try again or use another method. – CREART
                    // Template ID: 69295eabe5d99077c61b7ac1
                    
                    $mobile = $booking->user->mobile;
                    
                    // Ensure mobile has country code (91 for India)
                    if (!str_starts_with($mobile, '91')) {
                        $mobile = '91' . $mobile;
                    }
                    
                    // No additional parameters needed for payment_failed template
                    $smsParams = [];
                    
                    // Send SMS using MSG91 payment_failed template
                    $this->smsService->send(
                        $mobile,                        // Mobile number with country code
                        'payment_failed',               // Template key from config/msg91.php
                        $smsParams,                     // Template parameters (empty for this template)
                        [
                            'type' => 'manual',
                            'reference_type' => 'App\Models\Booking',
                            'reference_id' => $booking->id,
                            'notes' => 'Payment failed notification SMS sent to customer'
                        ]
                    );
                    
                    \Log::info('Payment failed SMS sent successfully', [
                        'booking_id' => $booking->id,
                        'mobile' => $mobile,
                        'template' => 'payment_failed',
                        'template_id' => '69295eabe5d99077c61b7ac1',
                        'order_status' => $orderStatus,
                        'payment_message' => $booking->cashfree_payment_message
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the payment process
                    \Log::error('Failed to send payment failed SMS', [
                        'booking_id' => $booking->id,
                        'mobile' => $booking->user->mobile ?? 'N/A',
                        'order_status' => $orderStatus,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Get aggregated payment data from payment history
        $totalPaid = $booking->total_paid;
        $latestPaymentHistory = $booking->latestPaymentHistory;
        
        return [
            'booking_id' => $booking->id,
            'order_id' => $orderId,
            'order_status' => $finalOrderStatus,
            'amount' => $orderAmount,
            'currency' => $orderCurrency,
            'payment_method' => $latestPaymentHistory?->payment_method ?? $booking->cashfree_payment_method,
            'reference_id' => $latestPaymentHistory?->gateway_payment_id ?? $booking->cashfree_reference_id,
            'payment_at' => optional($latestPaymentHistory?->completed_at ?? $booking->cashfree_payment_at)->toDateTimeString(),
            'status_message' => $latestPaymentHistory?->gateway_message ?? $booking->cashfree_payment_message,
            'total_paid' => $totalPaid / 100, // Convert from paise to rupees
            'remaining_amount' => $booking->remaining_amount_in_rupees,
            'payment_history_count' => $booking->paymentHistories()->count(),
            'raw' => $orderData,
        ];
    }

    /**
     * Map Cashfree payment status to PaymentHistory status
     */
    protected function mapCashfreeStatusToPaymentHistoryStatus(string $cashfreeStatus): string
    {
        $status = strtoupper(trim($cashfreeStatus));
        
        return match($status) {
            'PAID', 'SUCCESS', 'SUCCESSFUL' => 'completed',
            'FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED' => 'failed',
            'CANCELLED', 'CANCELED' => 'cancelled',
            'REFUNDED' => 'refunded',
            'PARTIALLY_REFUNDED' => 'partially_refunded',
            'ACTIVE', 'CREATED', 'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            default => 'pending',
        };
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
    public function bookingDashboard(){
        // Fetch authenticated user's bookings with related data
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // If user has no bookings, redirect to setup page to create first booking
        if ($bookings->isEmpty()) {
            return redirect()->route('frontend.setup');
        }
        
        // Get property types and BHK for edit modal (same as setup page)
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id', 'name', 'icon']);
        $bhk = BHK::all();

        // Get price settings for dynamic pricing
        $priceSettings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();

        return view('frontend.booking-dashboard', compact('bookings', 'types', 'bhk', 'priceSettings'));
    }

    /**
     * Booking Dashboard V2 - Redesigned with Analytics and KPIs
     */
    public function bookingDashboardV2(){
        // Fetch authenticated user's bookings with related data
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'assignees.user', 'histories'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Add attempt count and history to each booking for JavaScript
        $bookings->each(function($booking) {
            $booking->history = $booking->histories->map(function($h) {
                return ['to_status' => $h->to_status];
            })->toArray();
        });
        
        // If user has no bookings, redirect to setup page to create first booking
        if ($bookings->isEmpty()) {
            return redirect()->route('frontend.setup');
        }
        
        // Calculate Analytics & KPIs
        $totalBookings = $bookings->count();
        $paidBookings = $bookings->where('payment_status', 'paid')->count();
        $pendingBookings = $bookings->where('payment_status', '!=', 'paid')->count();
        $totalAmount = $bookings->where('payment_status', 'paid')->sum('cashfree_payment_amount') ?? $bookings->where('payment_status', 'paid')->sum('price');
        
        // Status breakdown
        $statusBreakdown = [
            'scheduled' => $bookings->whereIn('status', ['schedul_accepted', 'reschedul_accepted'])->count(),
            'pending' => $bookings->whereIn('status', ['schedul_pending', 'reschedul_pending'])->count(),
            'declined' => $bookings->whereIn('status', ['schedul_decline', 'reschedul_decline'])->count(),
            'not_scheduled' => $bookings->whereNotIn('status', ['schedul_accepted', 'reschedul_accepted', 'schedul_pending', 'reschedul_pending', 'schedul_decline', 'reschedul_decline'])->where('payment_status', 'paid')->count(),
        ];
        
        // Recent activity (last 5 bookings)
        $recentBookings = $bookings->take(5);
        
        // Get property types and BHK for edit modal
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id', 'name', 'icon']);
        $bhk = BHK::all();

        // Get price settings for dynamic pricing
        $priceSettings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();
        
        // Get max attempts setting
        $maxAttemptsSetting = Setting::where('name', 'customer_attempt')->first();
        $maxAttempts = $maxAttemptsSetting ? (int) $maxAttemptsSetting->value : 3;

        return view('frontend.booking-dashboard-v2', compact(
            'bookings', 
            'types', 
            'bhk', 
            'priceSettings',
            'totalBookings',
            'paidBookings',
            'pendingBookings',
            'totalAmount',
            'statusBreakdown',
            'recentBookings',
            'maxAttempts'
        ));
    }

    /**
     * Show individual booking details page
     */
    public function showBooking($id)
    {
        $booking = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'assignees.user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        
        // If booking doesn't exist or doesn't belong to the user, redirect with error
        if (!$booking) {
            return redirect()->route('frontend.booking-dashboard')
                ->with('error', 'This is not your booking. You can only view your own bookings.');
        }

        // Calculate estimated price
        $estimatedPrice = $this->calculateEstimate($booking->area);
        $priceToShow = $booking->price ?? $estimatedPrice;


        // Get booking history
        $history = BookingHistory::where('booking_id', $booking->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate attempt count and max attempts (same logic as dashboard)
        $status = $booking->status ?? 'pending';
        $isBlocked = $status === 'reschedul_blocked';
        $attemptCount = 0;
        $maxAttempts = 3;
        
        // Get decline reason or admin notes from latest history entry
        $declineReason = null;
        $adminNotes = null;
        
        if ($history->isNotEmpty()) {
            if (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                // Get latest decline history entry
                $declineHistory = $history->where('to_status', $status)->first();
                if ($declineHistory && isset($declineHistory->metadata['reason'])) {
                    $declineReason = $declineHistory->metadata['reason'];
                }
            } elseif (in_array($status, ['schedul_accepted', 'reschedul_accepted'])) {
                // Get latest accepted history entry
                $acceptedHistory = $history->where('to_status', $status)->first();
                if ($acceptedHistory && isset($acceptedHistory->metadata['admin_notes'])) {
                    $adminNotes = $acceptedHistory->metadata['admin_notes'];
                }
            }
        }
        
        if ($isBlocked || in_array($status, ['schedul_pending', 'schedul_accepted', 'schedul_decline', 'reschedul_pending', 'reschedul_accepted', 'reschedul_decline'])) {
            // Count accepted attempts
            $acceptedAttempts = BookingHistory::where('booking_id', $booking->id)
                ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                ->count();
            
            // If status is pending, add 1 for the current pending attempt
            if (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                $attemptCount = $acceptedAttempts + 1;
            } else {
                // For accepted or declined, use the accepted count
                $attemptCount = $acceptedAttempts;
            }
            
            $maxAttemptsSetting = Setting::where('name', 'customer_attempt')->first();
            $maxAttempts = $maxAttemptsSetting ? (int) $maxAttemptsSetting->value : 3;
        }

        // Format status text
        $statusText = match($status) {
            'schedul_pending' => 'Pending Approval',
            'schedul_accepted' => 'Scheduled',
            'schedul_decline' => 'Declined',
            'reschedul_pending' => 'Reschedule Pending',
            'reschedul_accepted' => 'Rescheduled',
            'reschedul_decline' => 'Reschedule Declined',
            'reschedul_blocked' => 'Blocked',
            default => ucfirst(str_replace('_', ' ', $status))
        };

        // Get property types and BHK for edit modal (same as dashboard)
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id', 'name', 'icon']);
        $bhk = BHK::all();

        // Get price settings for dynamic pricing
        $priceSettings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();

        return view('frontend.booking-show', compact('booking', 'priceToShow', 'history', 'attemptCount', 'maxAttempts', 'statusText', 'isBlocked', 'types', 'bhk', 'priceSettings', 'declineReason', 'adminNotes'));
    }

    /**
     * Show individual booking details page V2 - Redesigned with Analytics and Timeline
     */
    public function showBookingV2($id)
    {
        $booking = Booking::with(['user', 'propertyType', 'propertySubType', 'bhk', 'city', 'state', 'assignees.user', 'histories'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        
        // If booking doesn't exist or doesn't belong to the user, redirect with error
        if (!$booking) {
            return redirect()->route('frontend.booking-dashboard')
                ->with('error', 'This is not your booking. You can only view your own bookings.');
        }

        // Calculate estimated price
        $estimatedPrice = $this->calculateEstimate($booking->area);
        $priceToShow = $booking->price ?? $estimatedPrice;

        // Get booking history with user information
        $history = BookingHistory::where('booking_id', $booking->id)
            ->with('changedBy')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate attempt count and max attempts
        $status = $booking->status ?? 'pending';
        $isBlocked = $status === 'reschedul_blocked';
        $attemptCount = 0;
        $maxAttempts = 3;
        
        // Get decline reason or admin notes from latest history entry
        $declineReason = null;
        $adminNotes = null;
        
        if ($history->isNotEmpty()) {
            if (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                // Get latest decline history entry
                $declineHistory = $history->where('to_status', $status)->last();
                if ($declineHistory && isset($declineHistory->metadata['reason'])) {
                    $declineReason = $declineHistory->metadata['reason'];
                }
            } elseif (in_array($status, ['schedul_accepted', 'reschedul_accepted'])) {
                // Get latest accepted history entry
                $acceptedHistory = $history->where('to_status', $status)->last();
                if ($acceptedHistory && isset($acceptedHistory->metadata['admin_notes'])) {
                    $adminNotes = $acceptedHistory->metadata['admin_notes'];
                }
            }
        }
        
        if ($isBlocked || in_array($status, ['schedul_pending', 'schedul_accepted', 'schedul_decline', 'reschedul_pending', 'reschedul_accepted', 'reschedul_decline'])) {
            // Count accepted attempts
            $acceptedAttempts = BookingHistory::where('booking_id', $booking->id)
                ->whereIn('to_status', ['schedul_accepted', 'reschedul_accepted'])
                ->count();
            
            // If status is pending, add 1 for the current pending attempt
            if (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                $attemptCount = $acceptedAttempts + 1;
            } else {
                // For accepted or declined, use the accepted count
                $attemptCount = $acceptedAttempts;
            }
            
            $maxAttemptsSetting = Setting::where('name', 'customer_attempt')->first();
            $maxAttempts = $maxAttemptsSetting ? (int) $maxAttemptsSetting->value : 3;
        }

        // Format status text
        $statusText = match($status) {
            'schedul_pending' => 'Pending Approval',
            'schedul_accepted' => 'Scheduled',
            'schedul_decline' => 'Declined',
            'reschedul_pending' => 'Reschedule Pending',
            'reschedul_accepted' => 'Rescheduled',
            'reschedul_decline' => 'Reschedule Declined',
            'reschedul_blocked' => 'Blocked',
            default => ucfirst(str_replace('_', ' ', $status))
        };

        // Get property types and BHK for edit modal
        $types = PropertyType::with(['subTypes:id,property_type_id,name,icon'])->get(['id', 'name', 'icon']);
        $bhk = BHK::all();

        // Get price settings for dynamic pricing
        $priceSettings = Setting::whereIn('name', ['base_price', 'base_area', 'extra_area', 'extra_area_price'])
            ->pluck('value', 'name')
            ->toArray();

        // Calculate Analytics & Insights
        $daysSinceCreated = $booking->created_at->diffInDays(now());
        $daysUntilScheduled = $booking->booking_date ? now()->diffInDays(\Carbon\Carbon::parse($booking->booking_date), false) : null;
        $isPaymentPaid = ($booking->payment_status ?? 'pending') === 'paid';
        $isReadyForPayment = $booking->isReadyForPayment();
        $hasCompletePropertyData = $booking->hasCompletePropertyData();
        $hasCompleteAddressData = $booking->hasCompleteAddressData();

        
        // Calculate completion percentage
        $completionFields = [
            'property_type_id' => $booking->property_type_id,
            'property_sub_type_id' => $booking->property_sub_type_id,
            'area' => $booking->area,
            'house_no' => $booking->house_no,
            'building' => $booking->building,
            'pin_code' => $booking->pin_code,
            'full_address' => $booking->full_address,
        ];
        $completedFields = count(array_filter($completionFields));
        $totalFields = count($completionFields);
        $completionPercentage = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;

        // Get next steps suggestions
        $nextSteps = [];
        if (!$isPaymentPaid) {
            if (!$isReadyForPayment) {
                if (!$hasCompletePropertyData) {
                    $nextSteps[] = ['icon' => 'fa-building', 'title' => 'Complete Property Details', 'description' => 'Add property type, size, and area information', 'action' => 'Edit', 'priority' => 'high'];
                }
                if (!$hasCompleteAddressData) {
                    $nextSteps[] = ['icon' => 'fa-location-dot', 'title' => 'Complete Address Information', 'description' => 'Add house number, building name, pincode, and full address', 'action' => 'Edit', 'priority' => 'high'];
                }
            } else {
                $nextSteps[] = ['icon' => 'fa-credit-card', 'title' => 'Make Payment', 'description' => 'Complete payment to proceed with scheduling', 'action' => 'Pay Now', 'priority' => 'high'];
            }
        } else {
            if ($isBlocked) {
                $nextSteps[] = ['icon' => 'fa-headset', 'title' => 'Contact Support', 'description' => 'Reached maximum attempts. Contact admin for assistance', 'action' => 'Contact', 'priority' => 'high'];
            } elseif (in_array($status, ['schedul_pending', 'reschedul_pending'])) {
                $nextSteps[] = ['icon' => 'fa-clock', 'title' => 'Wait for Approval', 'description' => 'Your schedule request is pending admin approval', 'action' => 'View Status', 'priority' => 'medium'];
            } elseif (in_array($status, ['schedul_decline', 'reschedul_decline'])) {
                if ($attemptCount < $maxAttempts) {
                    $nextSteps[] = ['icon' => 'fa-calendar-plus', 'title' => 'Request Again', 'description' => 'You can request a new schedule date', 'action' => 'Schedule', 'priority' => 'high'];
                }
            } elseif (!in_array($status, ['schedul_accepted', 'reschedul_accepted']) || !$booking->booking_date) {
                $nextSteps[] = ['icon' => 'fa-calendar-check', 'title' => 'Schedule Appointment', 'description' => 'Select a date for your property photography', 'action' => 'Schedule', 'priority' => 'high'];
            } else {
                $nextSteps[] = ['icon' => 'fa-calendar-edit', 'title' => 'Manage Schedule', 'description' => 'View or reschedule your appointment if needed', 'action' => 'View', 'priority' => 'low'];
            }
        }

        return view('frontend.booking-show-v2', compact(
            'booking', 
            'priceToShow', 
            'history', 
            'attemptCount', 
            'maxAttempts', 
            'status',
            'statusText', 
            'isBlocked', 
            'types', 
            'bhk',
            'priceSettings', 
            'declineReason', 
            'adminNotes',
            'daysSinceCreated',
            'daysUntilScheduled',
            'isPaymentPaid',
            'isReadyForPayment',
            'hasCompletePropertyData',
            'hasCompleteAddressData',
            'completionPercentage',
            'nextSteps'
        ));
    }

    /**
     * Display user profile page
     */
    public function profile()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('frontend.login');
        }

        // Get user's booking count
        $bookingCount = Booking::where('user_id', $user->id)->count();
        
        // Get user's total bookings
        $bookings = Booking::where('user_id', $user->id)
            ->with(['propertyType', 'propertySubType', 'city', 'state'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.profile', compact('user', 'bookingCount', 'bookings'));
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
