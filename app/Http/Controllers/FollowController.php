<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;

class FollowController extends Controller
{
    public function toggle(User $user)
    {
        $currentUser = request()->user();

        if ($currentUser->id === $user->id) {
            return back();
        }

        $isFollowing = $currentUser
            ->following()
            ->where('followable_id', $user->id)
            ->exists();

        if ($isFollowing) {

            $currentUser->following()->detach($user->id);

        } else {

            $currentUser->following()->attach($user->id, [
                'followable_type' => User::class
            ]);

            Notification::create([
                'user_id' => $user->id,
                'from_user_id' => $currentUser->id,
                'type' => 'follow',
                'message' => $currentUser->username . ' started following you.',
            ]);

        }

        return back();
    }
}