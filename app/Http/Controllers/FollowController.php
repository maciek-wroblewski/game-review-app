<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewFollowerMail;
use Illuminate\Support\Facades\Log;

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
            Notification::where([
                'user_id' => $user->id,
                'from_user_id' => $currentUser->id,
                'type' => 'follow',
            ])->delete();
            $status = 'unfollowed'; // Track status for JSON
            Log::info("User {$currentUser->id} unfollowed User {$user->id}");
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
            Log::info("User {$currentUser->id} followed User {$user->id}");
            $status = 'followed'; // Track status for JSON
        }

        \Illuminate\Support\Facades\Cache::forget("user_" . $currentUser->id . "_following_ids");
        \Illuminate\Support\Facades\Cache::forget("user_" . $user->id . "_following_ids");
        \Illuminate\Support\Facades\Cache::forget("user_profile_model_{$currentUser->id}");
        \Illuminate\Support\Facades\Cache::forget("user_profile_model_{$user->id}");

        // Return JSON if requested via AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'status' => $status
            ]);
        }

        return back();
    }
}