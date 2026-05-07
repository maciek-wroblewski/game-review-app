<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\Game;
use App\Models\Post;
use App\Models\Media;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //  Main seeders
        $this->call([
            UserSeeder::class,
            GameSeeder::class,
            GenreSeeder::class,
            PlaylistSeeder::class,
            PostSeeder::class,
            ChatSeeder::class,
        ]);

        $posts = Post::inRandomOrder(1234)->take(20)->get();
        foreach ($posts as $post) {
            Media::factory()->create(['post_id' => $post->id]);
        }

        // =========================================================
        // 4. POPULATE THE PIVOT TABLES! 
        // =========================================================
        $users = User::all();
        $games = Game::all();

        $genres = Genre::all();

        // Assign 1 to 3 random genres to every game
        $games->each(function ($game) use ($genres) {
            $game->genres()->attach(
                $genres->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        foreach ($users as $user) {
            
            // POPULATE 'follows': Make users follow 3 random other users
            $randomUsersToFollow = $users->except($user->id)->random(3)->pluck('id');
            $user->following()->attach($randomUsersToFollow);
            
            // POPULATE 'blocks': Make users block 1 random user
            $randomUserToBlock = $users->except($user->id)->random(1)->pluck('id');
            $user->blockedUsers()->attach($randomUserToBlock);

            // POPULATE 'likes': Make users like 5 random posts
            $randomPostsToLike = $posts->random(5)->pluck('id');
            $user->likedPosts()->attach($randomPostsToLike);
        }

        // POPULATE 'game_credits': Assign a few random users as developers to games
        foreach ($games as $game) {
            // Regular posts
            Post::factory(10)->create([
                'hub_id' => $game->id,
                'hub_type' => get_class($game),
            ]);

            // Create 5 reviews manually in a loop to avoid Collection/Closure issues
            for ($i = 0; $i < 5; $i++) {
                // 1. Create the Post first
                $post = Post::factory()->create([
                    'hub_id' => $game->id,
                    'hub_type' => get_class($game),
                    'user_id' => $users->random()->id,
                ]);

                // 2. Create the Review linked to that post
                $review = \App\Models\Review::factory()->create([
                    'post_id' => $post->id,
                ]);

                // 3. Create the 3 replies to that specific post
                Post::factory(3)->create([
                    'parent_id' => $post->id, 
                    'hub_id' => $game->id,
                    'hub_type' => get_class($game),
                    'user_id' => $users->random()->id,
                ]);
            }
        }
    }
}