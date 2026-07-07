<?php

namespace App\Actions\Fortify;

use App\Actions\Auth\GenerateReferralCode;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private GenerateReferralCode $generateReferralCode,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Resolve pengundang dari kode referral (?ref= diteruskan sebagai input opsional)
        $referrer = ! empty($input['ref'])
            ? User::where('referral_code', $input['ref'])->first()
            : null;

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'referral_code' => $this->generateReferralCode->execute(),
            'referred_by' => $referrer?->id,
        ]);
    }
}
