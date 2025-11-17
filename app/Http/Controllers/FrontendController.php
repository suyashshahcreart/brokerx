<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\BHK;
use App\Models\PropertyType;
use App\Models\PropertySubType;
use App\Services\CashfreeService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class FrontendController extends Controller
{
    protected CashfreeService $cashfree;

    public function __construct(CashfreeService $cashfree)
    {
        $this->cashfree = $cashfree;
    }
    public function index()
    {
        return view('frontend.index');
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

            // TODO: Integrate SMS gateway here
            // Example: SMS::send($mobile, "Your OTP is: {$otp}. Valid for 5 minutes.");

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $isNewUser 
                    ? 'Account created! OTP sent to your mobile number.' 
                    : 'OTP sent to your registered mobile number.',
                'data' => [
                    'is_new_user' => $isNewUser,
                    'user_status' => $userStatus,
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
            'other_description' => 'nullable|string',
            'other_area' => 'nullable|numeric|min:1',
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
        $booking->area = $mapping['area'] ?? 0;
        $booking->price = $this->calculateEstimate($mapping['area'] ?? 0);
        $booking->payment_status = $booking->payment_status ?: 'pending';
        $booking->status = 'pending';
        $booking->updated_by = $user->id;
        $booking->save();

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
     * Step 4 -> Get booking summary
     */
    public function getBookingSummary(Request $request)
    {
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
                'area' => $booking->area,
                'other_details' => $booking->other_details ?? null,
                'house_number' => $booking->house_no,
                'building_name' => $booking->building ?? null,
                'city' => $booking->city?->name,
                'pincode' => $booking->pin_code,
                'full_address' => $booking->full_address,
                'price_estimate' => $this->calculateEstimate($booking->area),
            ],
        ]);
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
        $main = $data['main_property_type'];
        $propertyType = PropertyType::where('name', $main)->first();
        $propertyTypeId = $propertyType?->id;

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
            $otherDetails = $data['other_description'] ?? null;
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
            'area' => $area,
            'other_details' => $otherDetails,
        ];
    }

    /**
     * Simple dynamic price estimator (same logic as frontend)
     */
    protected function calculateEstimate($area): int
    {
        $areaVal = (int) $area;
        if ($areaVal <= 0) return 0;
        $baseArea = 1500;
        $basePrice = 599;
        $extraBlockPrice = 200;
        $price = $basePrice;
        if ($areaVal > $baseArea) {
            $extra = $areaVal - $baseArea;
            $blocks = (int) ceil($extra / 500);
            $price += $blocks * $extraBlockPrice;
        }
        return $price;
    }

    /**
     * Step 5 -> Create Cashfree order & session id
     */
    public function createCashfreeSession(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $booking = Booking::with('user')->findOrFail($validated['booking_id']);

        if ($booking->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This booking is already paid.',
            ], 422);
        }

        $amount = $booking->price ?: $this->calculateEstimate($booking->area);

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate payment amount for this booking.',
            ], 422);
        }

        $customer = $booking->user;
        $customerName = trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) ?: 'Customer';
        $customerEmail = $customer->email ?: 'customer' . $booking->id . '@example.com';
        $customerPhone = $customer->mobile ?: $request->input('phone');

        if (empty($customerPhone)) {
            return response()->json([
                'success' => false,
                'message' => 'Customer phone number missing for payment.',
            ], 422);
        }

        $orderId = $booking->cashfree_order_id ?: 'bk_' . $booking->id . '_' . Str::upper(Str::random(6));
        $customerId = 'cust_' . ($customer?->id ?? $booking->id);
        $returnUrl = config('cashfree.return_url') ?: route('frontend.cashfree.callback');

        $payload = [
            'order_id' => $orderId,
            'order_amount' => round($amount, 2),
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
        
        return view('frontend.booking-dashboard', compact('bookings'));
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
}
