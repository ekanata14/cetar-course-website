<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    public function __construct(
        private GenerateReferralCode $generateReferralCode,
    ) {}

    public function execute(RegisterData $data): User
    {
        // Resolve pengundang dari kode referral (jika ada & valid).
        // Kode tidak valid diabaikan diam-diam agar tidak memblokir registrasi.
        $referrer = $data->referralCode
            ? User::where('referral_code', $data->referralCode)->first()
            : null;

        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'role' => 'user',
            'referral_code' => $this->generateReferralCode->execute(),
            'referred_by' => $referrer?->id,
        ]);

        // Auto login setelah register
        Auth::login($user);

        // Trigger event agar email verifikasi terkirim
        event(new Registered($user));

        return $user;
    }
}
