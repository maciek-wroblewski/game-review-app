<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'file_path' => 'https://picsum.photos/seed/' . fake()->uuid() . '/500/500',
            'mime_type' => 'image/jpeg',
        ];
    }
}