<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsRolesSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Permission management
            'permission_view',
            'permission_create',
            'permission_edit',
            'permission_delete',
            'permission_assign',

            // Role management
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            'role_assign_permissions',

            // User management
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'user_manage_roles',

            // Activity management
            'activity_view',
            'activity_manage',

            // Media management
            'media_view',
            'media_upload',
            'media_delete',

            // Scheduler management
            'scheduler_view',
            'scheduler_create',
            'scheduler_edit',
            'scheduler_delete',

            // Portfolio view of the setup
            'portfolio_view',
            'portfolio_create',
            'portfolio_edit',
            'portfolio_delete',
            
            //booking 
            'booking_view',
            'booking_create',
            'booking_edit',
            'booking_delete',

            // Holiday management
            'holiday_view',
            'holiday_create',
            'holiday_edit',
            'holiday_delete',

            // Setting management
            'setting_view',
            'setting_create',
            'setting_edit',
            'setting_delete',

            // QR management
            'qr_view',
            'qr_create',
            'qr_edit',
            'qr_delete',

            // Tour management
            'tour_view',
            'tour_create',
            'tour_edit',
            'tour_delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $brokerRole = Role::firstOrCreate(['name' => 'broker', 'guard_name' => 'web']);

        $adminRole->syncPermissions(Permission::all());

        $defaultBrokerPermissions = Permission::whereIn('name', [
            'permission_view',
            'permission_create',
            'permission_edit',
            'permission_delete',
            'permission_assign',

            // role 
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            'role_assign_permissions',

            // user
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'user_manage_roles',
        ])->get();

        $brokerRole->syncPermissions($defaultBrokerPermissions);
    }
}


