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

        // Lazy-load counts only if they are not already preloaded (to avoid N+1 queries in feeds)
        $countsToLoad = [];
        if (!array_key_exists('reviews_count', $user->getAttributes())) {
            $countsToLoad[] = 'reviews';
        }
        if (!array_key_exists('followers_count', $user->getAttributes())) {
            $countsToLoad[] = 'followers';
        }
        if (!array_key_exists('following_count', $user->getAttributes())) {
            $countsToLoad[] = 'following';
        }
        if (!$this->isCompact) {
            if (!array_key_exists('posts_count', $user->getAttributes())) {
                $countsToLoad[] = 'posts';
            }
            if (!array_key_exists('playlists_count', $user->getAttributes())) {
                $countsToLoad[] = 'playlists';
            }
        }

        if (!empty($countsToLoad)) {
            $user->loadCount($countsToLoad);
        }

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