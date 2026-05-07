<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\Game;
use App\Models\Post;
use App\Models\Media;
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

        //  Automatically generate User Settings for everyone
        User::all()->each(function ($user) {
            UserSetting::factory()->create(['user_id' => $user->id]);
        });

        $posts = Post::inRandomOrder(1234)->take(20)->get();
        foreach ($posts as $post) {
            Media::factory()->create(['post_id' => $post->id]);
        }

        // =========================================================
        // 4. POPULATE THE PIVOT TABLES! 
        // =========================================================
        $users = User::all();
        $games = Game::all();

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
            $randomDev = $users->random();
            $game->credits()->attach($randomDev->id, ['role' => 'Lead Developer']);
        }
    }
}