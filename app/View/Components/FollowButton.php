<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FollowButton extends Component
{
    public $targetUser;
    public $buttonClasses;
    public $isFollowing = false;

    public function __construct($targetUser, $buttonClasses = 'btn-sm rounded-pill px-3 shadow-sm')
    {
        $this->targetUser = $targetUser;
        $this->buttonClasses = $buttonClasses;

        $authUser = auth()->user();

        if ($authUser && $authUser->id !== $targetUser->id) {
            // If the target user has following_ids preloaded from controller, use it
            if ($targetUser->getAttribute('is_followed_by_auth') !== null) {
                $this->isFollowing = (bool) $targetUser->getAttribute('is_followed_by_auth');
            }
            // If a memoized method exists (for batch processing), use it
            elseif (method_exists($authUser, 'isFollowingUser')) {
                $this->isFollowing = $authUser->isFollowingUser($targetUser->id);
            }
            // Otherwise, default to false to avoid N+1 queries
            // Controllers should preload this data or use a batched approach
            else {
                $this->isFollowing = false;
            }
        }
    }

    /**
     * If this returns false, Laravel will not render the component's HTML at all.
     */
    public function shouldRender(): bool
    {
        $authUser = auth()->user();
        
        return $authUser 
            && $authUser->id !== $this->targetUser->id 
            && !$this->targetUser->is_suspended;
    }

    public function render(): View|Closure|string
    {
        return view('components.follow-button');
    }
}