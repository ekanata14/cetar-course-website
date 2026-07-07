<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class GenerateReferralCode
{
    /**
     * Generate kode referral unik untuk user baru (dipakai mengundang orang lain).
     * Format: CETAR + 5 karakter alfanumerik, contoh: CETARX7K2P.
     */
    public function execute(): string
    {
        do {
            $code = 'CETAR'.strtoupper(Str::random(5));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }
}
