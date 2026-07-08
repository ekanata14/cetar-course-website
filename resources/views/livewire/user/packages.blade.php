<div class="space-y-6">

    {{-- HEADER --}}
    <div class="space-y-1">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Katalog') }}</p>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Paket Belajar') }}</h1>
        <p class="text-[15px] leading-relaxed text-ink-muted">
            {{ __('Pilih paket sesuai tujuanmu — semua try out & materi di dalamnya langsung terbuka.') }}
        </p>
    </div>

    {{-- BANNER: user baru kembali dari halaman pembayaran DOKU --}}
    @if ($status === 'payment-return')
        <div class="flex items-start gap-3 p-4 rounded-xl banner-grad">
            <x-lucide-clock class="w-5 h-5 text-primary-dark shrink-0 mt-0.5" />
            <div class="leading-snug">
                <p class="text-sm font-bold text-secondary">{{ __('Pembayaran kamu sedang diproses') }}</p>
                <p class="text-sm text-ink-muted">
                    {{ __('Paket akan aktif otomatis setelah pembayaran terkonfirmasi — kamu juga akan menerima email tanda terima.') }}
                    <a href="{{ route('user.transactions') }}" class="font-semibold text-primary hover:text-primary-dark transition-colors">
                        {{ __('Lihat status transaksi') }} &rarr;
                    </a>
                </p>
            </div>
        </div>
    @endif

    {{-- KATALOG PAKET --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ($this->packages as $package)
            @php
                $isOwned = in_array($package->id, $this->activePackageIds);
            @endphp
            <x-ui.card hover class="!p-6 space-y-4 flex flex-col" wire:key="package-{{ $package->id }}">

                <div class="flex items-start justify-between gap-3">
                    <div class="w-11 h-11 rounded-xl brand-grad text-white flex items-center justify-center shrink-0">
                        <x-lucide-package class="w-5 h-5" />
                    </div>
                    @if ($isOwned)
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-ok bg-ok-soft px-2.5 py-1 rounded-full">
                            {{ __('Aktif') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-1.5">
                    <h2 class="text-lg font-extrabold tracking-tight text-secondary">{{ $package->name }}</h2>
                    @if ($package->description)
                        <p class="text-sm leading-relaxed text-ink-muted">{{ $package->description }}</p>
                    @endif
                    <p class="text-xs text-ink-faint">
                        <span class="font-mono font-extrabold tabular-nums">{{ $package->quizzes_count }}</span> {{ __('try out tersedia') }}
                    </p>
                </div>

                {{-- TIER HARGA --}}
                <div class="space-y-2.5 mt-auto pt-2">
                    @foreach ($package->plans as $plan)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-tint" wire:key="plan-{{ $plan->id }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-secondary">{{ $plan->name }}</p>
                                <p class="text-xs text-ink-muted">{{ $plan->duration_days }} {{ __('hari akses') }}</p>
                            </div>
                            <p class="font-mono font-extrabold tabular-nums text-secondary shrink-0">
                                Rp{{ number_format($plan->price, 0, ',', '.') }}
                            </p>
                            <x-ui.button :href="route('user.checkout', $plan)" class="!px-4 !py-2 shrink-0">
                                {{ $isOwned ? __('Perpanjang') : __('Beli') }}
                            </x-ui.button>
                        </div>
                    @endforeach
                </div>

                {{-- PAKET DIMILIKI: masuk ke roadmap belajar --}}
                @if ($isOwned)
                    <x-ui.button variant="secondary" :href="route('user.journey', $package)" class="w-full justify-center">
                        <x-lucide-map class="w-4 h-4" /> {{ __('Mulai Belajar') }}
                    </x-ui.button>
                @endif
            </x-ui.card>
        @endforeach
    </div>

    {{-- LINK RIWAYAT TRANSAKSI --}}
    <x-ui.card class="!p-5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-surface-tint text-secondary flex items-center justify-center shrink-0">
                <x-lucide-receipt-text class="w-5 h-5" />
            </div>
            <div class="leading-tight min-w-0">
                <p class="font-bold text-secondary">{{ __('Riwayat Transaksi') }}</p>
                <p class="text-sm text-ink-muted truncate">{{ __('Lihat semua pembayaran dan unduh invoice-mu.') }}</p>
            </div>
        </div>
        <x-ui.button variant="ghost" :href="route('user.transactions')" class="shrink-0">
            {{ __('Lihat semua') }} &rarr;
        </x-ui.button>
    </x-ui.card>
</div>
