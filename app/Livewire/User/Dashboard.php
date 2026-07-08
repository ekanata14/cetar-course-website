<?php

namespace App\Livewire\User;

use App\Actions\Roadmap\ResolveJourney;
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

    /**
     * Perjalanan belajar per paket aktif: progres roadmap + item berikutnya.
     *
     * @return array<int, array{package: \App\Models\Package, total: int, completed: int, percent: int}>
     */
    #[Computed]
    public function journeys(): array
    {
        $resolver = app(ResolveJourney::class);
        $user = auth()->user();

        return $this->activeSubscriptions
            ->map(function ($subscription) use ($resolver, $user) {
                $items = $resolver->execute($user, $subscription->package)
                    ->flatMap(fn ($module) => $module->items);

                $total = $items->count();
                $completed = $items->where('is_completed', true)->count();

                return [
                    'package' => $subscription->package,
                    'total' => $total,
                    'completed' => $completed,
                    'percent' => $total > 0 ? (int) round($completed / $total * 100) : 0,
                ];
            })
            ->all();
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
