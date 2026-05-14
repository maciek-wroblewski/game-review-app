<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;

class GameReviewController extends Controller
{
    public function create(Game $game)
    {
        return view('reviews.create', compact('game'));
    }

    public function store(Request $request, Game $game)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'body'   => 'required|string|max:5000',
        ]);

        $userId = auth()->id() ?? 1;

        if ($game->posts()->where('user_id', $userId)->whereHas('review')->exists()) {
            return back()->withErrors(['rating' => 'You have already reviewed this game.']);
        }

        $post = $game->posts()->create([
            'user_id' => $userId,
            'body'    => $validated['body'],
        ]);

        $post->review()->create([
            'type'   => 'recommendation',
            'rating' => $validated['rating'],
        ]);

        return redirect('/games/' . $game->id)->with('success', 'Review submitted successfully!');
    }

    public function edit(Review $review)
    {
        return view('reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'body'   => 'required|string|max:5000',
        ]);

        $review->update([
            'rating' => $validated['rating'],
        ]);

        $review->post->update([
            'body' => $validated['body'],
        ]);

        return redirect('/games/' . $review->game->id)->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        
        return redirect('/games/' . $review->game->id)->with('success', 'Review deleted successfully!');
    }
}
