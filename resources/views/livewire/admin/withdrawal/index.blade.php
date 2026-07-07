<div class="space-y-6">

    {{-- HEADER --}}
    <div class="space-y-1">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Penarikan Saldo') }}</h1>
    </div>

    {{-- ANTRIAN PENDING --}}
    <div class="space-y-3">
        <h2 class="font-extrabold tracking-tight text-lg text-secondary">
            {{ __('Menunggu Persetujuan') }}
            <span class="font-mono tabular-nums text-primary-dark">({{ $this->pending->count() }})</span>
        </h2>

        @forelse ($this->pending as $withdrawal)
            <x-ui.card class="flex flex-col md:flex-row md:items-center gap-4" wire:key="pending-{{ $withdrawal->id }}">
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-bold text-secondary">{{ $withdrawal->user->name }}</p>
                        <span class="text-xs text-ink-muted">{{ $withdrawal->user->email }}</span>
                    </div>
                    <p class="text-sm text-ink-muted">
                        {{ $withdrawal->bank_details['bank_name'] ?? '-' }} ·
                        <span class="font-mono">{{ $withdrawal->bank_details['account_number'] ?? '-' }}</span> ·
                        a.n. {{ $withdrawal->bank_details['account_name'] ?? '-' }}
                    </p>
                    <p class="text-xs text-ink-faint">{{ __('Diajukan') }} {{ $withdrawal->created_at->diffForHumans() }}</p>
                </div>

                <p class="font-mono font-extrabold tabular-nums text-xl text-secondary shrink-0">
                    Rp{{ number_format($withdrawal->amount, 0, ',', '.') }}
                </p>

                <div class="flex items-center gap-2 shrink-0">
                    <x-ui.button wire:click="approve({{ $withdrawal->id }})"
                        wire:confirm="{{ __('Setujui penarikan ini? Pastikan kamu sudah/akan mentransfer dananya.') }}"
                        class="!px-4 !py-2.5">
                        <x-lucide-check class="w-4 h-4" /> {{ __('Setujui') }}
                    </x-ui.button>
                    <button type="button" wire:click="reject({{ $withdrawal->id }})"
                        wire:confirm="{{ __('Tolak penarikan ini? Saldo akan dikembalikan ke user.') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-bad bg-bad-soft hover:bg-bad hover:text-white transition-all cursor-pointer">
                        <x-lucide-x class="w-4 h-4" /> {{ __('Tolak') }}
                    </button>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="text-center py-8">
                <p class="text-sm text-ink-muted">{{ __('Tidak ada pengajuan yang menunggu. 🎉') }}</p>
            </x-ui.card>
        @endforelse
    </div>

    {{-- RIWAYAT DIPROSES --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="px-5 py-4">
            <h2 class="font-extrabold tracking-tight text-secondary">{{ __('Riwayat Diproses') }}</h2>
        </div>
        <div class="divide-y divide-black/5">
            @forelse ($history as $withdrawal)
                @php
                    $isSuccess = $withdrawal->status->value === 'success';
                @endphp
                <div class="px-5 py-3.5 flex items-center gap-3" wire:key="history-{{ $withdrawal->id }}">
                    <div class="flex-1 min-w-0 leading-tight">
                        <p class="text-sm font-semibold text-secondary truncate">{{ $withdrawal->user->name }}</p>
                        <p class="text-xs text-ink-muted">
                            {{ __('Diproses oleh') }} {{ $withdrawal->processedBy?->name ?? '-' }} ·
                            {{ $withdrawal->updated_at->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                    <p class="font-mono font-extrabold tabular-nums text-sm text-secondary shrink-0">
                        Rp{{ number_format($withdrawal->amount, 0, ',', '.') }}
                    </p>
                    <span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full shrink-0
                        {{ $isSuccess ? 'text-ok bg-ok-soft' : 'text-bad bg-bad-soft' }}">
                        {{ $withdrawal->status->value }}
                    </span>
                </div>
            @empty
                <p class="px-5 py-8 text-sm text-ink-muted text-center">{{ __('Belum ada riwayat.') }}</p>
            @endforelse
        </div>

        @if ($history->hasPages())
            <div class="px-5 py-4 border-t border-black/5">
                {{ $history->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
