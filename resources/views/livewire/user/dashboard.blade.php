<div class="space-y-6">

    {{-- HERO: sapaan hangat di atas banner-grad --}}
    <div class="banner-grad rounded-2xl p-6 md:p-8 flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
        <div class="flex-1 space-y-1.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark">{{ __('Dashboard') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">
                {{ __('Halo') }}, {{ auth()->user()->name }}! 👋
            </h1>
            <p class="text-[15px] leading-relaxed text-ink/80">
                {{ __('Konsistensi adalah kunci. Kerjakan satu try out hari ini.') }}
            </p>
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat title="{{ __('Paket Aktif') }}" value="{{ $stats['active_packages'] }}">
            <x-slot:icon><x-lucide-package class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Try Out Dikerjakan') }}" value="{{ $stats['completed_attempts'] }}">
            <x-slot:icon><x-lucide-clipboard-check class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Total Percobaan') }}" value="{{ $stats['total_attempts'] }}">
            <x-slot:icon><x-lucide-history class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="{{ __('Saldo Komisi') }}" value="Rp{{ number_format($stats['wallet_balance'], 0, ',', '.') }}">
            <x-slot:icon><x-lucide-wallet class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- KOLOM KIRI: PERJALANAN BELAJAR --}}
        <div class="lg:col-span-2 space-y-4">
            <h2 class="font-extrabold tracking-tight text-lg text-secondary">{{ __('Perjalanan Belajarmu') }}</h2>

            @forelse ($this->journeys as $journey)
                <x-ui.card hover class="space-y-4" wire:key="journey-{{ $journey['package']->id }}">
                    <div class="flex items-center gap-4">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <x-lucide-map class="w-5 h-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-secondary truncate">{{ $journey['package']->name }}</p>
                            <p class="text-sm text-ink-muted">
                                <span class="font-mono font-extrabold tabular-nums">{{ $journey['completed'] }}/{{ $journey['total'] }}</span>
                                {{ __('materi & try out selesai') }}
                            </p>
                        </div>
                        <x-ui.button variant="secondary" :href="route('user.journey', $journey['package'])" class="!px-4 !py-2.5 shrink-0">
                            {{ $journey['completed'] > 0 ? __('Lanjutkan') : __('Mulai Belajar') }}
                            <x-lucide-arrow-right class="w-4 h-4" />
                        </x-ui.button>
                    </div>

                    {{-- Progress bar --}}
                    <div class="space-y-1.5">
                        <div class="h-2 rounded-full bg-surface-soft overflow-hidden">
                            <div class="h-full brand-grad rounded-full transition-all duration-500" style="width: {{ $journey['percent'] }}%"></div>
                        </div>
                        <p class="text-[11px] font-semibold text-ink-muted text-right">
                            <span class="font-mono tabular-nums">{{ $journey['percent'] }}%</span>
                        </p>
                    </div>
                </x-ui.card>
            @empty
                {{-- Empty state: belum berlangganan paket --}}
                <x-ui.card class="text-center py-10">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-surface-soft text-ink-faint flex items-center justify-center mb-4">
                        <x-lucide-package-open class="w-6 h-6" />
                    </div>
                    <p class="font-bold text-secondary">{{ __('Belum ada perjalanan belajar') }}</p>
                    <p class="text-sm text-ink-muted mt-1 mb-5">
                        {{ __('Berlangganan paket untuk membuka semua materi dan try out.') }}
                    </p>
                    <x-ui.button :href="route('user.packages')" class="mx-auto">
                        <x-lucide-sparkles class="w-4 h-4" /> {{ __('Lihat Paket') }}
                    </x-ui.button>
                </x-ui.card>
            @endforelse
        </div>

        {{-- KOLOM KANAN: PAKET AKTIF + KODE REFERRAL --}}
        <div class="space-y-4">
            <h2 class="font-extrabold tracking-tight text-lg text-secondary">{{ __('Paket Kamu') }}</h2>

            @forelse ($this->activeSubscriptions as $subscription)
                <x-ui.card class="space-y-2" wire:key="sub-{{ $subscription->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-bold text-secondary truncate">{{ $subscription->package->name }}</p>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-ok bg-ok-soft px-2.5 py-1 rounded-full shrink-0">
                            {{ __('Aktif') }}
                        </span>
                    </div>
                    <p class="text-sm text-ink-muted">
                        {{ __('Berlaku sampai') }} {{ $subscription->expires_at->translatedFormat('d M Y') }}
                    </p>
                </x-ui.card>
            @empty
                <x-ui.card>
                    <p class="text-sm text-ink-muted">{{ __('Belum ada paket aktif.') }}</p>
                </x-ui.card>
            @endforelse

            {{-- KARTU REFERRAL: salin kode via Alpine Clipboard API (tanpa server) --}}
            <div class="bg-secondary rounded-xl shadow-card p-5 text-white space-y-3"
                x-data="{
                    copied: false,
                    copy() {
                        navigator.clipboard.writeText('{{ auth()->user()->referral_code }}');
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                }">
                <div class="flex items-center gap-2">
                    <x-lucide-gift class="w-4 h-4 text-primary-light" />
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-white/60">
                        {{ __('Kode Referral Kamu') }}
                    </p>
                </div>
                <p class="text-sm text-white/70 leading-relaxed">
                    {{ __('Ajak temanmu dan dapatkan komisi setiap mereka berlangganan.') }}
                </p>
                <button type="button" @click="copy()"
                    class="w-full flex items-center justify-between gap-2 bg-secondary-light/40 hover:bg-secondary-light/60 rounded-xl px-4 py-3 transition-all cursor-pointer">
                    <span class="font-mono font-extrabold tracking-widest">{{ auth()->user()->referral_code }}</span>
                    <span x-show="!copied"><x-lucide-copy class="w-4 h-4 text-primary-light" /></span>
                    <span x-show="copied" x-cloak class="flex items-center gap-1 text-xs font-semibold text-primary-light">
                        <x-lucide-check class="w-4 h-4" /> {{ __('Tersalin!') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
