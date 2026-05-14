<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function saved(Review $review)
    {
        $this->updateGameRating($review);
    }

    public function deleted(Review $review)
    {
        $this->updateGameRating($review);
    }

    protected function updateGameRating(Review $review)
    {
        // 1. Get the game through the post relationship
        $game = $review->post->hub; 

        // Ensure the post actually belongs to a Game hub
        if ($game instanceof \App\Models\Game) {
            // 2. Recalculate average from all related reviews
            $average = $game->posts()
                ->whereHas('review')
                ->join('reviews', 'posts.id', '=', 'reviews.post_id')
                ->avg('reviews.rating');

            // 3. Save to the games table
            $game->update(['average_rating' => $average ?? 0]);
        }
    }
}