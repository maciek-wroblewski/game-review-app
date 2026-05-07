<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        DB::table('users')->insert([
            ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => bcrypt('password')],
            ['name' => 'Bob', 'email' => 'bob@example.com', 'password' => bcrypt('password')],
        ]);

        // Games
        DB::table('games')->insert([
            ['title' => 'Cyberpunk 2077', 'slug' => 'cyberpunk-2077', 'summary' => 'Open world RPG', 'release_date' => '2020-12-10'],
            ['title' => 'Stardew Valley', 'slug' => 'stardew-valley', 'summary' => 'Farming sim', 'release_date' => '2016-02-26'],
            ['title' => 'Factorio', 'slug' => 'factorio', 'summary' => 'Factory builder', 'release_date' => '2020-08-14'],
        ]);

        // Genres
        DB::table('genres')->insert([
            ['name' => 'RPG', 'slug' => 'rpg'],
            ['name' => 'Simulation', 'slug' => 'simulation'],
            ['name' => 'Strategy', 'slug' => 'strategy'],
        ]);

        // Game Genre
        DB::table('game_genre')->insert([
            ['game_id' => 1, 'genre_id' => 1],
            ['game_id' => 2, 'genre_id' => 2],
            ['game_id' => 2, 'genre_id' => 1],
            ['game_id' => 3, 'genre_id' => 3],
            ['game_id' => 3, 'genre_id' => 2],
        ]);

        // Posts
        DB::table('posts')->insert([
            ['user_id' => 1, 'game_id' => 1, 'title' => 'New update is amazing!', 'body' => 'I love the new Phantom Liberty expansion.', 'upvotes' => 10, 'created_at' => now()],
            ['user_id' => 2, 'game_id' => 2, 'title' => 'My 10th farm', 'body' => 'Just started a new playthrough.', 'upvotes' => 5, 'created_at' => now()],
        ]);

        // Comments
        DB::table('comments')->insert([
            ['user_id' => 2, 'commentable_id' => 1, 'commentable_type' => 'App\\Models\\Post', 'body' => 'Totally agree with you!', 'created_at' => now()],
        ]);
        
        // Game User list
        DB::table('game_user')->insert([
            ['user_id' => 1, 'game_id' => 1, 'status' => 'played', 'personal_rating' => 9, 'recommendation_rating' => 'positive', 'review_text' => 'Great game!'],
            ['user_id' => 2, 'game_id' => 1, 'status' => 'wishlisted', 'personal_rating' => null, 'recommendation_rating' => null, 'review_text' => null],
        ]);
    }
}
