<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BHK>
 */
class BHKFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                '1 RK', '1 BHK', '1.5 BHK', '2 BHK', '2.5 BHK',
                '3 BHK', '3.5 BHK', '4 BHK', '4.5 BHK', '5 BHK', '5+ BHK'
            ]),
        ];
    }
}
