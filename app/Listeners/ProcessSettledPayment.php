<?php

namespace App\Listeners;

use App\Actions\Affiliate\DistributeCommission;
use App\Actions\Subscription\ProvisionPackageAccess;
use App\Events\PaymentSettled;
use App\Mail\PaymentReceipt;
use Illuminate\Support\Facades\Mail;

/**
 * Rangkaian pasca-pembayaran (event-driven, lihat system_architecture.md):
 * 1. Buka/perpanjang akses paket milik pembeli.
 * 2. Bagikan komisi ke pengundangnya (jika ada).
 * 3. Kirim kwitansi (queued) ke pembeli.
 * Terdaftar otomatis via event discovery Laravel (type-hint di handle()).
 */
class ProcessSettledPayment
{
    public function __construct(
        private ProvisionPackageAccess $provisionPackageAccess,
        private DistributeCommission $distributeCommission,
    ) {}

    public function handle(PaymentSettled $event): void
    {
        $subscription = $this->provisionPackageAccess->execute($event->payment);
        $this->distributeCommission->execute($event->payment);

        Mail::to($event->payment->user)->queue(new PaymentReceipt($event->payment, $subscription));
    }
}
