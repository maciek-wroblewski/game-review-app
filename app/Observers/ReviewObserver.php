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

        if ($post && $post->hub_type === 'game' && $post->hub_id) {
            $game = Game::find($post->hub_id);
            
            if ($game) {
                // Leverage the game model's reviews relation to perform the average calculation
                $newAverage = $game->reviews()
                    ->join('reviews', 'posts.id', '=', 'reviews.post_id')
                    ->avg('reviews.rating');

                $game->average_rating = round($newAverage ?? 0, 2);
                $game->save();
            }
        }
    }
}