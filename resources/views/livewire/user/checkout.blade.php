<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="space-y-1">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Checkout') }}</p>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Konfirmasi Pesanan') }}</h1>
        <p class="text-[15px] leading-relaxed text-ink-muted">
            {{ __('Periksa detail pesananmu sebelum melanjutkan ke pembayaran.') }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">

        {{-- KIRI: DETAIL PESANAN --}}
        <div class="lg:col-span-3 space-y-5">
            <x-ui.card class="!p-6 space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl brand-grad text-white flex items-center justify-center shrink-0">
                        <x-lucide-package class="w-6 h-6" />
                    </div>
                    <div class="min-w-0 space-y-1">
                        <h2 class="text-lg font-extrabold tracking-tight text-secondary">{{ $plan->package->name }}</h2>
                        @if ($plan->package->description)
                            <p class="text-sm leading-relaxed text-ink-muted">{{ $plan->package->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-surface-tint text-center">
                        <p class="font-mono font-extrabold tabular-nums text-secondary">{{ $plan->name }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint mt-1">{{ __('Durasi') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-tint text-center">
                        <p class="font-mono font-extrabold tabular-nums text-secondary">{{ $plan->duration_days }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint mt-1">{{ __('Hari Akses') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-tint text-center">
                        <p class="font-mono font-extrabold tabular-nums text-secondary">{{ $plan->package->quizzes_count }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint mt-1">{{ __('Try Out') }}</p>
                    </div>
                </div>
            </x-ui.card>

            {{-- LANGKAH SETELAH INI --}}
            <x-ui.card class="!p-6">
                <h3 class="font-extrabold tracking-tight text-secondary mb-4">{{ __('Setelah ini apa?') }}</h3>
                <ol class="space-y-3">
                    @foreach ([
                        __('Kamu diarahkan ke halaman pembayaran DOKU — pilih metode yang kamu mau (VA, e-wallet, kartu, dll).'),
                        __('Setelah pembayaran terkonfirmasi, paket aktif otomatis — tidak perlu konfirmasi manual.'),
                        __('Email tanda terima terkirim, dan semua try out dalam paket langsung terbuka.'),
                    ] as $i => $step)
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full brand-grad text-white flex items-center justify-center text-xs font-mono font-extrabold shrink-0">{{ $i + 1 }}</span>
                            <p class="text-sm leading-relaxed text-ink-muted">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>
            </x-ui.card>
        </div>

        {{-- KANAN: RINGKASAN PEMBAYARAN --}}
        <div class="lg:col-span-2 space-y-5">
            <x-ui.card class="!p-6 space-y-4">
                <h3 class="font-extrabold tracking-tight text-secondary">{{ __('Ringkasan Pembayaran') }}</h3>

                <div class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-ink-muted min-w-0 truncate">{{ $plan->package->name }} · {{ $plan->name }}</p>
                        <p class="font-mono font-bold tabular-nums text-secondary shrink-0">Rp{{ number_format($plan->price, 0, ',', '.') }}</p>
                    </div>
                    <div class="border-t border-black/5 pt-2.5 flex items-center justify-between gap-3">
                        <p class="font-bold text-secondary">{{ __('Total') }}</p>
                        <p class="font-mono font-extrabold tabular-nums text-primary text-xl shrink-0">
                            Rp{{ number_format($plan->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-surface-tint text-sm leading-tight">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint mb-1">{{ __('Ditagihkan kepada') }}</p>
                    <p class="font-semibold text-secondary truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
                </div>

                @if ($this->pendingPayment)
                    <div class="flex items-start gap-2.5 p-3 rounded-xl banner-grad">
                        <x-lucide-info class="w-4 h-4 text-primary-dark shrink-0 mt-0.5" />
                        <p class="text-xs leading-relaxed text-ink/90">
                            {{ __('Kamu punya tagihan tertunda untuk paket ini') }}
                            (<span class="font-mono font-bold">{{ $this->pendingPayment->external_id }}</span>).
                            {{ __('Melanjutkan akan memakai invoice yang sama — tidak ada tagihan ganda.') }}
                        </p>
                    </div>
                @endif

                <x-ui.button class="w-full !py-3.5" wire:click="pay" wire:loading.attr="disabled" wire:target="pay">
                    <span wire:loading.remove wire:target="pay" class="flex items-center gap-2">
                        <x-lucide-lock class="w-4 h-4" /> {{ __('Lanjutkan ke Pembayaran') }}
                    </span>
                    <span wire:loading wire:target="pay">{{ __('Memproses...') }}</span>
                </x-ui.button>

                <p class="text-[11px] text-ink-faint text-center leading-relaxed">
                    {{ __('Pembayaran diproses aman oleh DOKU. Link bayar berlaku 60 menit.') }}
                </p>
            </x-ui.card>

            <a href="{{ route('user.packages') }}"
                class="block text-center text-sm font-semibold text-ink-muted hover:text-primary transition-colors">
                &larr; {{ __('Kembali ke Paket Belajar') }}
            </a>
        </div>
    </div>
</div>
