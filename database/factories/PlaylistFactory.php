<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaylistFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->catchPhrase(),
            'is_system' => false,
            'is_public' => fake()->boolean(90),
            'description' => fake()->paragraph(),
            'cover' => 'https://picsum.photos/seed/' . fake()->uuid() . '/600/600',
        ];
    }
}