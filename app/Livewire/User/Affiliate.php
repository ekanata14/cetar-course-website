<?php

namespace App\Livewire\User;

use App\Actions\Affiliate\RequestWithdrawal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Afiliasi')]
class Affiliate extends Component
{
    use Toast;

    // --- STATE FORM PENARIKAN (modal) ---
    public bool $showWithdrawForm = false;

    #[Validate('required|numeric|min:1')]
    public string $amount = '';

    #[Validate('required|string|max:100')]
    public string $bankName = '';

    #[Validate('required|string|max:50')]
    public string $accountNumber = '';

    #[Validate('required|string|max:100')]
    public string $accountName = '';

    /** Komisi yang diterima user sebagai referrer */
    #[Computed]
    public function commissions()
    {
        return auth()->user()->referralCommissions()
            ->with('referred:id,name')
            ->latest()
            ->take(20)
            ->get();
    }

    /** Riwayat pengajuan penarikan */
    #[Computed]
    public function withdrawals()
    {
        return auth()->user()->withdrawals()->latest()->take(20)->get();
    }

    /** Link registrasi berkode referral untuk dibagikan */
    #[Computed]
    public function referralLink(): string
    {
        return route('register', ['ref' => auth()->user()->referral_code]);
    }

    public function openWithdrawForm(): void
    {
        $this->reset(['amount', 'bankName', 'accountNumber', 'accountName']);
        $this->resetValidation();
        $this->showWithdrawForm = true;
    }

    public function requestWithdrawal(RequestWithdrawal $action): void
    {
        $this->validate();

        // Action melempar ValidationException (field: amount) jika saldo kurang / di bawah minimum
        $action->execute(auth()->user(), (float) $this->amount, [
            'bank_name' => $this->bankName,
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
        ]);

        unset($this->withdrawals);
        $this->showWithdrawForm = false;
        $this->success('Pengajuan penarikan terkirim.', description: 'Admin akan memprosesnya segera.', position: 'toast-top');
    }

    public function render()
    {
        return view('livewire.user.affiliate');
    }
}
