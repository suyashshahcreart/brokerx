<?php

namespace Database\Seeders;

use App\Models\BHK;
use App\Models\Booking;
use App\Models\City;
use App\Models\PropertySubType;
use App\Models\PropertyType;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $users = User::all();
        $propertyTypes = PropertyType::all();
        $propertySubTypes = PropertySubType::all();
        $bhks = BHK::all();
        $cities = City::all();
        $states = State::all();

        // Check if we have necessary data
        if ($users->isEmpty() || $propertyTypes->isEmpty() || $propertySubTypes->isEmpty()) {
            $this->command->warn('Cannot seed bookings: Missing required data (users, property types, or subtypes)');
            return;
        }

        $furnitureTypes = ['Furnished', 'Semi-Furnished', 'Unfurnished'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        // Create 10 sample bookings
        for ($i = 0; $i < 5; $i++) {
            $city = $cities->isNotEmpty() ? $cities->random() : null;
            $state = $states->isNotEmpty() ? $states->random() : null;

            Booking::create([
                'user_id' => $users->random()->id,
                'property_type_id' => $propertyTypes->random()->id,
                'property_sub_type_id' => $propertySubTypes->random()->id,
                'bhk_id' => $bhks->isNotEmpty() ? $bhks->random()->id : null,
                'city_id' => $city?->id,
                'state_id' => $state?->id,
                'furniture_type' => fake()->randomElement($furnitureTypes),
                'area' => fake()->numberBetween(500, 5000),
                'price' => fake()->numberBetween(1000000, 50000000),
                'house_no' => fake()->buildingNumber(),
                'building' => fake()->optional()->streetName() . ' Building',
                'society_name' => fake()->optional()->company() . ' Society',
                'address_area' => fake()->streetName(),
                'landmark' => fake()->optional()->streetAddress(),
                'full_address' => fake()->address(),
                'pin_code' => fake()->numerify('######'),
                'booking_date' => fake()->dateTimeBetween('-30 days', 'now'),
                'payment_status' => fake()->randomElement($paymentStatuses),
                'status' => fake()->randomElement($statuses),
                'created_by' => $users->random()->id,
            ]);
        }

        $this->command->info('Created 10 sample bookings successfully.');
    }
}
