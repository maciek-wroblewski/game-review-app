<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'file_path' => 'https://placehold.co/800x400/2a2a2a/ffffff?text=' . urlencode('Attached Media'),
            'mime_type' => 'image/jpeg',
        ];
    }
}