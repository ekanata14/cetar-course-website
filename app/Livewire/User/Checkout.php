<?php

namespace App\Livewire\User;

use App\Actions\Payment\InitiateCheckout;
use App\Enums\PaymentStatus;
use App\Models\PackagePlan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Checkout')]
class Checkout extends Component
{
    use Toast;

    public PackagePlan $plan;

    public function mount(PackagePlan $plan)
    {
        abort_unless($plan->package->is_active, 404);

        $this->plan = $plan->load('package');
        $this->plan->package->loadCount('quizzes');
    }

    /** Invoice pending yang sudah ada untuk plan ini — dipakai ulang saat bayar */
    #[Computed]
    public function pendingPayment()
    {
        return auth()->user()->payments()
            ->where('package_plan_id', $this->plan->id)
            ->where('status', PaymentStatus::Pending)
            ->first();
    }

    /** Konfirmasi pesanan: buat/pakai-ulang invoice + sesi DOKU, lalu ke halaman bayar */
    public function pay(InitiateCheckout $action)
    {
        try {
            $payment = $action->execute(auth()->user(), $this->plan);
        } catch (\Throwable $e) {
            Log::error('DOKU checkout gagal', [
                'user_id' => auth()->id(),
                'plan_id' => $this->plan->id,
                'error' => $e->getMessage(),
            ]);

            $this->error(
                'Pembayaran gagal dibuat',
                description: 'Coba lagi beberapa saat, atau hubungi admin jika masalah berlanjut.',
                position: 'toast-top',
            );

            return;
        }

        // Kredensial DOKU terpasang -> langsung ke halaman bayar gateway
        if ($payment->payment_url) {
            return $this->redirect($payment->payment_url);
        }

        // Gateway belum dikonfigurasi: di produksi ini adalah error, jangan diam-diam
        if (app()->isProduction()) {
            Log::error('DOKU belum dikonfigurasi di produksi', ['payment_id' => $payment->id]);

            $this->error(
                'Pembayaran sedang tidak tersedia',
                description: 'Silakan coba lagi nanti atau hubungi admin.',
                position: 'toast-top',
            );

            return;
        }

        // Mode dev tanpa gateway: invoice tetap tercatat, arahkan ke riwayat transaksi
        session()->flash('success', 'Invoice '.$payment->external_id.' tercatat. Cek email kamu untuk detail tagihan.');

        return $this->redirectRoute('user.transactions');
    }

    public function render()
    {
        return view('livewire.user.checkout');
    }
}
