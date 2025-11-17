<?php

namespace Database\Seeders;

use App\Models\BHK;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BHKSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bhkTypes = [
            '1 RK',
            '1 BHK',
            '1.5 BHK',
            '2 BHK',
            '2.5 BHK',
            '3 BHK',
            '3.5 BHK',
            '4 BHK',
            '4.5 BHK',
            '5 BHK',
            '5+ BHK',
        ];

        foreach ($bhkTypes as $type) {
            BHK::firstOrCreate(['name' => $type]);
        }
    }
}
