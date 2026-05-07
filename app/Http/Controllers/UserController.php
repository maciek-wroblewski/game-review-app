<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        $user->load(['games', 'posts']);
        
        $stats = [
            'games_played' => $user->games->where('pivot.status', 'played')->count(),
            'average_rating' => round($user->games->avg('pivot.personal_rating'), 1) ?: 'N/A',
            'posts_count' => $user->posts->count(),
        ];

        // Group games by status
        $lists = [
            'played' => $user->games->where('pivot.status', 'played'),
            'playing' => $user->games->where('pivot.status', 'playing'),
            'wishlisted' => $user->games->where('pivot.status', 'wishlisted'),
            'dropped' => $user->games->where('pivot.status', 'dropped'),
        ];

        return view('user.show', compact('user', 'stats', 'lists'));
    }
}
