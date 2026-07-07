<?php

namespace App\Actions\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\UserSubscription;

class ProvisionPackageAccess
{
    /**
     * Buka/perpanjang akses paket dari sebuah pembayaran settled.
     * - Belum pernah berlangganan  -> buat subscription baru (now + duration).
     * - Masih aktif                -> perpanjang dari expires_at yang berjalan.
     * - Sudah expired/suspended    -> aktifkan lagi mulai sekarang.
     */
    public function execute(Payment $payment): UserSubscription
    {
        $plan = $payment->packagePlan;

        $subscription = UserSubscription::query()
            ->where('user_id', $payment->user_id)
            ->where('package_id', $plan->package_id)
            ->latest('expires_at')
            ->first();

        // Perpanjangan dihitung dari sisa masa aktif (kalau masih ada), bukan menimpanya
        $base = $subscription && $subscription->expires_at->isFuture()
            ? $subscription->expires_at
            : now();

        $expiresAt = $base->addDays($plan->duration_days);

        if ($subscription) {
            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'expires_at' => $expiresAt,
            ]);

            return $subscription->refresh();
        }

        return UserSubscription::create([
            'user_id' => $payment->user_id,
            'package_id' => $plan->package_id,
            'status' => SubscriptionStatus::Active,
            'expires_at' => $expiresAt,
        ]);
    }
}
