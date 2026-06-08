<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $rawgApiKey = env('RAWG_API_KEY');
        $genresSeeded = false;

        if ($rawgApiKey) {
            $genresResponse = Http::get('https://api.rawg.io/api/genres', [
                'key' => $rawgApiKey,
                'page_size' => 50,
            ]);

            if ($genresResponse->successful() && !empty($genresResponse->json('results'))) {
                $genres = $genresResponse->json('results');
                foreach ($genres as $genreData) {
                    Genre::firstOrCreate(
                        ['name' => $genreData['name']],
                        ['slug' => $genreData['slug'] ?? \Illuminate\Support\Str::slug($genreData['name'])]
                    );
                }
                $genresSeeded = true;
            }
        }

        if (!$genresSeeded) {
            $genres = [
                'Action RPG', 'Metroidvania', 'First-Person Shooter', 
                'Cozy Farming', 'Survival Horror', 'Platformer', 
                'Turn-Based Strategy', 'MMORPG', 'Fighting', 'Roguelike'
            ];

            foreach ($genres as $genre) {
                Genre::firstOrCreate(['name' => $genre]);
            }
        }
    }
}