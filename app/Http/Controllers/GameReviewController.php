<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;

class GameReviewController extends Controller
{
    public function create(Game $game)
    {
        return view('reviews.create', compact('game'));
    }

    public function store(StoreReviewRequest $request, Game $game)
    {
        $validated = $request->validated();
        $userId = auth()->id();

        if ($game->posts()->where('user_id', $userId)->whereHas('review')->exists()) {
            return back()->withErrors(['rating' => __('common.already_reviewed')]);
        }

        $post = $game->posts()->create([
            'user_id' => $userId,
            'body'    => $validated['body'],
        ]);

        $post->review()->create([
            'type'   => 'recommendation',
            'rating' => $validated['rating'],
        ]);

        \Illuminate\Support\Facades\Cache::forget("game_{$game->id}_reviews_page_1");
        \Illuminate\Support\Facades\Cache::forget("game_{$game->id}_reviews_count");
        \Illuminate\Support\Facades\Cache::forget("game_show_model_{$game->id}");

        return redirect('/games/' . $game->id)->with('success', __('common.review_submitted'));
    }

    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validated();

        $review->update([
            'rating' => $validated['rating'],
        ]);

        $review->post->update([
            'body' => $validated['body'],
        ]);

        $gameId = $review->game->id;
        \Illuminate\Support\Facades\Cache::forget("game_{$gameId}_reviews_page_1");
        \Illuminate\Support\Facades\Cache::forget("game_{$gameId}_reviews_count");
        \Illuminate\Support\Facades\Cache::forget("game_show_model_{$gameId}");

        return redirect('/games/' . $review->game->id)->with('success', __('common.review_updated'));
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $gameId = $review->game->id;
        $review->delete();

        \Illuminate\Support\Facades\Cache::forget("game_{$gameId}_reviews_page_1");
        \Illuminate\Support\Facades\Cache::forget("game_{$gameId}_reviews_count");
        \Illuminate\Support\Facades\Cache::forget("game_show_model_{$gameId}");

        return redirect('/games/' . $gameId)->with('success', __('common.review_deleted'));
    }
}
