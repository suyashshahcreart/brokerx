<?php

namespace Database\Factories;

use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertySubType>
 */
class PropertySubTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_type_id' => PropertyType::factory(),
            'name' => fake()->unique()->randomElement(['Villa', 'Apartment', 'Office', 'Warehouse', 'Showroom', 'Plot']),
            'icon' => fake()->optional()->randomElement(['fa-villa', 'fa-building', 'fa-briefcase', 'fa-warehouse', 'fa-store', 'fa-map']),
        ];
    }
}
