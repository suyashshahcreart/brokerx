<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Get the first user if exists, otherwise use NULL
            $user = User::first();
            $userId = $user ? $user->id : null;
            
            $settings = [
                [
                    'name' => 'base_price',
                    'value' => '599',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'base_area',
                    'value' => '1500',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'extra_area',
                    'value' => '500',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'extra_area_price',
                    'value' => '200',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'active_payment_gateway',
                    'value' => 'cashfree',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'cashfree_status',
                    'value' => '1',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'payu_status',
                    'value' => '0',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'payu_merchant_key',
                    'value' => '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'payu_merchant_salt',
                    'value' => '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'payu_mode',
                    'value' => 'test',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'razorpay_status',
                    'value' => '0',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'razorpay_key',
                    'value' => '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'razorpay_secret',
                    'value' => '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'razorpay_mode',
                    'value' => 'test',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'sms_gateway_msg91_status',
                    'value' => '1',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'msg91_auth_key',
                    'value' => '433998AopMixVzR672c8994P1',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'msg91_sender_id',
                    'value' => 'PROPPK',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'msg91_timeout',
                    'value' => '3000',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'avaliable_days',
                    'value' => '45',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'per_day_booking',
                    'value' => '20',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'customer_attempt',
                    'value' => '3',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'customer_attempt_note',
                    'value' => 'You have reached the maximum number of schedule attempts. Please contact admin for further assistance.',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'photographer_available_from',
                    'value' => '08:00',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'photographer_available_to',
                    'value' => '21:00',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'photographer_working_duration',
                    'value' => '60',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'portfolio_api_mobile',
                    'value' => '',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'portfolio_api_token_validity_minutes',
                    'value' => '30',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'portfolio_api_enabled',
                    'value' => '1',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($settings as $setting) {
                Setting::updateOrCreate(
                    ['name' => $setting['name']],
                    $setting
                );
            }
        });
    }
}
