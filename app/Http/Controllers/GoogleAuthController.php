<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google belum dikonfigurasi.',
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! $this->isGoogleConfigured()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google belum dikonfigurasi.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'Proses login Google gagal. Coba lagi dari tombol Login dengan Google.',
            ]);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'User Google',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(32)),
                'role' => 'member',
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->route('home')->with('success', 'Login Google berhasil.');
    }

    private function isGoogleConfigured(): bool
    {
        return filled(Config::get('services.google.client_id'))
            && filled(Config::get('services.google.client_secret'))
            && filled(Config::get('services.google.redirect'));
    }
}
