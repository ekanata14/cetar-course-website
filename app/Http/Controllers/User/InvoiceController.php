<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /** Unduh invoice PDF milik user untuk sebuah pembayaran */
    public function __invoke(Payment $payment)
    {
        abort_unless($payment->user_id === auth()->id(), 403);

        $payment->load('packagePlan.package', 'user');

        return Pdf::loadView('pdf.invoice', ['payment' => $payment])
            ->download('invoice-'.$payment->external_id.'.pdf');
    }
}
