<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleGoogleCallback;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /** Arahkan user ke halaman consent Google; simpan kode referral (?ref=) dulu di session */
    public function redirect(Request $request)
    {
        if ($request->filled('ref')) {
            $request->session()->put('referral_code', $request->query('ref'));
        }

        return Socialite::driver('google')->redirect();
    }

    /** Callback dari Google: login/registrasi lalu arahkan sesuai role & status onboarding */
    public function callback(Request $request, HandleGoogleCallback $action)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth gagal', ['error' => $e->getMessage()]);

            return redirect()->route('login')->with('error', 'Login dengan Google gagal, silakan coba lagi.');
        }

        $user = $action->execute($googleUser, $request->session()->pull('referral_code'));

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return $user->hasOnboarded()
            ? redirect()->route('user.dashboard')
            : redirect()->route('user.onboarding');
    }
}
