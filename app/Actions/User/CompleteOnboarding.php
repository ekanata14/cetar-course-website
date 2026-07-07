<?php

namespace App\Actions\User;

use App\Models\User;

class CompleteOnboarding
{
    /**
     * Tandai user selesai onboarding (idempotent).
     * Dipanggil saat user menyelesaikan tutorial, memilih paket, atau klik "Lewati".
     */
    public function execute(User $user): void
    {
        if (! $user->hasOnboarded()) {
            $user->forceFill(['onboarded_at' => now()])->save();
        }
    }
}
