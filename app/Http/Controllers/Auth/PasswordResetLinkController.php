<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\TemporaryPasswordMail; // <-- Pamiętaj o imporcie naszego maila!
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('common.user_not_found')]);
        }

        $temporaryPassword = Str::random(12);

        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
        ])->save();

        Log::info('Temporary password generated for user: '.$user->username.' (ID: '.$user->id.')');

        try {
            Mail::to($user->email)->send(new TemporaryPasswordMail($temporaryPassword));
        } catch (\Exception $e) {
            Log::error('Error sending temporary password email: ' . $e->getMessage());
            return back()->with('error', __('common.password_reset_email_error'));
        }

        Log::info('Temporary password email sent to user: '.$user->username.' (ID: '.$user->id.')');
        return back()->with('status', __('common.password_reset_email_sent'));
    }
}