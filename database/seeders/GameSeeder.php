<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Game;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $rawgApiKey = env('RAWG_API_KEY');
        $gamesSeeded = false;

        if ($rawgApiKey) {
            try {
                $gamesResponse = Http::get('https://api.rawg.io/api/games', [
                    'key' => $rawgApiKey,
                    'page_size' => 50,
                    'ordering' => '-added'
                ]);

                if ($gamesResponse->successful() && !empty($gamesResponse->json('results'))) {
                    $games = $gamesResponse->json('results');
                    foreach ($games as $gameData) {
                        $title = $gameData['name'];
                        $releaseDate = $gameData['released'] ?? fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d');
                        
                        $details = 'No description available.';
                        $publisher = null;

                        try {
                            $detailsResponse = Http::get("https://api.rawg.io/api/games/{$gameData['id']}", [
                                'key' => $rawgApiKey
                            ]);

                            if ($detailsResponse->successful()) {
                                $detailedData = $detailsResponse->json();
                                $details = $detailedData['description_raw'] ?: ($detailedData['description'] ?: 'No description available.');
                                if (!empty($detailedData['publishers'])) {
                                    $publisher = $detailedData['publishers'][0]['name'];
                                }
                            }
                        } catch (\Exception $e) {
                            // Ignore detail exception and use fallbacks
                        }

                        $game = $this->createGameFromSteamGridDB($title, $details, $releaseDate, $publisher);

                        if ($game && !empty($gameData['genres'])) {
                            $genreIds = [];
                            foreach ($gameData['genres'] as $genreObj) {
                                $genre = Genre::firstOrCreate(
                                    ['name' => $genreObj['name']],
                                    ['slug' => $genreObj['slug'] ?? \Illuminate\Support\Str::slug($genreObj['name'])]
                                );
                                $genreIds[] = $genre->id;
                            }
                            $game->genres()->sync($genreIds);
                        }
                    }
                    $gamesSeeded = true;
                }
            } catch (\Exception $e) {
                // If there's any network or connection error, we log it and fallback to factory
                logger()->error("Failed to seed games from RAWG: " . $e->getMessage());
            }
        }

        if (!$gamesSeeded) {
            Game::factory(50)->create();
        }
    }

    private function createGameFromSteamGridDB(string $title, string $details, string $releaseDate, ?string $publisher = null): Game
    {
        $apiKey = env('STEAMGRIDDB_API_KEY');
        $attributes = [
            'title' => $title,
            'details' => $details,
            'publisher' => $publisher,
            'release_date' => $releaseDate,
        ];

        if ($apiKey) {
            try {
                $searchResponse = Http::withToken($apiKey)
                    ->get('https://www.steamgriddb.com/api/v2/search/autocomplete/' . urlencode($title));
                
                if ($searchResponse->successful() && !empty($searchResponse->json('data'))) {
                    $gameId = $searchResponse->json('data')[0]['id'];

                    $responses = Http::pool(fn ($pool) => [
                        $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/grids/game/{$gameId}"),
                        $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/heroes/game/{$gameId}"),
                        $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/logos/game/{$gameId}")
                    ]);

                    // COVER / GRID
                    if (isset($responses[0]) && $responses[0] instanceof \Illuminate\Http\Client\Response && $responses[0]->successful() && !empty($responses[0]->json('data'))) {
                        $attributes['cover_img'] = $responses[0]->json('data')[0]['url'];
                    }
                    
                    // BANNER / HERO
                    if (isset($responses[1]) && $responses[1] instanceof \Illuminate\Http\Client\Response && $responses[1]->successful() && !empty($responses[1]->json('data'))) {
                        $attributes['banner_img'] = $responses[1]->json('data')[0]['url'];
                    }
                    
                    // LOGO
                    if (isset($responses[2]) && $responses[2] instanceof \Illuminate\Http\Client\Response && $responses[2]->successful() && !empty($responses[2]->json('data'))) {
                        $attributes['logo'] = $responses[2]->json('data')[0]['url'];
                    }
                }
            } catch (\Exception $e) {
                // Ignore SteamGridDB connection errors and just create the game with basic attributes
                logger()->error("SteamGridDB API error for game '{$title}': " . $e->getMessage());
            }
        }

        return Game::factory()->create($attributes);
    }
}