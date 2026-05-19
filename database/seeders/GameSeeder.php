<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $rawgApiKey = env('RAWG_API_KEY');

        if ($rawgApiKey) {
            $gamesResponse = Http::get('https://api.rawg.io/api/games', [
                'key' => $rawgApiKey,
                'page_size' => 50,
                'ordering' => '-added',
            ]);
        }

        if ($gamesResponse->successful() && ! empty($gamesResponse->json('results'))) {
            $games = $gamesResponse->json('results');
            foreach ($games as $gameData) {
                $title = $gameData['name'];
                $releaseDate = $gameData['released'] ?? fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d');
                $detailsResponse = Http::get("https://api.rawg.io/api/games/{$gameData['id']}", [
                    'key' => $rawgApiKey,
                ]);

                $details = 'No description available.';
                $publisher = null;

                if ($detailsResponse->successful()) {
                    $detailedData = $detailsResponse->json();
                    $details = $detailedData['description_raw'] ?: ($detailedData['description'] ?: 'No description available.');
                    if (! empty($detailedData['publishers'])) {
                        $publisher = $detailedData['publishers'][0]['name'];
                    }
                }

                $this->createGameFromSteamGridDB($title, $details, $releaseDate, $publisher);
            }
        } else {
            Game::factory(50)->create();
        }
    }

    private function createGameFromSteamGridDB(string $title, string $details, string $releaseDate, ?string $publisher = null): void
    {
        $apiKey = env('STEAMGRIDDB_API_KEY');
        $attributes = [
            'title' => $title,
            'details' => $details,
            'publisher' => $publisher,
            'release_date' => $releaseDate,
        ];

        if ($apiKey) {
            $searchResponse = Http::withToken($apiKey)
                ->get('https://www.steamgriddb.com/api/v2/search/autocomplete/'.urlencode($title));

            if ($searchResponse->successful() && ! empty($searchResponse->json('data'))) {
                $gameId = $searchResponse->json('data')[0]['id'];

                $responses = Http::pool(fn ($pool) => [
                    $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/grids/game/{$gameId}"),
                    $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/heroes/game/{$gameId}"),
                    $pool->withToken($apiKey)->get("https://www.steamgriddb.com/api/v2/logos/game/{$gameId}"),
                ]);

                // COVER / GRID
                if ($responses[0]->successful() && ! empty($responses[0]->json('data'))) {
                    $attributes['cover_img'] = $responses[0]->json('data')[0]['url'];
                }

                // BANNER / HERO
                if ($responses[1]->successful() && ! empty($responses[1]->json('data'))) {
                    $attributes['banner_img'] = $responses[1]->json('data')[0]['url'];
                }

                // LOGO
                if ($responses[2]->successful() && ! empty($responses[2]->json('data'))) {
                    $attributes['logo'] = $responses[2]->json('data')[0]['url'];
                }
            }
        }

        Game::factory()->create($attributes);
    }
}
