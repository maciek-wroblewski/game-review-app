<?php

use App\Models\Game;
use App\Models\Genre;

it('seeds the database and associates genres with games successfully', function () {
    // Database starts fresh in Pest because of RefreshDatabase trait.
    expect(Game::count())->toBe(0);
    expect(Genre::count())->toBe(0);

    // Run the DatabaseSeeder
    $this->artisan('db:seed');

    // Verify genres and games were successfully seeded
    expect(Genre::count())->toBeGreaterThan(0);
    expect(Game::count())->toBeGreaterThan(0);

    // Verify all games have at least one genre associated
    $games = Game::with('genres')->get();
    foreach ($games as $game) {
        expect($game->genres->count())->toBeGreaterThanOrEqual(1);
    }
});
