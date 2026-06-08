<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     * Preloads user counts to prevent N+1 queries in the view.
     */
    public function show(): View
    {
        $user = auth()->user();
        
        // Preload counts to avoid N+1 queries when rendering
        $user->loadCount(['posts', 'playlists', 'followers', 'following']);

        return view('dashboard', compact('user'));
    }
}
