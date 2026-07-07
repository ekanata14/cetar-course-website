<?php

namespace App\Actions\Payment;

use App\Actions\Subscription\SettlePayment;
use App\Enums\PaymentStatus;
use App\Models\Payment;

class HandleDokuWebhook
{
    public function __construct(
        private SettlePayment $settlePayment,
    ) {}

    /**
     * Proses notifikasi status dari DOKU (payload sudah terverifikasi signature-nya).
     * SUCCESS -> SettlePayment (memicu provisioning + komisi + kwitansi via event).
     * FAILED/EXPIRED -> tandai hanya jika masih pending (replay tidak menimpa status settled).
     * Return null jika invoice tidak dikenal.
     */
    public function execute(array $payload): ?Payment
    {
        $invoiceNumber = (string) data_get($payload, 'order.invoice_number');
        $status = strtoupper((string) data_get($payload, 'transaction.status'));

        $payment = Payment::where('external_id', $invoiceNumber)->first();

        if (! $payment) {
            return null;
        }

        match ($status) {
            'SUCCESS' => $this->settlePayment->execute($payment),
            'FAILED' => $this->markIfPending($payment, PaymentStatus::Failed),
            'EXPIRED' => $this->markIfPending($payment, PaymentStatus::Expired),
            default => null, // Status lain (PENDING dsb.) tidak mengubah apa pun
        };

        return $payment->refresh();
    }

    private function markIfPending(Payment $payment, PaymentStatus $status): void
    {
        if ($payment->status === PaymentStatus::Pending) {
            $payment->update(['status' => $status]);
        }
    }
}
