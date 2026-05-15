<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Post $post)
    {
        $userId = auth()->id() ?? 1;

        $post->toggleLike($userId);

        return back();
    }
}
