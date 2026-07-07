<?php

namespace App\Actions\Subscription;

use App\Enums\PaymentStatus;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class CreateCheckout
{
    /**
     * Buat invoice pending untuk sebuah plan.
     * external_id = nomor invoice unik yang dikirim ke DOKU dan dipakai
     * mencocokkan webhook (Phase 6 mengisi payment_url dari API DOKU).
     */
    public function execute(User $user, PackagePlan $plan): Payment
    {
        // Satu invoice pending per plan per user — klik "Beli" dua kali tidak menumpuk tagihan
        $existing = $user->payments()
            ->where('package_plan_id', $plan->id)
            ->where('status', PaymentStatus::Pending)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $user->payments()->create([
            'package_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => PaymentStatus::Pending,
            'external_id' => 'CETAR-INV-'.strtoupper(Str::ulid()),
        ]);
    }
}
