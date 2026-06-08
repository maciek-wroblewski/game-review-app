<?php

namespace App\View\Components\User;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class HomeSidebarProfile extends Component
{
    public $user;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->user = auth()->user();
        if ($this->user) {
            $this->user->loadCount(['posts', 'reviews', 'followers', 'following']);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.user.home-sidebar-profile');
    }
}
