<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Models\Media;

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

    /**
     * Update the user's avatar or banner via uploaded media IDs.
     */
    public function updateMedia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar_media_id' => ['nullable', 'exists:media,id'],
            'banner_media_id' => ['nullable', 'exists:media,id'],
        ]);

        $user = $request->user();

        if (!empty($validated['avatar_media_id'])) {
            $media = Media::find($validated['avatar_media_id']);
            if ($media) {
                $user->avatar = $media->file_path;
            }
        }

        if (!empty($validated['banner_media_id'])) {
            $media = Media::find($validated['banner_media_id']);
            if ($media) {
                $user->banner = $media->file_path;
            }
        }

        $user->save();

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