<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create some recognizable staple games for testing
        Game::factory()->create([
            'title' => 'Elden Ring',
            'details' => 'An action role-playing game developed by FromSoftware. It features a vast open world, deep lore, and challenging combat.',
            'release_date' => '2022-02-25',
        ]);

        Game::factory()->create([
            'title' => 'Hollow Knight',
            'details' => 'A beautifully hand-drawn Metroidvania with tough-as-nails combat and a sprawling underground insect kingdom to explore.',
            'release_date' => '2017-02-24',
        ]);

        Game::factory()->create([
            'title' => 'Stardew Valley',
            'details' => 'A cozy farming simulation game where you inherit your grandfather\'s farm and build a life in Pelican Town.',
            'release_date' => '2016-02-26',
        ]);

        // 2. Generate 30 random filler games
        Game::factory(30)->create();
    }
}