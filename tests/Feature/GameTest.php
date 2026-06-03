<?php

use App\Models\Game;
use App\Models\Genre;
use App\Models\User;

/* -------------------------------------------------------------------------- */
/*  GAME CREATION                                                             */
/* -------------------------------------------------------------------------- */

test('admin can access game creation page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get('/games/create');

    $response->assertOk();
});

test('non-admin cannot access game creation page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/games/create');

    $response->assertForbidden();
});

test('admin can create a game', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/games', [
        'title' => 'Super Game 2026',
        'publisher' => 'Awesome Studios',
        'release_date' => '2026-01-15',
        'details' => 'An incredible game experience.',
    ]);

    $response->assertRedirect();

    expect(Game::where('title', 'Super Game 2026')->first())
        ->not()->toBeNull()
        ->title->toBe('Super Game 2026')
        ->publisher->toBe('Awesome Studios')
        ->release_date->toDateString()->toBe('2026-01-15');
});

test('non-admin cannot create a game', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->post('/games', [
        'title' => 'Unauthorized Game',
        'publisher' => 'Bad Publisher',
    ]);

    $response->assertForbidden();
});

test('game creation requires title', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/games', [
        'title' => '',
        'publisher' => 'Some Publisher',
    ]);

    $response->assertSessionHasErrors(['title']);
});

test('game title cannot exceed 255 characters', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/games', [
        'title' => str_repeat('a', 256),
        'publisher' => 'Some Publisher',
    ]);

    $response->assertSessionHasErrors(['title']);
});

test('guest cannot create a game', function () {
    $response = $this->post('/games', [
        'title' => 'Guest Game',
        'publisher' => 'Guest Publisher',
    ]);

    $response->assertRedirect('/login');
});

test('admin can create a game with genres', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $genre1 = Genre::factory()->create();
    $genre2 = Genre::factory()->create();

    $response = $this->actingAs($admin)->post('/games', [
        'title' => 'Genre Rich Game',
        'publisher' => 'Genre Studios',
        'genres' => [$genre1->id, $genre2->id],
    ]);

    $response->assertRedirect();

    $game = Game::where('title', 'Genre Rich Game')->first();
    expect($game)->not()->toBeNull();
    expect($game->genres)->toHaveCount(2);
});

/* -------------------------------------------------------------------------- */
/*  GAME EDITING                                                              */
/* -------------------------------------------------------------------------- */

test('admin can access game edit page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $game = Game::factory()->create();

    $response = $this->actingAs($admin)->get("/games/{$game->id}/edit");

    $response->assertOk();
});

test('non-admin credited on game can access edit page', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $game = Game::factory()->create();
    $game->credits()->attach($user, ['role' => 'Developer']);

    $response = $this->actingAs($user)->get("/games/{$game->id}/edit");

    $response->assertOk();
});

test('non-admin without credit cannot access edit page', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->get("/games/{$game->id}/edit");

    $response->assertForbidden();
});

test('admin can update a game', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $game = Game::factory()->create();

    $response = $this->actingAs($admin)->patch("/games/{$game->id}", [
        'title' => 'Updated Game Title',
        'publisher' => 'New Publisher',
        'release_date' => '2025-06-01',
        'details' => 'Updated details.',
    ]);

    $response->assertRedirect("/games/{$game->id}");

    expect($game->fresh())
        ->title->toBe('Updated Game Title')
        ->publisher->toBe('New Publisher');
});

test('credited user can update a game', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $game = Game::factory()->create();
    $game->credits()->attach($user, ['role' => 'Developer']);

    $response = $this->actingAs($user)->patch("/games/{$game->id}", [
        'title' => 'Updated by Credited User',
        'publisher' => $game->publisher,
    ]);

    $response->assertRedirect("/games/{$game->id}");

    expect($game->fresh()->title)->toBe('Updated by Credited User');
});

test('non-admin without credit cannot update a game', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->patch("/games/{$game->id}", [
        'title' => 'Hacked Title',
        'publisher' => 'Hacked Publisher',
    ]);

    $response->assertForbidden();
});

test('guest cannot update a game', function () {
    $game = Game::factory()->create();

    $response = $this->patch("/games/{$game->id}", [
        'title' => 'Guest Edit',
        'publisher' => 'Guest Publisher',
    ]);

    $response->assertRedirect('/login');
});

test('admin can update game genres', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $game = Game::factory()->create();
    $genre1 = Genre::factory()->create();
    $genre2 = Genre::factory()->create();

    $response = $this->actingAs($admin)->patch("/games/{$game->id}", [
        'title' => $game->title,
        'genres' => [$genre1->id, $genre2->id],
    ]);

    $response->assertRedirect();

    expect($game->fresh()->genres)->toHaveCount(2);
});

/* -------------------------------------------------------------------------- */
/*  GAME VIEWING                                                              */
/* -------------------------------------------------------------------------- */

test('anyone can view the games listing', function () {
    $response = $this->get('/games');

    $response->assertOk();
});

test('anyone can view a single game page', function () {
    $game = Game::factory()->create();

    $response = $this->get("/games/{$game->id}");

    $response->assertOk();
});

test('anyone can view game discussions', function () {
    $game = Game::factory()->create();

    $response = $this->get("/games/{$game->id}/discussions");

    $response->assertOk();
});

test('authenticated user can access review creation page', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();

    $response = $this->actingAs($user)->get("/games/{$game->id}/review");

    $response->assertOk();
});
