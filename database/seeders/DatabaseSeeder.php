<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {   // Seed base permissions and roles
        $this->call(PermissionsRolesSeeder::class);

        // Seed property types and subtypes
        $this->call(PropertyTypeSeeder::class);
        $this->call(PropertySubTypeSeeder::class);

        // Seed BHK data
        $this->call(BHKSeeder::class);

        // Seed location data
        $this->call(StateSeeder::class);
        $this->call(CitySeeder::class);


        // Seed bookings
        // $this->call(BookingSeeder::class);

        // Seed QR codes
        // $this->call(QRSeeder::class);

        $user = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'firstname' => 'admin',
                'lastname' => 'User',
                'mobile' => 9876543210,
                'email_verified_at' => now(),
                'mobile_verified_at' => now(),
                'password' => Hash::make('123456'),
                'remember_token' => Str::random(10),
            ]
        );

        // assign admin role to demo user for access
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$user->hasRole('admin')) {
            $user->assignRole($adminRole);
        }

    }
}
