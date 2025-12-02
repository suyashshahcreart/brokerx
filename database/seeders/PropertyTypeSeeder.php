<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Residential', 'icon' => 'fa-home'],
            ['name' => 'Commercial', 'icon' => 'fa-building'],
            ['name' => 'Other', 'icon' => 'fa-coffee'],
        ];
        foreach ($types as $type) {
            PropertyType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
