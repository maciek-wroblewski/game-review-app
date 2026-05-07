<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            'Action RPG', 'Metroidvania', 'First-Person Shooter', 
            'Cozy Farming', 'Survival Horror', 'Platformer', 
            'Turn-Based Strategy', 'MMORPG', 'Fighting', 'Roguelike'
        ];

        foreach ($genres as $genre) {
            Genre::create(['name' => $genre]); // Slug auto-generates via Model boot!
        }
    }
}