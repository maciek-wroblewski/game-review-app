<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameReviewController extends Controller
{
    /**
     * Show the form for creating a new review.
     */
    public function create(Game $game)
    {
        return view('reviews.create', compact('game'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Game $game)
    {
        // 1. Validate the incoming data from the form
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:10',
            'body'   => 'required|string|max:5000',
        ]);

        // Get the currently authenticated user, or default to User ID 1 for testing purposes
        $userId = auth()->id() ?? 1;

        // 2. Create the generic Post linked to the Game hub
        $post = $game->posts()->create([
            'user_id' => $userId,
            'body'    => $validated['body'],
        ]);

        // 3. Create the specific Review linked to the Post
        $post->review()->create([
            'type'   => 'recommendation', // Matches the DB enum constraint
            'rating' => $validated['rating'],
        ]);

        // Redirect back to the Hub or the Game's page with a success message
        return redirect('/Hub')->with('success', 'Review submitted successfully!');
    }
}
