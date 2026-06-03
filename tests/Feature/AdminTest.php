<?php

use App\Models\Game;
use App\Models\Post;
use App\Models\User;

/* -------------------------------------------------------------------------- */
/*  ADMIN ACCESS                                                              */
/* -------------------------------------------------------------------------- */

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('non-admin cannot access admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('guest cannot access admin dashboard', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

/* -------------------------------------------------------------------------- */
/*  USER VERIFICATION                                                         */
/* -------------------------------------------------------------------------- */

test('admin can verify a user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['verified' => false]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/verify");

    $response->assertRedirect();
    expect($user->fresh()->verified)->toBeTrue();
});

test('admin can unverify a user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['verified' => true]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/verify");

    $response->assertRedirect();
    expect($user->fresh()->verified)->toBeFalse();
});

/* -------------------------------------------------------------------------- */
/*  ADMIN RANK / DERANK                                                       */
/* -------------------------------------------------------------------------- */

test('admin can rank a user to admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/admin");

    $response->assertRedirect();
    expect($user->fresh()->is_admin)->toBeTrue();
});

test('admin can derank a user from admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetAdmin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post("/admin/users/{$targetAdmin->id}/admin");

    $response->assertRedirect();
    expect($targetAdmin->fresh()->is_admin)->toBeFalse();
});

test('admin cannot remove their own admin status', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/admin");

    $response->assertRedirect();
    expect($admin->fresh()->is_admin)->toBeTrue();
});

test('non-admin cannot rank users to admin', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $target = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->post("/admin/users/{$target->id}/admin");

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  ACCOUNT SUSPENSION                                                        */
/* -------------------------------------------------------------------------- */

test('admin can suspend a user account', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_suspended' => false]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/suspend");

    $response->assertRedirect();
    expect($user->fresh()->is_suspended)->toBe(1);
});

test('admin can unsuspend a user account', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_suspended' => true]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/suspend");

    $response->assertRedirect();
    expect($user->fresh()->is_suspended)->toBe(0);
});

test('admin cannot suspend themselves', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/suspend");

    $response->assertRedirect();
    expect($admin->fresh()->is_suspended)->toBe(0);
});

test('non-admin cannot suspend users', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $target = User::factory()->create(['is_suspended' => false]);

    $response = $this->actingAs($user)->post("/admin/users/{$target->id}/suspend");

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST PINNING                                                              */
/* -------------------------------------------------------------------------- */

test('admin can pin a post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['is_pinned' => false]);

    $response = $this->actingAs($admin)->post("/admin/posts/{$post->id}/pin");

    $response->assertRedirect();
    expect($post->fresh()->is_pinned)->toBeTrue();
});

test('admin can unpin a post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['is_pinned' => true]);

    $response = $this->actingAs($admin)->post("/admin/posts/{$post->id}/pin");

    $response->assertRedirect();
    expect($post->fresh()->is_pinned)->toBeFalse();
});

test('non-admin cannot pin a post', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $post = Post::factory()->create(['is_pinned' => false]);

    $response = $this->actingAs($user)->post("/admin/posts/{$post->id}/pin");

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST LOCKDOWN                                                             */
/* -------------------------------------------------------------------------- */

test('admin can lockdown a post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['admin_locked' => false]);

    $response = $this->actingAs($admin)->post("/admin/posts/{$post->id}/lock");

    $response->assertRedirect();
    expect($post->fresh()->admin_locked)->toBeTrue();
});

test('admin can unlock a post', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create(['admin_locked' => true]);

    $response = $this->actingAs($admin)->post("/admin/posts/{$post->id}/lock");

    $response->assertRedirect();
    expect($post->fresh()->admin_locked)->toBeFalse();
});

test('non-admin cannot lockdown a post', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $post = Post::factory()->create(['admin_locked' => false]);

    $response = $this->actingAs($user)->post("/admin/posts/{$post->id}/lock");

    $response->assertForbidden();
});

test('locked post prevents replies from any user including author', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $author->id,
        'admin_locked' => false,
    ]);

    // Admin locks the post
    $this->actingAs($admin)->post("/admin/posts/{$post->id}/lock");

    // Even the author cannot reply to their own locked post
    $response = $this->actingAs($author)->postJson('/posts', [
        'body' => 'Reply attempt',
        'parent_id' => $post->id,
    ]);

    $response->assertForbidden();
});

/* -------------------------------------------------------------------------- */
/*  POST REMOVAL (via admin delete)                                           */
/* -------------------------------------------------------------------------- */

test('admin can remove any post via destroy route', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($admin)->deleteJson("/posts/{$post->id}");

    $response->assertOk();
    expect($post->fresh()->deleted_at)->not()->toBeNull();
});

test('suspended user cannot create posts after suspension', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_suspended' => false]);
    $game = Game::factory()->create();

    // Admin suspends the user
    $this->actingAs($admin)->post("/admin/users/{$user->id}/suspend");

    $user->refresh();

    // Suspended user cannot create posts - the StorePostRequest authorize checks is_suspended
    $response = $this->actingAs($user)->postJson('/posts', [
        'body' => 'Post after suspension',
        'hub_type' => 'game',
        'hub_id' => $game->id,
        'parent_id' => null,
    ]);

    // The authorize method returns false for suspended users, which triggers 403
    $response->assertForbidden();
});
