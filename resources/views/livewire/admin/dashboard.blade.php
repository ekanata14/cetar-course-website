<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Dashboard') }}</h1>
        </div>
        <x-ui.button :href="route('admin.users')">
            <x-lucide-user-plus class="w-4 h-4" /> {{ __('Kelola Users') }}
        </x-ui.button>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat title="{{ __('Total Users') }}" value="{{ number_format($stats['total_users'], 0, ',', '.') }}">
            <x-slot:icon><x-lucide-users class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Paket Aktif') }}" value="{{ $stats['active_packages'] }}">
            <x-slot:icon><x-lucide-package class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Total Kuis') }}" value="{{ $stats['total_quizzes'] }}">
            <x-slot:icon><x-lucide-file-text class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Pendapatan') }}" value="Rp{{ number_format($stats['revenue'], 0, ',', '.') }}">
            <x-slot:icon><x-lucide-banknote class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- USER TERBARU --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between">
            <h2 class="font-extrabold tracking-tight text-secondary">{{ __('User Terbaru') }}</h2>
            <a href="{{ route('admin.users') }}"
                class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
                {{ __('Lihat semua') }}
            </a>
        </div>

        <div class="divide-y divide-black/5">
            @forelse ($recentUsers as $user)
                <div class="px-5 py-3.5 flex items-center gap-3" wire:key="user-{{ $user->id }}">
                    <div class="w-9 h-9 rounded-full brand-grad text-white flex items-center justify-center text-xs font-extrabold shrink-0">
                        {{ $user->initials() }}
                    </div>
                    <div class="flex-1 min-w-0 leading-tight">
                        <p class="text-sm font-semibold text-secondary truncate">{{ $user->name }}</p>
                        <p class="text-xs text-ink-muted truncate">{{ $user->email }}</p>
                    </div>
                    <span class="hidden sm:inline font-mono text-xs text-ink-faint">{{ $user->referral_code }}</span>
                    <span class="text-xs text-ink-muted shrink-0">{{ $user->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="px-5 py-8 text-sm text-ink-muted text-center">{{ __('Belum ada user terdaftar.') }}</p>
            @endforelse
        </div>
    </x-ui.card>
</div>
