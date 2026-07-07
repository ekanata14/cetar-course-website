<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div class="space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Pembayaran') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Riwayat Transaksi') }}</h1>
            <p class="text-[15px] leading-relaxed text-ink-muted">
                {{ __('Semua tagihan dan pembayaranmu — unduh invoice kapan saja.') }}
            </p>
        </div>

        {{-- FILTER STATUS --}}
        <div class="w-full md:w-52">
            <x-ui.select wire:model.live="filter" name="filter">
                <option value="">{{ __('Semua Status') }}</option>
                <option value="pending">{{ __('Menunggu Pembayaran') }}</option>
                <option value="settled">{{ __('Lunas') }}</option>
                <option value="failed">{{ __('Gagal') }}</option>
                <option value="expired">{{ __('Kedaluwarsa') }}</option>
            </x-ui.select>
        </div>
    </div>

    {{-- DAFTAR TRANSAKSI --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="divide-y divide-black/5">
            @forelse ($this->payments as $payment)
                @php
                    $statusStyle = match ($payment->status->value) {
                        'settled' => 'text-ok bg-ok-soft',
                        'pending' => 'text-warn-dark bg-warn-soft',
                        default => 'text-bad bg-bad-soft',
                    };
                    $statusLabel = match ($payment->status->value) {
                        'settled' => __('Lunas'),
                        'pending' => __('Pending'),
                        'failed' => __('Gagal'),
                        default => __('Kedaluwarsa'),
                    };
                @endphp
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3" wire:key="payment-{{ $payment->id }}">
                    <div class="flex-1 min-w-0 leading-tight">
                        <p class="text-sm font-bold text-secondary truncate">
                            {{ $payment->packagePlan->package->name }} · {{ $payment->packagePlan->name }}
                        </p>
                        <p class="text-xs text-ink-muted font-mono truncate mt-0.5">{{ $payment->external_id }}</p>
                        <p class="text-xs text-ink-faint mt-0.5">{{ $payment->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <p class="font-mono font-extrabold tabular-nums text-secondary text-sm">
                            Rp{{ number_format($payment->amount, 0, ',', '.') }}
                        </p>
                        <span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full {{ $statusStyle }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 sm:ms-2">
                        @if ($payment->status->value === 'pending' && $payment->payment_url)
                            <a href="{{ $payment->payment_url }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg brand-grad text-white text-xs font-bold hover:-translate-y-0.5 transition-all">
                                {{ __('Bayar') }}
                            </a>
                        @endif
                        <a href="{{ route('user.invoice.download', $payment) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-soft text-secondary text-xs font-bold hover:bg-surface-tint transition-colors">
                            <x-lucide-download class="w-3.5 h-3.5" /> {{ __('Invoice') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-14 px-5">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-surface-soft text-ink-faint flex items-center justify-center mb-4">
                        <x-lucide-receipt-text class="w-6 h-6" />
                    </div>
                    <p class="font-bold text-secondary">{{ __('Belum ada transaksi') }}</p>
                    <p class="text-sm text-ink-muted mt-1 mb-5">
                        {{ $filter !== '' ? __('Tidak ada transaksi dengan status ini.') : __('Mulai dengan memilih paket belajarmu.') }}
                    </p>
                    @if ($filter === '')
                        <x-ui.button :href="route('user.packages')" class="mx-auto">
                            <x-lucide-sparkles class="w-4 h-4" /> {{ __('Lihat Paket') }}
                        </x-ui.button>
                    @endif
                </div>
            @endforelse
        </div>
    </x-ui.card>

    {{-- PAGINATION --}}
    @if ($this->payments->hasPages())
        <div>{{ $this->payments->links() }}</div>
    @endif
</div>
