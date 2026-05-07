<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Post;
use App\Models\Game;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::all();

        if ($games->isEmpty()) {
            return; // Don't run if we have no games to post in!
        }

        // Create 50 top-level posts inside random Game Hubs
        Post::factory(50)->create()->each(function ($post) use ($games) {
            
            // Assign this post to a random game hub
            $post->hub_type = Game::class;
            $post->hub_id = $games->random()->id;
            $post->save();

            // Create 1-3 replies for each of these posts
            Post::factory(rand(1, 3))->create([
                'hub_type' => $post->hub_type,
                'hub_id' => $post->hub_id,
                'parent_id' => $post->id, 
            ]);
        });
    }
}