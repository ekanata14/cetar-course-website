<?php

namespace App\Livewire\User;

use App\Models\Quiz;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function mount()
    {
        // Jaring pengaman: user baru dari jalur mana pun (termasuk POST /register
        // milik Fortify) tetap diarahkan ke onboarding
        if (! auth()->user()->hasOnboarded()) {
            return $this->redirectRoute('user.onboarding');
        }
    }

    /** Langganan paket yang masih aktif (status active & belum expired) */
    #[Computed]
    public function activeSubscriptions()
    {
        return auth()->user()
            ->subscriptions()
            ->active()
            ->with('package')
            ->get();
    }

    /** Kuis yang bisa diakses user dari seluruh paket aktifnya (via pivot package_content) */
    #[Computed]
    public function availableQuizzes()
    {
        $packageIds = $this->activeSubscriptions->pluck('package_id');

        return Quiz::query()
            ->whereHas('packages', fn ($q) => $q->whereIn('packages.id', $packageIds))
            ->withCount('questions')
            ->latest()
            ->get();
    }

    public function render()
    {
        $user = auth()->user();

        // Statistik ringkas untuk stat cards
        $stats = [
            'active_packages' => $this->activeSubscriptions->count(),
            'total_attempts' => $user->quizAttempts()->count(),
            'completed_attempts' => $user->quizAttempts()->whereNotNull('completed_at')->count(),
            'wallet_balance' => $user->wallet_balance,
        ];

        return view('livewire.user.dashboard', [
            'stats' => $stats,
        ]);
    }
}
