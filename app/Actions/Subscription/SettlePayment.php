<?php

namespace App\Actions\Subscription;

use App\Enums\PaymentStatus;
use App\Events\PaymentSettled;
use App\Models\Payment;

class SettlePayment
{
    /**
     * Tandai pembayaran sebagai settled dan pancarkan event PaymentSettled.
     * Dipanggil oleh webhook DOKU (Phase 6) — atau langsung di test/manual admin.
     * Hanya payment pending yang bisa settled (replay webhook tidak memicu event ganda).
     */
    public function execute(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Pending) {
            return $payment;
        }

        $payment->update(['status' => PaymentStatus::Settled]);

        event(new PaymentSettled($payment->refresh()));

        return $payment;
    }
}
