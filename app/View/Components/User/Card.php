<?php

namespace App\View\Components\User;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Card extends Component
{
    public $user;
    public $layout;
    public $interactive;
    public $isCompact;

    public $reviewsCount;
    public $followersCount;
    public $followingCount;
    public $postsCount;
    public $playlistsCount;

    public function __construct($user, $layout = 'full', $interactive = true)
    {
        $this->user = $user;
        $this->layout = $layout;
        $this->interactive = $interactive;
        $this->isCompact = $layout === 'compact';

        // Prefer preloaded counts from controller (set via withCount)
        // Fall back to existing attributes if set, otherwise default to 0 to avoid per-user DB queries
        $this->reviewsCount = $user->reviews_count ?? 0;
        $this->followersCount = $user->followers_count ?? 0;
        $this->followingCount = $user->following_count ?? 0;

        if (!$this->isCompact) {
            $this->postsCount = $user->posts_count ?? 0;
            $this->playlistsCount = $user->playlists_count ?? 0;
        } else {
            $this->postsCount = 0;
            $this->playlistsCount = 0;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.user.card');
    }
}