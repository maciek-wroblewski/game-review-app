<?php

use App\Models\Game;
use App\Models\Media;
use App\Models\Post;
use App\Models\Playlist;
use App\Models\User;

/* -------------------------------------------------------------------------- */
/*  POST CREATION                                                             */
/* -------------------------------------------------------------------------- */

test('authenticated user can create a post on their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Hello from my profile!',
        'hub_type' => 'user',
        'hub_id' => $user->id,
        'parent_id' => null,
    ]);

    $response->assertOk();
    expect(Post::where('body', 'Hello from my profile!')->first())
        ->user_id->toBe($user->id)
        ->hub_type->toBe('user')
        ->hub_id->toBe($user->id);
});

test('authenticated user can create a post on a playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Playlist discussion post',
        'hub_type' => 'playlist',
        'hub_id' => $playlist->id,
        'parent_id' => null,
    ]);

    $response->assertOk();
    expect(Post::where('body', 'Playlist discussion post')->first())
        ->user_id->toBe($user->id)
        ->hub_type->toBe('playlist')
        ->hub_id->toBe($playlist->id);
});

test('authenticated user can create a post on a game (discussion)', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Great game discussion!',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
    ]);

    $response->assertOk();
    expect(Post::where('body', 'Great game discussion!')->first())
        ->user_id->toBe($user->id)
        ->hub_type->toBe('game')
        ->hub_id->toBe($game->id);
});

test('authenticated user can create a post as a review on a game', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'This game is amazing!',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
        'review_type' => 'recommendation',
        'rating' => 9,
    ]);

    $response->assertOk();

    $post = Post::where('body', 'This game is amazing!')->first();
    expect($post)
        ->user_id->toBe($user->id)
        ->hub_type->toBe('game')
        ->review->not()->toBeNull()
        ->review->rating->toBe(9);

    expect($game)->average_rating->not()->toBe(0);
});

test('post creation requires body', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => '',
        'hub_type' => 'game',
        'hub_id' => Game::factory()->create()->id,
        'parent_id' => null,
    ]);

    $response->assertJsonValidationErrors(['body']);
});

test('post creation requires authentication', function () {
    $response = $this->postJson('/posts', [
        'body' => 'Unauthenticated post',
        'hub_type' => 'game',
        'hub_id' => Game::factory()->create()->id,
        'parent_id' => null,
    ]);

    $response->assertUnauthorized();
});

test('suspended user cannot create posts', function () {
    $user = User::factory()->create(['is_suspended' => true]);
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'I am suspended!',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
    ]);

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST WITH MEDIA                                                           */
/* -------------------------------------------------------------------------- */

test('authenticated user can create a post with media attachments', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $media1 = Media::factory()->create();
    $media2 = Media::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Post with media',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
        'media_ids' => [$media1->id, $media2->id],
    ]);

    $response->assertOk();

    $post = Post::where('body', 'Post with media')->first();
    expect($post)->not()->toBeNull();
    expect($post->media)->toHaveCount(2);
});

/* -------------------------------------------------------------------------- */
/*  POST SPOILER / LOCK TOGGLES                                               */
/* -------------------------------------------------------------------------- */

test('authenticated user can create a post with spoiler toggle', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Spoiler content!',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
        'is_spoiler' => true,
    ]);

    $response->assertOk();

    expect(Post::where('body', 'Spoiler content!')->first())
        ->is_spoiler->toBeTrue();
});

test('authenticated user can create a post with lock toggle', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Locked post',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
        'is_locked' => true,
    ]);

    $response->assertOk();

    expect(Post::where('body', 'Locked post')->first())
        ->is_locked->toBeTrue();
});

/* -------------------------------------------------------------------------- */
/*  POST UPDATES                                                              */
/* -------------------------------------------------------------------------- */

test('post author can update their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/posts/{$post->id}", [
        'body' => 'Updated content',
        'media_ids' => [],
    ]);

    $response->assertOk();
    expect($post->fresh()->body)->toBe('Updated content');
});

test('post author can toggle spoiler on their post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id, 'is_spoiler' => false]);

    $response = $this->actingAs($user)->putJson("/posts/{$post->id}", [
        'body' => $post->body,
        'media_ids' => [],
        'is_spoiler' => true,
    ]);

    $response->assertOk();
    expect($post->fresh()->is_spoiler)->toBeTrue();
});

