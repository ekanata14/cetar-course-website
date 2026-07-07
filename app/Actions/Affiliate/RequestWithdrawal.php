<?php

namespace App\Actions\Affiliate;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestWithdrawal
{
    /**
     * Ajukan penarikan saldo komisi.
     * Saldo LANGSUNG dipotong saat pengajuan (dana di-hold) agar tidak bisa
     * diajukan ganda; penolakan admin akan mengembalikannya (lihat ProcessWithdrawal).
     *
     * @param  array{bank_name: string, account_number: string, account_name: string}  $bankDetails
     */
    public function execute(User $user, float $amount, array $bankDetails): Withdrawal
    {
        $minimum = config('cetar.min_withdrawal');

        if ($amount < $minimum) {
            throw ValidationException::withMessages([
                'amount' => __('Minimal penarikan Rp:min.', ['min' => number_format($minimum, 0, ',', '.')]),
            ]);
        }

        return DB::transaction(function () use ($user, $amount, $bankDetails) {
            // Kunci baris user agar dua pengajuan bersamaan tidak lolos dari saldo yang sama
            $user = User::whereKey($user->id)->lockForUpdate()->first();

            if ($amount > (float) $user->wallet_balance) {
                throw ValidationException::withMessages([
                    'amount' => __('Saldo tidak mencukupi.'),
                ]);
            }

            $user->decrement('wallet_balance', $amount);

            return $user->withdrawals()->create([
                'amount' => $amount,
                'bank_details' => $bankDetails,
                'status' => WithdrawalStatus::Pending,
            ]);
        });
    }
}
