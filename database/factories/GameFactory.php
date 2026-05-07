<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucwords(fake()->words(fake()->numberBetween(1, 4), true)),
            'details' => fake()->paragraphs(3, true),
            'release_date' => fake()->dateTimeBetween('-10 years', '+1 year')->format('Y-m-d'),
            
            'cover_img' => 'https://placehold.co/600x800/2a2a2a/ffffff?text=' . urlencode('Cover'),
            'logo' => 'https://placehold.co/400x200/transparent/ffffff?text=' . urlencode('Logo'),
            'banner_img' => 'https://placehold.co/1200x400/1a1a1a/444444?text=' . urlencode('Banner'),
        ];
    }
    
}
