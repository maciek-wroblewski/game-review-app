<?php
namespace Database\Factories;

use App\Models\Post;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory()->state([
                'hub_type' => 'App\Models\Game',
                'hub_id' => Game::factory(), // Creates a new game if one isn't provided
            ]),
            'type' => $this->faker->randomElement(['recommendation', 'article', 'patch_note']),
            'rating' => $this->faker->numberBetween(0, 1), // 0 for Bad, 1 for Good
        ];
    }

    /**
     * State helper for recommendations specifically
     */
    public function recommendation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'recommendation',
        ]);
    }
}