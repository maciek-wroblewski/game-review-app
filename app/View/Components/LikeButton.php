<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LikeButton extends Component
{
    public $post;
    public $hasLiked = false;
    public $count = 0;
    public $isGuest = true;

    public function __construct($post)
    {
        $this->post = $post;
        $this->isGuest = auth()->guest();
        
        // Prefer likes_count attribute (set via withCount), fallback to 0 to avoid queries
        $this->count = $post->likes_count ?? 0;

        // Determine if authenticated user has liked the post
        if (!$this->isGuest) {
            $authId = auth()->id();
            
            // Prefer liked_by_auth attribute (set via withExists), fallback to false to avoid N+1 queries
            if ($post->getAttribute('liked_by_auth') !== null) {
                $this->hasLiked = (bool) $post->getAttribute('liked_by_auth');
            } elseif ($post->relationLoaded('likes')) {
                // If likes relation is preloaded, use it
                $this->hasLiked = $post->likes->contains('id', $authId);
            } else {
                // Avoid N+1: if data not preloaded, default to false
                // Controllers should use withExists() for posts rendered in lists
                $this->hasLiked = false;
            }
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.like-button');
    }
}