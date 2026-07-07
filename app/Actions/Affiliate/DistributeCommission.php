<?php

namespace App\Actions\Affiliate;

use App\Enums\CommissionStatus;
use App\Models\AffiliateCommission;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DistributeCommission
{
    /**
     * Bagikan komisi referral dari pembayaran settled:
     * - payer tidak punya pengundang -> tidak ada komisi.
     * - idempotent: satu payment maksimal satu komisi (aman terhadap replay webhook).
     * - ledger (affiliate_commissions) + saldo (wallet_balance) diubah dalam satu transaksi.
     */
    public function execute(Payment $payment): ?AffiliateCommission
    {
        $referrer = $payment->user->referrer;

        if (! $referrer) {
            return null;
        }

        // Guard idempotensi: komisi untuk payment ini sudah pernah dibagikan
        if (AffiliateCommission::where('payment_id', $payment->id)->exists()) {
            return null;
        }

        $amount = round($payment->amount * config('cetar.commission_rate'), 2);

        return DB::transaction(function () use ($payment, $referrer, $amount) {
            $commission = AffiliateCommission::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'status' => CommissionStatus::Success,
            ]);

            // Saldo langsung bertambah — penarikan diproses terpisah via withdrawals
            $referrer->increment('wallet_balance', $amount);

            return $commission;
        });
    }
}
