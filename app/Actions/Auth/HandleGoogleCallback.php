<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as GoogleUser;

class HandleGoogleCallback
{
    public function __construct(
        private GenerateReferralCode $generateReferralCode,
    ) {}

    /**
     * Login/registrasi via akun Google:
     * 1. Sudah pernah login Google -> masuk.
     * 2. Email sudah terdaftar (registrasi form) -> tautkan google_id lalu masuk.
     * 3. Belum terdaftar -> buat akun baru tanpa password; email dianggap
     *    terverifikasi karena sudah diverifikasi Google.
     */
    public function execute(GoogleUser $googleUser, ?string $referralCode = null): User
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Tautkan akun lama dengan Google
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        if (! $user) {
            $referrer = $referralCode
                ? User::where('referral_code', $referralCode)->first()
                : null;

            $user = User::create([
                'name' => $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: Str::before($googleUser->getEmail(), '@'),
                'email' => $googleUser->getEmail(),
                'password' => null,
                'role' => 'user',
                'google_id' => $googleUser->getId(),
                'referral_code' => $this->generateReferralCode->execute(),
                'referred_by' => $referrer?->id,
            ]);

            // Email Google sudah terverifikasi — lolos middleware `verified` tanpa
            // perlu email verifikasi (notifikasinya otomatis di-skip untuk user verified)
            $user->forceFill(['email_verified_at' => now()])->save();

            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        return $user;
    }
}
