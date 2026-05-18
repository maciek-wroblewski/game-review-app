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

            // Perform an optimized join instead of whereHas
            $newAverage = Review::join('posts', 'reviews.post_id', '=', 'posts.id')
                ->where('posts.hub_id', $gameId)
                ->where('posts.hub_type', Game::class)
                ->avg('reviews.rating');

            Game::where('id', $gameId)->update([
                'average_rating' => round($newAverage ?? 0, 2)
            ]);
        }
    }
}
