<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'India', 'country_code' => 'IN', 'dial_code' => '+91', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'United States', 'country_code' => 'US', 'dial_code' => '+1', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'United Kingdom', 'country_code' => 'GB', 'dial_code' => '+44', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Canada', 'country_code' => 'CA', 'dial_code' => '+1', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Australia', 'country_code' => 'AU', 'dial_code' => '+61', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'New Zealand', 'country_code' => 'NZ', 'dial_code' => '+64', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'United Arab Emirates', 'country_code' => 'AE', 'dial_code' => '+971', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Saudi Arabia', 'country_code' => 'SA', 'dial_code' => '+966', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Singapore', 'country_code' => 'SG', 'dial_code' => '+65', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Malaysia', 'country_code' => 'MY', 'dial_code' => '+60', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Thailand', 'country_code' => 'TH', 'dial_code' => '+66', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Indonesia', 'country_code' => 'ID', 'dial_code' => '+62', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Philippines', 'country_code' => 'PH', 'dial_code' => '+63', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Germany', 'country_code' => 'DE', 'dial_code' => '+49', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'France', 'country_code' => 'FR', 'dial_code' => '+33', 'flag_icon' => null, 'is_active' => true],
            ['name' => 'Spain', 'country_code' => 'ES', 'dial_code' => '+34', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Italy', 'country_code' => 'IT', 'dial_code' => '+39', 'flag_icon' => null, 'is_active' => false],
            ['name' => 'Japan', 'country_code' => 'JP', 'dial_code' => '+81', 'flag_icon' => null, 'is_active' => false],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['country_code' => $country['country_code']],
                $country
            );
        }
    }
}