test('post author can toggle lock on their post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id, 'is_locked' => false]);

    $response = $this->actingAs($user)->putJson("/posts/{$post->id}", [
        'body' => $post->body,
        'media_ids' => [],
        'is_locked' => true,
    ]);

    $response->assertOk();
    expect($post->fresh()->is_locked)->toBeTrue();
});

test('non-author cannot update another users post', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($other)->putJson("/posts/{$post->id}", [
        'body' => 'Hacked content',
        'media_ids' => [],
    ]);

    $response->assertForbidden();
});

test('admin can update post even if admin locked', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['user_id' => $owner->id, 'admin_locked' => true]);

    $response = $this->actingAs($admin)->putJson("/posts/{$post->id}", [
        'body' => 'Admin edited',
        'media_ids' => [],
    ]);

    $response->assertOk();
    expect($post->fresh()->body)->toBe('Admin edited');
});

test('non-admin cannot update admin-locked post even if author', function () {
    $owner = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id, 'admin_locked' => true]);

    $response = $this->actingAs($owner)->putJson("/posts/{$post->id}", [
        'body' => 'Trying to edit locked post',
        'media_ids' => [],
    ]);

    $response->assertForbidden();
});

test('suspended user cannot update their own post', function () {
    $user = User::factory()->create(['is_suspended' => true]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/posts/{$post->id}", [
        'body' => 'Updated',
        'media_ids' => [],
    ]);

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST DELETION                                                             */
/* -------------------------------------------------------------------------- */

test('post author can delete their own post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/posts/{$post->id}");

    $response->assertOk();
    expect($post->fresh()->deleted_at)->not()->toBeNull();
});

test('admin can delete any post', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($admin)->deleteJson("/posts/{$post->id}");

    $response->assertOk();
    expect($post->fresh()->deleted_at)->not()->toBeNull();
});

test('non-author non-admin cannot delete another users post', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($other)->deleteJson("/posts/{$post->id}");

    $response->assertForbidden();
});

test('suspended user cannot delete their own post', function () {
    $user = User::factory()->create(['is_suspended' => true]);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/posts/{$post->id}");

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST REPLIES                                                              */
/* -------------------------------------------------------------------------- */

test('authenticated user can reply to a post', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $parentPost = Post::factory()->create([
        'hub_type' => 'game',
        'hub_id' => $game->id,
    ]);

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Reply to the post!',
        'parent_id' => $parentPost->id,
        'hub_type' => 'game',
        'hub_id' => $game->id,
    ]);

    $response->assertOk();

    expect(Post::where('body', 'Reply to the post!')->first())
        ->user_id->toBe($user->id)
        ->parent_id->toBe($parentPost->id);
});

test('authenticated user cannot reply to a locked post', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $lockedPost = Post::factory()->create([
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'is_locked' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Trying to reply to locked post',
        'parent_id' => $lockedPost->id,
        'hub_type' => 'game',
        'hub_id' => $game->id,
    ]);

    $response->assertForbidden();
});

test('authenticated user cannot reply to an admin-locked post', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $adminLockedPost = Post::factory()->create([
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'admin_locked' => true,
    ]);

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Trying to reply to admin-locked post',
        'parent_id' => $adminLockedPost->id,
        'hub_type' => 'game',
        'hub_id' => $game->id,
    ]);

    $response->assertForbidden();
});

test('replies are created on the correct hub context', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $parentPost = Post::factory()->create([
        'hub_type' => 'playlist',
        'hub_id' => $playlist->id,
    ]);

    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Reply on playlist post',
        'parent_id' => $parentPost->id,
        'hub_type' => 'playlist',
        'hub_id' => $playlist->id,
    ]);

    $response->assertOk();

    expect(Post::where('body', 'Reply on playlist post')->first())
        ->parent_id->toBe($parentPost->id);
});

/* -------------------------------------------------------------------------- */
/*  REVIEWS VIA DEDICATED ROUTES                                              */
/* -------------------------------------------------------------------------- */

test('authenticated user can create a review via dedicated route', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->post('/games/' . $game->id . '/reviews', [
        'body' => 'Excellent game!',
        'rating' => 8,
    ]);

    $response->assertRedirect("/games/{$game->id}");
    $response->assertSessionHas('success');

    expect(Post::where('body', 'Excellent game!')->first())
        ->user_id->toBe($user->id)
        ->review->not()->toBeNull()
        ->review->rating->toBe(8);

    expect($game)->average_rating->not()->toBe(0);
});

