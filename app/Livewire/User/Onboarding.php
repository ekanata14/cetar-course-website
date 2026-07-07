<?php

namespace App\Livewire\User;

use App\Actions\User\CompleteOnboarding;
use App\Models\Package;
use App\Models\PackagePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('layouts.app')]
#[Title('Selamat Datang di Cetar')]
class Onboarding extends Component
{
    use Toast;

    public function mount()
    {
        // Onboarding hanya untuk user baru
        if (auth()->user()->hasOnboarded()) {
            return $this->redirectRoute('user.dashboard');
        }
    }

    /** Katalog paket aktif — query identik dengan halaman Paket Belajar */
    #[Computed]
    public function packages()
    {
        return Package::active()
            ->with(['plans' => fn ($q) => $q->orderBy('duration_days')])
            ->withCount('quizzes')
            ->orderBy('name')
            ->get();
    }

    /**
     * Pilih paket dari onboarding: tandai onboarded dulu (agar tidak terjebak
     * kembali ke onboarding), lalu lanjut ke halaman checkout.
     */
    public function checkout(PackagePlan $plan, CompleteOnboarding $complete)
    {
        abort_unless($plan->package->is_active, 404);

        $complete->execute(auth()->user());

        return $this->redirectRoute('user.checkout', $plan);
    }

    /** "Lewati" / "Selesai": tandai onboarded lalu ke dashboard */
    public function finish(CompleteOnboarding $complete)
    {
        $complete->execute(auth()->user());

        return $this->redirectRoute('user.dashboard');
    }

    public function render()
    {
        return view('livewire.user.onboarding');
    }
}
