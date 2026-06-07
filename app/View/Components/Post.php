<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Post extends Component
{
    public $post;
    public $showReplies;
    public $isReview;
    public $isAdmin;

    public function __construct($post, $showReplies = true)
    {
        $this->post = $post;
        $this->showReplies = $showReplies;
        
        $this->isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
        $this->isAdmin = auth()->user()?->is_admin ?? false;
    }

    public function render(): View|Closure|string
    {
        return view('components.post');
    }
}