test('user cannot create duplicate review on same game', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    // First review via PostController (with review_type)
    $this->actingAs($user)->postJson('/posts', [
        'body' => 'First review',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
        'review_type' => 'recommendation',
        'rating' => 7,
    ]);

    // Second review attempt via dedicated route should fail
    $response = $this->actingAs($user)->post('/games/' . $game->id . '/reviews', [
        'body' => 'Second review attempt',
        'rating' => 9,
    ]);

    $response->assertSessionHasErrors('rating');

    expect(Post::where('user_id', $user->id)->whereHas('review')->count())->toBe(1);
});

test('authenticated user can update their own review via dedicated route', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    // Create review via the dedicated route
    $this->actingAs($user)->post('/games/' . $game->id . '/reviews', [
        'body' => 'Original review text',
        'rating' => 5,
    ]);

    $post = Post::where('user_id', $user->id)->where('hub_type', 'game')->where('hub_id', $game->id)->first();
    $review = \App\Models\Review::where('post_id', $post->id)->first();

    // Update via dedicated route
    $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
        'body' => 'Updated review text',
        'rating' => 9,
    ]);

    $response->assertRedirect("/games/{$game->id}");

    expect($review->fresh()->rating)->toBe(9);
});

test('authenticated user can delete their own review via dedicated route', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    // Create review via the dedicated route
    $this->actingAs($user)->post('/games/' . $game->id . '/reviews', [
        'body' => 'Review to delete',
        'rating' => 5,
    ]);

    $post = Post::where('user_id', $user->id)->where('hub_type', 'game')->where('hub_id', $game->id)->first();
    $review = \App\Models\Review::where('post_id', $post->id)->first();

    $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

    $response->assertRedirect("/games/{$game->id}");
});

/* -------------------------------------------------------------------------- */
/*  POST FEED / SHOW                                                          */
/* -------------------------------------------------------------------------- */

test('anyone can view the posts feed', function () {
    $response = $this->get('/posts');

    $response->assertOk();
});

test('anyone can view a single post', function () {
    $post = Post::factory()->create();

    $response = $this->get("/posts/{$post->id}");

    $response->assertOk();
});

test('authenticated user can fetch replies for a post', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $post = Post::factory()->create([
        'hub_type' => 'game',
        'hub_id' => $game->id,
    ]);

    Post::factory()->count(3)->create([
        'parent_id' => $post->id,
    ]);

    $response = $this->actingAs($user)->getJson("/posts/{$post->id}/replies");

    $response->assertOk();
});

/* -------------------------------------------------------------------------- */
/*  NOTIFICATIONS & DELETED POST ACTIONS                                       */
/* -------------------------------------------------------------------------- */

test('viewing a deleted post returns the deleted view instead of a 404', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    
    // Delete the post
    $post->delete();

    $response = $this->get("/posts/{$post->id}");

    $response->assertOk();
    $response->assertViewIs('posts.deleted');
});

test('deleting a post deletes any notifications created in its creation', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $user->followers()->attach($follower->id);

    // Create a post (sends notification to followers)
    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Hello followers!',
        'hub_type' => 'user',
        'hub_id' => $user->id,
        'parent_id' => null,
    ]);

    $response->assertOk();
    
    $post = Post::where('body', 'Hello followers!')->first();
    expect(\App\Models\Notification::where('post_id', $post->id)->count())->toBeGreaterThan(0);

    // Delete the post
    $post->delete();

    // Verify notifications are deleted
    expect(\App\Models\Notification::where('post_id', $post->id)->count())->toBe(0);
});

test('unfollowing a user deletes the follow notification', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    // Follow
    $response = $this->actingAs($user)->post("/users/{$target->username}/follow");
    $response->assertRedirect();
    
    expect(\App\Models\Notification::where('user_id', $target->id)->where('type', 'follow')->count())->toBe(1);

    // Unfollow
    $response = $this->actingAs($user)->post("/users/{$target->username}/follow");
    $response->assertRedirect();

    expect(\App\Models\Notification::where('user_id', $target->id)->where('type', 'follow')->count())->toBe(0);
});

test('unliking a post deletes the like notification', function () {
    $user = User::factory()->create();
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    // Like
    $response = $this->actingAs($user)->post("/posts/{$post->id}/like");
    $response->assertRedirect();

    expect(\App\Models\Notification::where('user_id', $author->id)->where('type', 'like')->count())->toBe(1);

    // Unlike
    $response = $this->actingAs($user)->post("/posts/{$post->id}/like");
    $response->assertRedirect();

    expect(\App\Models\Notification::where('user_id', $author->id)->where('type', 'like')->count())->toBe(0);
});
