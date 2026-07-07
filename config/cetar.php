<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Affiliate
    |--------------------------------------------------------------------------
    | commission_rate : porsi komisi referrer dari setiap pembayaran settled
    |                   milik user yang diundangnya (0.10 = 10%).
    | min_withdrawal  : nominal minimum pengajuan penarikan saldo (Rupiah).
    */

    'commission_rate' => (float) env('CETAR_COMMISSION_RATE', 0.10),

    'min_withdrawal' => (int) env('CETAR_MIN_WITHDRAWAL', 50000),

];
