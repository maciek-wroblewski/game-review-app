<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Game;
use App\Models\Post;
use App\Models\Media;
use App\Models\Genre;
use App\Models\Review;
use App\Models\Playlist;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================
        // 1. CALL MAIN SEEDERS
        // =========================================================
        $this->call([
            UserSeeder::class,
            GameSeeder::class,
            GenreSeeder::class,
            PlaylistSeeder::class,
            PostSeeder::class,
            ChatSeeder::class,
        ]);

        $users = User::all();
        $games = Game::all();
        $roles = ['Lead Developer', 'Designer', 'Composer', 'Writer', 'Artist'];
        $genres = Genre::all();

        // =========================================================
        // 2. POPULATE GAME RELATIONSHIPS
        // =========================================================
        
        // Assign 1 to 3 random genres to every game
        $games->each(function ($game) use ($genres) {
            $game->genres()->attach(
                $genres->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // Assign game credits (developers, designers, etc.)
        foreach ($games as $game) {
            $staffMembers = $users->random(rand(2, 4));
            foreach ($staffMembers as $staff) {
                $game->credits()->attach($staff->id, [
                    'role' => $roles[array_rand($roles)]
                ]);
            }
        }

        // =========================================================
        // 3. POPULATE USER RELATIONSHIPS
        // =========================================================
        foreach ($users as $user) {
            // Make users follow 3 random other users
            $randomUsersToFollow = $users->except($user->id)->random(3)->pluck('id');
            $user->following()->attach($randomUsersToFollow);
            
            // Make users block 1 random user
            $randomUserToBlock = $users->except($user->id)->random(1)->pluck('id');
            $user->blockedUsers()->attach($randomUserToBlock);
        }

        // =========================================================
        // 4. CREATE RANDOM POSTS ON RANDOM HUBS (Games)
        // =========================================================
        $allPosts = collect();
        
        foreach ($games as $game) {
            // Create 5-10 random posts per game
            $postsPerGame = rand(5, 10);
            for ($i = 0; $i < $postsPerGame; $i++) {
                $post = Post::factory()->create([
                    'hub_id' => $game->id,
                    'hub_type' => 'game',
                    'user_id' => $users->random()->id,
                ]);
                $allPosts->push($post);
            }
        }

        // =========================================================
        // 5. CREATE REVIEW POSTS ON GAME HUBS
        // =========================================================
        foreach ($games as $game) {
            // Create 2-4 review posts per game
            $reviewsPerGame = rand(2, 4);
            for ($i = 0; $i < $reviewsPerGame; $i++) {
                // Create the Post first
                $reviewPost = Post::factory()->create([
                    'hub_id' => $game->id,
                    'hub_type' => 'game',
                    'user_id' => $users->random()->id,
                ]);

                // Create the Review linked to that post
                Review::factory()->create([
                    'post_id' => $reviewPost->id,
                    'type' => 'recommendation',
                ]);

                $allPosts->push($reviewPost);
            }
        }

        // =========================================================
        // 6. CREATE COMMENTS ON RANDOM POSTS (including reviews)
        // =========================================================
        foreach ($allPosts as $post) {
            // Each post can have 0-5 comments
            $commentsPerPost = rand(0, 5);
            for ($i = 0; $i < $commentsPerPost; $i++) {
                // Create a comment (reply) on this post
                $comment = Post::factory()->create([
                    'parent_id' => $post->id,
                    'hub_id' => $post->hub_id,
                    'hub_type' => $post->hub_type,
                    'user_id' => $users->random()->id,
                ]);

                // Nested replies: 0-3 comments can have their own replies
                $nestedReplies = rand(0, 3);
                for ($j = 0; $j < $nestedReplies; $j++) {
                    Post::factory()->create([
                        'parent_id' => $comment->id,
                        'hub_id' => $post->hub_id,
                        'hub_type' => $post->hub_type,
                        'user_id' => $users->random()->id,
                    ]);
                }
            }
        }

        // =========================================================
        // 7. ADD MEDIA TO RANDOM POSTS
        // =========================================================
        $randomPostsWithMedia = $allPosts->random(min(20, count($allPosts)));
        foreach ($randomPostsWithMedia as $post) {
            Media::factory()->create(['post_id' => $post->id]);
        }

        // =========================================================
        // 8. CREATE CUSTOM PLAYLISTS FOR USERS
        // =========================================================
        foreach ($users as $user) {
            // Each user gets 2-4 custom playlists
            $playlistsPerUser = rand(2, 4);
            for ($i = 0; $i < $playlistsPerUser; $i++) {
                $playlist = Playlist::factory()->create([
                    'is_system' => false,
                    'is_public' => rand(0, 1) === 1,
                ]);

                // Attach the user to the playlist
                $playlist->users()->attach($user->id, ['role' => 'owner']);

                // Fill each playlist with 3-8 random games
                $gamesPerPlaylist = rand(3, 8);
                $randomGames = $games->random(min($gamesPerPlaylist, count($games)));
                
                // fill each playlist with 3-5 random comments
                $commentsPerPlaylist = rand(3, 5);
                for ($j = 0; $j < $commentsPerPlaylist; $j++) {
                    Post::factory()->create([
                        'hub_id' => $playlist->id,
                        'hub_type' => 'playlist',
                        'user_id' => $users->random()->id,
                    ]);
                }

                $order = 1;
                foreach ($randomGames as $game) {
                    $playlist->games()->attach($game->id, ['order' => $order++]);
                }
            }
        }

        // =========================================================
        // 9. POPULATE LIKES ON POSTS
        // =========================================================
        foreach ($users as $user) {
            // Each user likes 5-15 random posts
            $postsToLike = rand(5, 15);
            $randomPostsToLike = $allPosts->random(min($postsToLike, count($allPosts)))->pluck('id');
            $user->likedPosts()->attach($randomPostsToLike);
        }

        /// =========================================================
        /// 10. POPULATE COMMENTS ON USER PROFILES
        /// =========================================================
        foreach ($users as $user) {
            // Each user gets 0-5 comments on their profile
            $commentsOnProfile = rand(0, 5);
            for ($i = 0; $i < $commentsOnProfile; $i++) {
                Post::factory()->create([
                    'hub_id' => $user->id,
                    'hub_type' => 'user',
                    'user_id' => $users->random()->id,
                ]);
            }
        }
    }
}