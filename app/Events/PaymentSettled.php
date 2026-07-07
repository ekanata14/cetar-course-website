<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dipancarkan tepat saat sebuah Payment berpindah status ke `settled`
 * (dari webhook DOKU di Phase 6, atau penyelesaian manual oleh admin).
 * Listener: ProcessSettledPayment (provisioning akses + komisi afiliasi).
 */
class PaymentSettled
{
    use Dispatchable;

    public function __construct(
        public Payment $payment,
    ) {}
}
