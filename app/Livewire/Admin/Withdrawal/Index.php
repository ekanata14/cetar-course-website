<?php

namespace App\Livewire\Admin\Withdrawal;

use App\Actions\Affiliate\ProcessWithdrawal;
use App\Models\Withdrawal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Penarikan Saldo')]
class Index extends Component
{
    use Toast, WithPagination;

    /** Antrian pengajuan yang menunggu diproses */
    #[Computed]
    public function pending()
    {
        return Withdrawal::with('user:id,name,email')
            ->where('status', 'pending')
            ->oldest() // FIFO: yang mengajukan duluan diproses duluan
            ->get();
    }

    public function approve(Withdrawal $withdrawal, ProcessWithdrawal $action): void
    {
        $action->execute($withdrawal, auth()->user(), approve: true);

        unset($this->pending);
        $this->success('Penarikan disetujui.', description: 'Jangan lupa transfer manual ke rekening user.', position: 'toast-top');
    }

    public function reject(Withdrawal $withdrawal, ProcessWithdrawal $action): void
    {
        $action->execute($withdrawal, auth()->user(), approve: false);

        unset($this->pending);
        $this->success('Penarikan ditolak — saldo dikembalikan ke user.', position: 'toast-top');
    }

    public function render()
    {
        // Riwayat yang sudah diproses (paginated)
        $history = Withdrawal::with(['user:id,name,email', 'processedBy:id,name'])
            ->where('status', '!=', 'pending')
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.admin.withdrawal.index', [
            'history' => $history,
        ]);
    }
}
