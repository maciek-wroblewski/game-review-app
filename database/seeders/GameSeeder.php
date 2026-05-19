<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $this->createGameFromSteamGridDB(
            'Elden Ring',
            'An action role-playing game developed by FromSoftware. It features a vast open world, deep lore, and challenging combat. The Lands Between await.',
            '2022-02-25'
        );

        $this->createGameFromSteamGridDB(
            'Hollow Knight',
            'A beautifully hand-drawn Metroidvania with tough-as-nails combat and a sprawling underground insect kingdom to explore.  Journey through the ruins of a fallen kingdom, encounter colorful characters, and uncover the secrets of a world shrouded in mystery.',
            '2017-02-24'
        );

        $this->createGameFromSteamGridDB(
            'Stardew Valley',
            'A cozy farming simulation game where you inherit your grandfather\'s farm and build a life in Pelican Town. Escape the hustle of modern life and embrace a simpler existence as you restore a rundown farm to its former glory.',
            '2016-02-26'
        );

        $this->createGameFromSteamGridDB(
            'The Witcher 3: Wild Hunt',
            'An epic fantasy RPG where you play as Geralt of Rivia, a monster slayer for hire. Traverse war-torn lands, make difficult moral choices, and hunt the mythical Wild Hunt in a world teeming with monsters, magic, and intrigue.',
            '2015-05-19'
        );

        $this->createGameFromSteamGridDB(
            'Cyberpunk 2077',
            'A futuristic open-world role-playing game. In the neon-drenched metropolis of Night City, players take on the role of V, a mercenary outlaw seeking a unique implant that holds the key to immortality. Explore a sprawling, dystopian urban landscape filled with advanced cybernetics, corporate conspiracies, and diverse factions vying for control.',
            '2020-12-10'
        );

        $this->createGameFromSteamGridDB(
            'Grand Theft Auto V',
            'An action-adventure game developed by Rockstar North. A massive open-world crime saga. Play as one of three protagonists, Michael, Franklin, and Trevor, as they commit heists and other crimes in the state of San Andreas.',
            '2013-09-17'
        );

        $this->createGameFromSteamGridDB(
            'Red Dead Redemption 2',
            'An action-adventure game developed by Rockstar Studios. The year is 1899. See how the West was won, or rather, how it ended. Play as Arthur Morgan, an outlaw and member of the Van der Linde gang, as they navigate the dying days of the American frontier.', 
            '2018-10-26'
        );

        $this->createGameFromSteamGridDB(
            'League of Legends',
            'A free-to-play MOBA game developed by Riot Games. Players compete in teams of five, choosing from a roster of champions to destroy the enemy team\'s Nexus.',
            '2009-10-27'
        );

        $this->createGameFromSteamGridDB(
            'Minecraft',
            'Minecraft is a sandbox game developed by Mojang Studios where players can explore and build virtual worlds. Players can mine resources, craft tools and items, and build structures of various kinds.',
            '2011-11-18'
        );

        $this->createGameFromSteamGridDB(
            'Geometry Dash',
            'Rhythm-based platformer video game developed by Robert Topala. It features simple, one-touch gameplay where players control a character that moves automatically and must jump to avoid obstacles and reach the end of the level.',
            '2013-08-13'
        );

        $this->createGameFromSteamGridDB(
            'Team Fortress 2',
            'Team Fortress 2 is a multiplayer class-based first-person shooter video game developed and published by Valve. It was released in October 2007 as part of The Orange Box compilation and later as a standalone free-to-play game in June 2011.',
            '2007-10-10'
        );

        $this->createGameFromSteamGridDB(
            'Rocket League',
            'Vehicular soccer video game. Combine soccer with driving. Fast-paced matches, aerial acrobatics, and strategic teamwork.',
            '2015-07-07'
        );

        $this->createGameFromSteamGridDB(
            'osu!',
            'Free-to-play rhythm game where players tap circles, slide rings, and spin spinners in time with the music. Extremely challenging and addictive.',
            '2007-09-15'
        );

        $this->createGameFromSteamGridDB(
            'Fortnite',
            'Hugely popular battle royale game. Drop in, gear up, and battle your way to victory. Can you be the last one standing?',
            '2017-09-26'
        );

        $this->createGameFromSteamGridDB(
            'Metal Gear Solid 2: Sons of Liberty',
            'Sequel to the tactical espionage action game Metal Gear Solid. You play as Raiden, a member of FOXHOUND, as he infiltrates the Big Shell to stop a terrorist threat. Features revolutionary graphics, gameplay, and storytelling for its time.',
            '2001-09-03'
        );

        $this->createGameFromSteamGridDB(
            'CONTROL',
            'Remedy Entertainment presents CONTROL. Players take on the role of Jesse Faden, who becomes the new Director of the Federal Bureau of Control, a secret U.S. government agency tasked with investigating and containing paranormal phenomena.',
            '2019-08-27'
        );

        $this->createGameFromSteamGridDB(
            'The Legend Of Zelda: Ocarina Of Time',
            'You are Link, a young boy who must travel through time to stop Ganondorf from taking over the world. Features a vast open world, challenging dungeons, and a memorable story. Regarded by many as one of the greatest games ever made.',
            '1998-11-21'
        );

        $this->createGameFromSteamGridDB(
            'Super Mario World',
            'Play as Mario or Luigi as you journey through the Mushroom Kingdom to save Princess Peach.',
            '1990-11-21'
        );

        $this->createGameFromSteamGridDB(
            'Silent Hill 2',
            'A psychological horror game where you play as James Sunderland as he searches for his deceased wife in the town of Silent Hill. Features a disturbing atmosphere, memorable monsters, and a thought-provoking story. Do you have what it takes to survive this psychological nightmare?',
            '2001-09-24'
        );

        $this->createGameFromSteamGridDB(
            'A Short Hike',
            'Charming indie game where you play as a bird exploring a mysterious island. The main objective is to climb the mountain peak, but the real fun is in the journey.',
            '2019-07-30'
        );

        // PLACEHOLDER GENERATOR
        Game::factory(30)->create();
    }

    private function createGameFromSteamGridDB(string $title, string $details, string $releaseDate): void
    {
        $apiKey = env('STEAMGRIDDB_API_KEY');
        $attributes = [
            'title' => $title,
            'details' => $details,
            'release_date' => $releaseDate,
        ];

        if ($apiKey) {
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
                if ($responses[0]->successful() && !empty($responses[0]->json('data'))) {
                    $attributes['cover_img'] = $responses[0]->json('data')[0]['url'];
                }
                
                // BANNER / HERO
                if ($responses[1]->successful() && !empty($responses[1]->json('data'))) {
                    $attributes['banner_img'] = $responses[1]->json('data')[0]['url'];
                }
                
                // LOGO
                if ($responses[2]->successful() && !empty($responses[2]->json('data'))) {
                    $attributes['logo'] = $responses[2]->json('data')[0]['url'];
                }
            }
        }

        Game::factory()->create($attributes);
    }
}