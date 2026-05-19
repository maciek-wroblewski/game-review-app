<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggle(User $user, Request $request)
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return back();
        }

        $existingFollow = DB::table('follows')
            ->where('user_id', $currentUser->id)
            ->where('followable_id', $user->id)
            ->where('followable_type', User::class)
            ->first();

        if ($existingFollow) {

            DB::table('follows')
                ->where('user_id', $currentUser->id)
                ->where('followable_id', $user->id)
                ->where('followable_type', User::class)
                ->delete();

            $status = 'unfollowed';

        } else {

            DB::table('follows')->insert([
                'user_id' => $currentUser->id,
                'followable_id' => $user->id,
                'followable_type' => User::class,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $status = 'followed';
        }

        if ($request->wantsJson()) {

            return response()->json([
                'status' => $status
            ]);
        }

        return back();
    }
}