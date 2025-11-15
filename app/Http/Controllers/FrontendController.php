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
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class FrontendController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }

    public function setup()
    {
        return view('frontend.setup');
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

                Log::info('✅ NEW USER CREATED', [
                    'timestamp' => now()->toDateTimeString(),
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'status' => 'User created successfully'
                ]);
            } else {
                Log::info('👤 EXISTING USER FOUND', [
                    'timestamp' => now()->toDateTimeString(),
                    'user_id' => $user->id,
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'mobile' => $mobile,
                    'status' => 'User already exists'
                ]);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Save OTP to user table with 5 minute expiry
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(5),
            ]);

            // Log OTP for testing (save to log file)
            Log::info('📱 OTP GENERATED AND SENT', [
                'timestamp' => now()->toDateTimeString(),
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5)->toDateTimeString(),
                'user_status' => $userStatus,
                'message' => 'OTP saved to database and queued for SMS'
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
        
        // OTP is valid - clear it and mark mobile as verified
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'mobile_verified_at' => $user->mobile_verified_at ?? now(),
        ]);

        Log::info('OTP verified successfully', [
            'user_id' => $user->id,
            'mobile' => $mobile,
        ]);

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

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'property_type_id' => $propertyData['property_type_id'],
            'property_sub_type_id' => $propertyData['property_sub_type_id'],
            'bhk_id' => $propertyData['bhk_id'],
            'city_id' => $city->id,
            'state_id' => $city->state_id,
            'booking_date' => now(),
            'status' => 'pending',
            'payment_status' => 'paid',
            'amount' => $validated['amount'],
            'furnish_type' => $propertyData['furnish_type'],
            'area' => $propertyData['area'],
            'house_number' => $validated['house_number'],
            'building_name' => $validated['building_name'],
            'pincode' => $validated['pincode'],
            'full_address' => $validated['full_address'],
            'owner_type' => $validated['owner_type'],
            'property_category' => $validated['main_property_type'],
            'other_details' => $propertyData['other_details'],
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Redirect with success message
        return redirect()->route('frontend.index')->with('success', 'Booking submitted successfully! Our team will contact you soon.');
    }

    private function extractPropertyData(Request $request, $mainType)
    {
        $data = [
            'property_type_id' => null,
            'property_sub_type_id' => null,
            'bhk_id' => null,
            'furnish_type' => null,
            'area' => null,
            'other_details' => null,
        ];

        if ($mainType === 'Residential') {
            $propertyTypeName = $request->input('residential_property_type', 'Apartment');
            $propertyType = PropertyType::firstOrCreate(['name' => $propertyTypeName]);
            $data['property_type_id'] = $propertyType->id;

            $data['furnish_type'] = $request->input('residential_furnish');
            $data['area'] = $request->input('residential_area');

            // Handle BHK
            $bhkSize = $request->input('residential_size');
            if ($bhkSize) {
                $bhk = BHK::firstOrCreate(['size' => $bhkSize]);
                $data['bhk_id'] = $bhk->id;
            }
        } elseif ($mainType === 'Commercial') {
            $propertyTypeName = $request->input('commercial_property_type', 'Office');
            $propertyType = PropertyType::firstOrCreate(['name' => $propertyTypeName]);
            $data['property_type_id'] = $propertyType->id;

            $data['furnish_type'] = $request->input('commercial_furnish');
            $data['area'] = $request->input('commercial_area');
        } elseif ($mainType === 'Other') {
            $otherLooking = $request->input('other_looking', 'Other');
            $propertyType = PropertyType::firstOrCreate(['name' => 'Other']);
            $data['property_type_id'] = $propertyType->id;

            $data['area'] = $request->input('other_area');
            $data['other_details'] = json_encode([
                'looking_for' => $otherLooking,
                'description' => $request->input('other_description'),
            ]);
        }

        return $data;
    }
}
