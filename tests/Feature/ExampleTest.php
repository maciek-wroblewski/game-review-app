<?php

use App\Models\User;
use App\Models\Post;

it('returns a successful response', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

it('paginates posts on the home page via ajax', function () {
    // Seed 25 posts
    $user = User::factory()->create();
    Post::factory()->count(25)->create([
        'user_id' => $user->id,
        'parent_id' => null,
    ]);

    // Request global_feed via AJAX
    $response = $this->getJson('/?tab=global_feed&page=2', [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);
    $response->assertStatus(200);
    $data = $response->json();
    expect($data['next_page_url'])->not->toBeNull();
    expect($data['html'])->not->toBeEmpty();

    // Request trending page 1 first to seed cache
    $this->getJson('/?tab=trending&page=1', [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);

    // Request trending page 2 (should hit cache page 2, or construct page 2 and cache it)
    $responseTrending = $this->getJson('/?tab=trending&page=2', [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);
    $responseTrending->assertStatus(200);
    $dataTrending = $responseTrending->json();
    expect($dataTrending['html'])->not->toBeEmpty();

    // Request popular_reviews via AJAX
    // Ensure we have reviews
    $posts = Post::factory()->count(15)->create([
        'user_id' => $user->id,
        'parent_id' => null,
    ]);
    foreach ($posts as $post) {
        $post->review()->create([
            'type' => 'recommendation',
            'rating' => 5,
        ]);
    }
    
    $responseReviews = $this->getJson('/?tab=popular_reviews&page=2', [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);
    $responseReviews->assertStatus(200);
    $dataReviews = $responseReviews->json();
    expect($dataReviews['html'])->not->toBeEmpty();

    // Request my_feed via AJAX
    $follower = User::factory()->create();
    $followed = User::factory()->create();
    // Follow
    $follower->following()->attach($followed->id);

    // Seed 15 posts for the followed user
    Post::factory()->count(15)->create([
        'user_id' => $followed->id,
        'parent_id' => null,
    ]);

    // Request via AJAX as authenticated follower
    $responseMyFeed = $this->actingAs($follower)->getJson('/?tab=my_feed&page=2', [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);
    $responseMyFeed->assertStatus(200);
    $dataMyFeed = $responseMyFeed->json();
    expect($dataMyFeed['html'])->not->toBeEmpty();
});

