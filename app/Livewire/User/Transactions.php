<?php

namespace App\Livewire\User;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Riwayat Transaksi')]
class Transactions extends Component
{
    use WithPagination;

    /** Filter status: '' = semua, atau salah satu nilai PaymentStatus */
    #[Url]
    public string $filter = '';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function payments()
    {
        return auth()->user()->payments()
            ->with('packagePlan.package')
            ->when($this->filter !== '', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.user.transactions');
    }
}
