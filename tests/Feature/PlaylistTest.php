<?php

use App\Models\Game;
use App\Models\Playlist;
use App\Models\User;

/* -------------------------------------------------------------------------- */
/*  PLAYLIST CREATION                                                         */
/* -------------------------------------------------------------------------- */

test('authenticated user can access playlist creation page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/playlists/create');

    $response->assertOk();
});

test('authenticated user can create a playlist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/playlists', [
        'name' => 'My Awesome Playlist',
        'description' => 'A collection of great games',
        'is_public' => true,
    ]);

    $response->assertRedirect();
    expect(Playlist::where('name', 'My Awesome Playlist')->first())
        ->not()->toBeNull()
        ->name->toBe('My Awesome Playlist')
        ->description->toBe('A collection of great games')
        ->is_public->toBeTrue();

    $playlist = Playlist::where('name', 'My Awesome Playlist')->first();
    expect($playlist->users->pluck('id'))->toContain($user->id);
});

test('playlist creation requires name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/playlists', [
        'name' => '',
        'description' => 'No name playlist',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('playlist name cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/playlists', [
        'name' => str_repeat('a', 256),
        'description' => 'Too long name',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('playlist description cannot exceed 1000 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/playlists', [
        'name' => 'Valid Name',
        'description' => str_repeat('a', 1001),
    ]);

    $response->assertSessionHasErrors(['description']);
});

test('guest cannot create a playlist', function () {
    $response = $this->post('/playlists', [
        'name' => 'Guest Playlist',
        'description' => 'Should fail',
    ]);

    $response->assertRedirect('/login');
});

/* -------------------------------------------------------------------------- */
/*  PLAYLIST EDITING                                                          */
/* -------------------------------------------------------------------------- */

test('playlist member can access edit page', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->get("/playlists/{$playlist->id}/edit");

    $response->assertOk();
});

test('non-member cannot access edit page', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();

    $response = $this->actingAs($user)->get("/playlists/{$playlist->id}/edit");

    $response->assertForbidden();
});

test('playlist member can update playlist name and description', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->put("/playlists/{$playlist->id}", [
        'name' => 'Updated Playlist Name',
        'description' => 'Updated description text',
        'is_public' => false,
    ]);

    $response->assertRedirect();

    expect($playlist->fresh())
        ->name->toBe('Updated Playlist Name')
        ->description->toBe('Updated description text')
        ->is_public->toBeFalse();
});

test('non-member cannot update playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();

    $response = $this->actingAs($user)->put("/playlists/{$playlist->id}", [
        'name' => 'Hacked Name',
        'description' => 'Hacked description',
    ]);

    $response->assertForbidden();
});

test('playlist update requires name', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->put("/playlists/{$playlist->id}", [
        'name' => '',
        'description' => 'Empty name should fail',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('member can toggle playlist public/private status', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create(['is_public' => true]);
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->put("/playlists/{$playlist->id}", [
        'name' => $playlist->name,
        'description' => $playlist->description,
        'is_public' => false,
    ]);

    $response->assertRedirect();
    expect($playlist->fresh()->is_public)->toBeFalse();
});

/* -------------------------------------------------------------------------- */
/*  PLAYLIST DELETION                                                         */
/* -------------------------------------------------------------------------- */

test('playlist member can delete a playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}");

    $response->assertRedirect();
    expect(Playlist::where('id', $playlist->id)->first())->toBeNull();
});

test('non-member cannot delete a playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();

    $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}");

    $response->assertForbidden();
});

test('guest cannot delete a playlist', function () {
    $playlist = Playlist::factory()->create();

    $response = $this->delete("/playlists/{$playlist->id}");

    $response->assertRedirect('/login');
});

/* -------------------------------------------------------------------------- */
/*  PLAYLIST VIEWING                                                          */
/* -------------------------------------------------------------------------- */

test('anyone can view a public playlist', function () {
    $playlist = Playlist::factory()->create(['is_public' => true]);

    $response = $this->get("/playlists/{$playlist->id}");

    $response->assertOk();
});

test('authenticated user can view their own playlists', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);

    $response = $this->actingAs($user)->get("/playlists/{$playlist->id}");

    $response->assertOk();
});

/* -------------------------------------------------------------------------- */
/*  PLAYLIST GAMES MANAGEMENT                                                 */
/* -------------------------------------------------------------------------- */

test('authenticated user can add a game to their playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->post("/playlists/{$playlist->id}/games/{$game->id}");

    expect($playlist->fresh()->games->pluck('id'))->toContain($game->id);
});

test('authenticated user can remove a game from their playlist', function () {
    $user = User::factory()->create();
    $playlist = Playlist::factory()->create();
    $playlist->users()->attach($user);
    $game = Game::factory()->create();

    // Add game first
    $playlist->games()->attach($game);

    $response = $this->actingAs($user)->delete("/playlists/{$playlist->id}/games/{$game->id}");

    expect($playlist->fresh()->games->pluck('id'))->not()->toContain($game->id);
});

test('playlist sidebar on the home page fetches only system playlists', function () {
    $user = User::factory()->create();

    // User automatically gets 4 system playlists via UserObserver
    
    // Create a custom (non-system) playlist for the user
    $customPlaylist = Playlist::create([
        'name' => 'Custom Playlist',
        'is_system' => false,
        'is_public' => true,
        'description' => 'My custom list',
        'cover' => null,
    ]);
    $user->playlists()->attach($customPlaylist->id, ['role' => 'owner']);

    // Call the home page
    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    
    // Assert system playlists are passed to index view
    $response->assertViewHas('playlists', function ($playlists) use ($customPlaylist) {
        $hasCustom = $playlists->contains('id', $customPlaylist->id);
        $allSystem = $playlists->every('is_system', true);
        
        return !$hasCustom && $allSystem && $playlists->count() === 4;
    });
});

