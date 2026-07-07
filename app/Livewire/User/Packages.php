<?php

namespace App\Livewire\User;

use App\Models\Package;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Paket Belajar')]
class Packages extends Component
{
    use Toast;

    /** Diisi 'payment-return' oleh callback_url DOKU saat user kembali dari halaman bayar */
    #[Url]
    public ?string $status = null;

    /** Katalog paket aktif beserta tier harganya */
    #[Computed]
    public function packages()
    {
        return Package::active()
            ->with(['plans' => fn ($q) => $q->orderBy('duration_days')])
            ->withCount('quizzes')
            ->orderBy('name')
            ->get();
    }

    /** Id paket yang sedang aktif dimiliki user (untuk badge "Aktif") */
    #[Computed]
    public function activePackageIds()
    {
        return auth()->user()->subscriptions()->active()->pluck('package_id')->all();
    }

    public function render()
    {
        return view('livewire.user.packages');
    }
}
