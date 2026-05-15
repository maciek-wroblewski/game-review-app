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
        $title = ucwords(fake()->words(fake()->numberBetween(1, 4), true));
        return [
            'title' => $title,
            'details' => fake()->paragraphs(3, true),
            'release_date' => fake()->dateTimeBetween('-10 years', '+1 year')->format('Y-m-d'),
            
            'cover_img' => 'https://picsum.photos/seed/' . fake()->uuid() . '/400/600',
            'logo' => 'https://placehold.co/400x200/transparent/ffffff?text=' . urlencode($title),
            'banner_img' => 'https://picsum.photos/seed/' . fake()->uuid() . '/1200/400',
        ];
    }
    
}
