<?php

namespace App\Actions\Affiliate;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class ProcessWithdrawal
{
    /**
     * Admin memproses pengajuan penarikan:
     * - approve -> status success (dana sudah dipotong saat pengajuan, admin transfer manual).
     * - reject  -> status rejected + saldo dikembalikan ke user.
     * Hanya pengajuan pending yang bisa diproses (idempotent terhadap klik ganda).
     */
    public function execute(Withdrawal $withdrawal, User $admin, bool $approve): Withdrawal
    {
        if ($withdrawal->status !== WithdrawalStatus::Pending) {
            return $withdrawal;
        }

        return DB::transaction(function () use ($withdrawal, $admin, $approve) {
            if (! $approve) {
                // Penolakan mengembalikan dana yang di-hold saat pengajuan
                $withdrawal->user->increment('wallet_balance', $withdrawal->amount);
            }

            $withdrawal->update([
                'status' => $approve ? WithdrawalStatus::Success : WithdrawalStatus::Rejected,
                'processed_by' => $admin->id,
            ]);

            return $withdrawal->refresh();
        });
    }
}
