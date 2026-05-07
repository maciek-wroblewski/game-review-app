<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Grab a random existing user to be the author
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'body' => fake()->paragraphs(2, true),
            'format_type' => fake()->randomElement(['post', 'article', 'review']),
            // Only give a rating if it's a review
            'rating' => function (array $attributes) {
                return $attributes['format_type'] === 'review' ? fake()->numberBetween(1, 10) : null;
            },
            'is_locked' => fake()->boolean(5), // 5% chance the thread is locked
            'likes_count' => 0,
        ];
    }
}