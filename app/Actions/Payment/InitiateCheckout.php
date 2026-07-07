<?php

namespace App\Actions\Payment;

use App\Actions\Subscription\CreateCheckout;
use App\Mail\InvoiceCreated;
use App\Models\PackagePlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InitiateCheckout
{
    public function __construct(
        private CreateCheckout $createCheckout,
        private CreateDokuPayment $createDokuPayment,
    ) {}

    /**
     * Alur checkout lengkap: buat/pakai-ulang invoice pending -> minta URL bayar DOKU
     * -> kirim email invoice (hanya untuk invoice BARU, bukan klik "Beli" ulang).
     */
    public function execute(User $user, PackagePlan $plan): Payment
    {
        $payment = $this->createCheckout->execute($user, $plan);

        // Email menyertakan link bayar, jadi DOKU dipanggil dulu sebelum queue mail
        $payment = $this->createDokuPayment->execute($payment);

        if ($payment->wasRecentlyCreated) {
            Mail::to($user)->queue(new InvoiceCreated($payment));
        }

        return $payment;
    }
}
