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

        // 1. Szukamy użytkownika po adresie e-mail
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Nie znaleźliśmy użytkownika z tym adresem e-mail.']);
        }

        // 2. Generujemy losowe, bezpieczne hasło składające się z 12 znaków
        $temporaryPassword = Str::random(12);

        // 3. Aktualizujemy hasło użytkownika w bazie danych (haszując je!)
        $user->forceFill([
            'password' => Hash::make($temporaryPassword),
        ])->save();

        // 4. Wysyłamy maila w bloku try-catch dla pełnego bezpieczeństwa
        try {
            Mail::to($user->email)->send(new TemporaryPasswordMail($temporaryPassword));
        } catch (\Exception $e) {
            Log::error('Błąd wysyłki hasła tymczasowego: ' . $e->getMessage());
            return back()->with('error', 'Wystąpił problem z wysyłką e-maila. Skontaktuj się z administratorem.');
        }

        // Zwracamy status sukcesu do widoku
        return back()->with('status', 'Nowe hasło tymczasowe zostało wysłane na Twój adres e-mail.');
    }
}