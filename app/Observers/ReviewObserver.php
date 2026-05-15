<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Game;

class ReviewObserver
{
    public function saved(Review $review)
    {
        $this->recalculateGameRating($review);
    }

    public function deleted(Review $review)
    {
        $this->recalculateGameRating($review);
    }

    protected function recalculateGameRating(Review $review)
    {
        $post = $review->post;

        if ($post && $post->hub_type === Game::class && $post->hub_id) {
            $gameId = $post->hub_id;

            $newAverage = Review::whereHas('post', function ($query) use ($gameId) {
                $query->where('hub_id', $gameId)
                      ->where('hub_type', Game::class);
            })->avg('rating');

            Game::where('id', $gameId, null, null)->update([
                'average_rating' => round($newAverage ?? 0, 2) // Default to 0 if no reviews left
            ]);
        }
    }
}