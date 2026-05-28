<?php

use App\Models\Game;

test('it can fetch the games index api', function () {
    // Arrange: Create a couple of fake games
    Game::factory()->count(3)->create();

    // Act: Hit the endpoint
    $response = $this->getJson('/api/v1/games');

    // Assert: Check it worked and returns the right structure
    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'message' => 'Fetched games successfully.'
             ])
             ->assertJsonStructure([
                 'data' => [
                     'data' => [
                         '*' => ['id', 'title', 'average_rating']
                     ]
                 ]
             ]);
});