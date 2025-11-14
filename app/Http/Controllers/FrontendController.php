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
