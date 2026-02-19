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
                ['name' => 'Farm House', 'icon' => 'fa fa-house-flag'],
                ['name' => 'Penthouse', 'icon' => 'fa fa-building-user'],
                ['name' => 'Independent House', 'icon' => 'fa fa-house'],
                ['name' => 'Independent Floor', 'icon' => 'fa fa-house-chimney'],
                ['name' => 'Studio', 'icon' => 'fa fa-door-open'],
                ['name' => 'Duplex', 'icon' => 'fa fa-house-chimney-window'],
                ['name' => 'Villa', 'icon' => 'fa fa-home-lg-alt'],
            ],
            'Commercial' => [
                ['name' => 'Office', 'icon' => 'fa fa-briefcase'],
                ['name' => 'Retail Shop', 'icon' => 'fa fa-shop'],
                ['name' => 'Showroom', 'icon' => 'fa fa-store'],
                ['name' => 'Plot', 'icon' => 'fa fa-map'],
                ['name' => 'Warehouse', 'icon' => 'fa fa-warehouse'],
                ['name'=>'Restaurant','icon'=>'fa fa-cutlery'],
                ['name'=>'Cafe','icon'=>'fa fa-coffee'],
                ['name' => 'Others', 'icon' => 'fa fa-puzzle-piece'],
            ],
            'Other'=>[
                ['name'=>'Heritage','icon'=>'ri-government-line'],
                ['name'=>'Religious','icon'=>'fa fa-star-of-david'],
                ['name'=>'Industries','icon'=>'fa fa-industry'],
                ['name'=>'Hospitality','icon'=>'fa fa-hotel'],
                ['name'=>'Spaces','icon'=>'fa fa-cube'],
                ['name'=>'Others','icon'=>'fa fa-puzzle-piece'],
            ]
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
