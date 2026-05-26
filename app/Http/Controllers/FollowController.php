<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewFollowerMail;

class FollowController extends Controller
{
    public function toggle(User $user, Request $request)
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
            $status = 'unfollowed'; // Track status for JSON
        } else {
            $currentUser->following()->attach($user->id);

            Notification::create([
                'user_id' => $user->id,
                'from_user_id' => $currentUser->id,
                'type' => 'follow',
                'message' => __(':username started following you.', ['username' => $currentUser->username]),
                'target_url' => url('/users/' . $currentUser->id),
            ]);
            Mail::to($user->email)->send(new NewFollowerMail($currentUser));
            $status = 'followed'; // Track status for JSON
        }

        // Return JSON if requested via AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'status' => $status
            ]);
        }

        return back();
    }
}