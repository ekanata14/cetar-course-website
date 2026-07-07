<?php

namespace App\DTOs\Auth;

class RegisterData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $referralCode = null, // Kode referral pengundang (dari ?ref=), opsional
    ) {}
}
