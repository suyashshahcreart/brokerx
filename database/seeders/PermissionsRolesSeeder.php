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

            // Customer management
            'customer_view',
            'customer_create',
            'customer_edit',
            'customer_delete',

            // Activity management
            'activity_view',
            'activity_manage',

            // Media management
            'media_view',
            'media_upload',
            'media_delete',


            //booking 
            'booking_view',
            'booking_create',
            'booking_edit',
            'booking_delete',
            // Booking action permissions
            'booking_schedule',
            'booking_update_payment_status',
            'booking_update_status',
            'booking_assign_qr',
            'booking_approval',
            'booking_manage_assignees',

            // Photographer Visit management
            'photographer_visit_view',
            'photographer_visit_create',
            'photographer_visit_edit',
            'photographer_visit_delete',


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
            // Settings tabs permissions
            'setting_booking_schedule',
            'setting_photographer',
            'setting_base_price',
            'setting_payment_gateway',
            'setting_sms_configuration',
            'setting_ftp_configuration',

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
            
            // QR Analytics management
            'qr_analytics_view',


            // Tour Manager management
            'tour_manager_view',
            'tour_manager_edit',

            // Tour Notification management
            'tour_notification_view',

        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $photographerRole = Role::firstOrCreate(['name' => 'photographer', 'guard_name' => 'web']);
        $tour_manager = Role::firstOrCreate(['name' => 'tour_manager', 'guard_name' => 'web']);
        $seo_manager = Role::firstOrCreate(['name' => 'seo_manager', 'guard_name' => 'web']);
        $customer = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

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

        // Photographer role permissions
        $photographerPermissions = Permission::whereIn('name', [
            'photographer_visit_view',
            'photographer_visit_create',
            'photographer_visit_edit',
            'booking_view',
        ])->get();

        $photographerRole->syncPermissions($photographerPermissions);

        // Tour Manager role permissions
        $tourManagerPermissions = Permission::whereIn('name', [
            'tour_manager_view',
            'tour_manager_edit',
            'tour_notification_view',
            'tour_view',
            'booking_view',
        ])->get();

        $seoManagerPermissions = Permission::whereIn('name', [
            'tour_view',
            'booking_view',
            'booking_edit',
        ])->get();

        $tour_manager->syncPermissions($tourManagerPermissions);
    }
}