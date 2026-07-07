<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use App\Services\Payment\DokuClient;

class CreateDokuPayment
{
    public function __construct(
        private DokuClient $client,
    ) {}

    /**
     * Isi payment_url dari DOKU Checkout API.
     * - Kredensial kosong (dev lokal) -> invoice tetap dibuat tanpa URL, tidak error.
     * - URL sudah ada (invoice pending yang dipakai ulang) -> tidak minta sesi baru.
     */
    public function execute(Payment $payment): Payment
    {
        if (! $this->client->isConfigured() || $payment->payment_url) {
            return $payment;
        }

        $url = $this->client->createCheckoutSession($payment);

        $payment->update(['payment_url' => $url]);

        return $payment;
    }
}
