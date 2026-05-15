<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    /**
     * Toggle the like status of a post for the authenticated user.
     */
    public function store(Post $post)
    {
        // Get the currently authenticated user, or default to User ID 1 for testing purposes
        $userId = auth()->id() ?? 1;
        
        // The toggle() method on a belongsToMany relationship automatically 
        // adds the ID if it doesn't exist, and removes it if it does!
        $post->likes()->toggle($userId);

        // Redirect back to the page they were on
        return back();
    }
}
