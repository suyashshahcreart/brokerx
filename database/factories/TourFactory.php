<?php

namespace Database\Factories;

use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $durationDays = $this->faker->numberBetween(3, 15);
        $endDate = (clone $startDate)->modify("+{$durationDays} days");

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'description' => $this->faker->paragraph(3),
            'content' => $this->faker->paragraphs(5, true),
            'featured_image' => $this->faker->imageUrl(1200, 600, 'travel'),
            'price' => $this->faker->randomFloat(2, 5000, 50000),
            'duration_days' => $durationDays,
            'location' => $this->faker->randomElement([
                'Goa, India',
                'Manali, Himachal Pradesh',
                'Kerala, India',
                'Rajasthan, India',
                'Ladakh, India',
                'Sikkim, India',
                'Andaman Islands',
                'Uttarakhand, India',
                'Shimla, Himachal Pradesh',
                'Darjeeling, West Bengal'
            ]),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_participants' => $this->faker->numberBetween(10, 50),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            
            // SEO Meta Fields
            'meta_title' => $title . ' - Best Tour Packages',
            'meta_description' => $this->faker->sentence(15),
            'meta_keywords' => implode(', ', $this->faker->words(8)),
            'og_image' => $this->faker->imageUrl(1200, 630, 'travel'),
            'og_title' => $title,
            'og_description' => $this->faker->sentence(12),
            'canonical_url' => null,
            'meta_robots' => 'index, follow',
            'twitter_title' => $title,
            'twitter_description' => $this->faker->sentence(10),
            'twitter_image' => $this->faker->imageUrl(1200, 628, 'travel'),
            'structured_data_type' => $this->faker->randomElement(['TouristAttraction', 'Place', 'Event']),
            'structured_data' => null,
            'header_code' => null,
            'footer_code' => null,
        ];
    }

    /**
     * Indicate that the tour is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the tour is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the tour is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the tour has complete SEO data.
     */
    public function withCompleteSeo(): static
    {
        return $this->state(function (array $attributes) {
            $title = $attributes['title'];
            $description = $attributes['description'];
            
            return [
                'meta_title' => $title . ' - Explore Amazing Destinations',
                'meta_description' => $description,
                'meta_keywords' => 'tour, travel, vacation, holiday, package, ' . $attributes['location'],
                'og_title' => $title,
                'og_description' => $description,
                'og_image' => $this->faker->imageUrl(1200, 630, 'travel'),
                'twitter_title' => $title,
                'twitter_description' => $description,
                'twitter_image' => $this->faker->imageUrl(1200, 628, 'travel'),
                'canonical_url' => 'https://example.com/tours/' . $attributes['slug'],
                'structured_data_type' => 'TouristAttraction',
                'structured_data' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'TouristAttraction',
                    'name' => $title,
                    'description' => $description,
                ],
            ];
        });
    }

    /**
     * Indicate that the tour has custom code injections.
     */
    public function withCustomCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'header_code' => '<!-- Custom Header Code -->',
            'footer_code' => '<!-- Custom Footer Code -->',
        ]);
    }
}
