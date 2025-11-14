<?php

namespace Database\Seeders;

use App\Models\PropertySubType;
use App\Models\PropertyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySubTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Residential' => [
                ['name' => 'Farm House', 'icon' => 'fa-house-flag'],
                ['name' => 'Penthouse', 'icon' => 'fa-building-user'],
                ['name' => 'Independent House', 'icon' => 'fa-house'],
                ['name' => 'Independent Floor', 'icon' => 'fa-house-chimney'],
                ['name' => 'Studio', 'icon' => 'fa-door-open'],
                ['name' => 'Duplex', 'icon' => 'fa-house-chimney-window'],
                ['name' => 'Villa', 'icon' => 'fa-home-lg-alt'],
            ],
            'Commercial' => [
                ['name' => 'Office', 'icon' => 'fa-briefcase'],
                ['name' => 'Retail Shop', 'icon' => 'fa-shop'],
                ['name' => 'Showroom', 'icon' => 'fa-store'],
                ['name' => 'Plot', 'icon' => 'fa-map'],
                ['name' => 'Warehouse', 'icon' => 'fa-warehouse'],
                ['name' => 'Others', 'icon' => 'fa-ellipsis'],
            ],
        ];
        
        foreach ($data as $typeName => $subTypes) {
            $type = PropertyType::where('name', $typeName)->first();

            if (!$type) {
                // Log warning or throw exception if PropertyType doesn't exist
                \Log::warning("PropertyType '{$typeName}' not found. Run PropertyTypeSeeder first.");
                continue;
            }

            foreach ($subTypes as $subType) {
                PropertySubType::firstOrCreate(
                    [
                        'property_type_id' => $type->id,
                        'name' => $subType['name'],
                    ],
                    [
                        'icon' => $subType['icon'],
                    ]
                );
            }
        }
    }
}
