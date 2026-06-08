<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('users/avatars', 'public');
            $user->avatar = '/storage/' . $path;
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('users/banners', 'public');
            $user->banner = '/storage/' . $path;
        }

        // Update profile fields (excluding file fields)
        $user->fill(array_diff_key($validated, array_flip(['avatar', 'banner'])));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        \Illuminate\Support\Facades\Cache::forget("user_profile_model_{$user->id}");

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePrivacy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_visibility' => [
                'required',
                Rule::in([
                    'public',
                    'followers',
                    'mutuals',
                    'private',
                ]),
            ],

            'playlist_visibility' => [
                'required',
                Rule::in([
                    'public',
                    'followers',
                    'mutuals',
                    'private',
                ]),
            ],
        ]);

        $user = $request->user();
        $user->settings()->update($validated);

        \Illuminate\Support\Facades\Cache::forget("user_profile_model_{$user->id}");

        return back()->with('status', 'privacy-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}