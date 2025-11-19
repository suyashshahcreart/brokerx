<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $settings = [
                [
                    'name' => 'site_name',
                    'value' => 'BrokerX',
                ],
                [
                    'name' => 'support_email',
                    'value' => 'support@brokerx.com',
                ],
                [
                    'name' => 'default_timezone',
                    'value' => 'Asia/Kolkata',
                ],
                [
                    'name' => 'maintenance_mode',
                    'value' => 'off',
                ],
                [
                    'name' => 'avaliable_days',
                    'value' => '30',
                ],
            ];
            foreach ($settings as $setting) {
                Setting::updateOrCreate(['name' => $setting['name']], $setting);
            }
        });
    }
}
