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
    {
        // Seed base permissions and roles
        $this->call(PermissionsRolesSeeder::class);

        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'demo@user.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        // assign admin role to demo user for access
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->assignRole($adminRole);
        }
    }
}
