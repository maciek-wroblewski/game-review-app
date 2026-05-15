<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'body' => $this->faker->paragraphs(2, true),
            'hub_id' => $this->faker->numberBetween(1, 10),
            'hub_type' => 'App\Models\Game',
            'parent_id' => null, // Default is a top-level post
            'likes_count' => $this->faker->numberBetween(0, 100),
            'is_locked' => $this->faker->boolean(10),
            'is_spoiler' => $this->faker->boolean(25)
        ];
    }
}