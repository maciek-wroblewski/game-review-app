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
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateMedia(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('users/avatars', 'public');
            $user->avatar = '/storage/' . $path;
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('users/banners', 'public');
            $user->banner = '/storage/' . $path;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-media-updated');
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

        $request->user()
            ->settings()
            ->update($validated);

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