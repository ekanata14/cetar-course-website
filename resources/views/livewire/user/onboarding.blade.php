<div class="max-w-4xl mx-auto space-y-6" x-data="{ step: 1, slide: 1 }">

    {{-- HEADER + LEWATI --}}
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Onboarding') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">
                {{ __('Selamat datang di BIMBEL CETAR') }}, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-[15px] leading-relaxed text-ink-muted">
                {{ __('Kenali cara kerja platform, lalu pilih paket belajarmu.') }}
            </p>
        </div>
        <button type="button" wire:click="finish" wire:loading.attr="disabled"
            class="text-sm font-semibold text-ink-muted hover:text-primary transition-colors shrink-0 cursor-pointer">
            {{ __('Lewati') }} &rarr;
        </button>
    </div>

    {{-- STEP INDICATOR --}}
    <div class="flex items-center gap-3">
        <button type="button" @click="step = 1"
            class="flex items-center gap-2 text-sm font-bold transition-colors cursor-pointer"
            :class="step === 1 ? 'text-primary' : 'text-ink-faint'">
            <span class="w-7 h-7 rounded-full flex items-center justify-center font-mono text-xs"
                :class="step === 1 ? 'brand-grad text-white' : 'bg-surface-soft text-ink-muted'">1</span>
            {{ __('Cara Kerja') }}
        </button>
        <div class="flex-1 h-px bg-black/10"></div>
        <button type="button" @click="step = 2"
            class="flex items-center gap-2 text-sm font-bold transition-colors cursor-pointer"
            :class="step === 2 ? 'text-primary' : 'text-ink-faint'">
            <span class="w-7 h-7 rounded-full flex items-center justify-center font-mono text-xs"
                :class="step === 2 ? 'brand-grad text-white' : 'bg-surface-soft text-ink-muted'">2</span>
            {{ __('Pilih Paket') }}
        </button>
    </div>

    {{-- ============ STEP 1: TUTORIAL ============ --}}
    <div x-show="step === 1" x-transition.opacity>
        <x-ui.card class="!p-8">
            @php
                $slides = [
                    [
                        'icon' => 'layout-dashboard',
                        'title' => __('Dashboard: pusat kendalimu'),
                        'desc' => __('Semua paket aktif, try out yang tersedia, dan progres latihanmu tampil di satu tempat. Mulai dari sini setiap kali login.'),
                    ],
                    [
                        'icon' => 'package',
                        'title' => __('Beli paket sesuai tujuanmu'),
                        'desc' => __('Pilih paket CPNS atau SNBT dengan tier bulanan atau tahunan. Pembayaran aman lewat DOKU, dan paket aktif otomatis setelah pembayaran terkonfirmasi.'),
                    ],
                    [
                        'icon' => 'timer',
                        'title' => __('Kerjakan try out layaknya ujian asli'),
                        'desc' => __('Timer berjalan otomatis, jawaban tersimpan sendiri, dan kamu bisa menandai soal ragu-ragu lewat grid navigasi. Saat waktu habis, ujian terkumpul otomatis.'),
                    ],
                    [
                        'icon' => 'chart-column',
                        'title' => __('Pelajari hasil & pembahasan'),
                        'desc' => __('Skor langsung keluar lengkap dengan rincian per seksi (benar, salah, kosong) dan pembahasan setiap soal — belajar dari kesalahan jadi lebih cepat.'),
                    ],
                ];
            @endphp

            @foreach ($slides as $i => $s)
                <div x-show="slide === {{ $i + 1 }}" x-transition.opacity class="text-center py-6">
                    <div class="w-16 h-16 mx-auto rounded-2xl brand-grad text-white flex items-center justify-center mb-6">
                        <x-dynamic-component :component="'lucide-' . $s['icon']" class="w-8 h-8" />
                    </div>
                    <h2 class="text-xl font-extrabold tracking-tight text-secondary mb-3">{{ $s['title'] }}</h2>
                    <p class="text-[15px] leading-relaxed text-ink-muted max-w-lg mx-auto">{{ $s['desc'] }}</p>
                </div>
            @endforeach

            {{-- NAVIGASI SLIDE --}}
            <div class="flex items-center justify-between mt-4">
                <button type="button" @click="slide = Math.max(1, slide - 1)" x-show="slide > 1"
                    class="text-sm font-semibold text-ink-muted hover:text-secondary transition-colors cursor-pointer">
                    &larr; {{ __('Sebelumnya') }}
                </button>
                <span x-show="slide === 1"></span>

                <div class="flex items-center gap-1.5">
                    @foreach ($slides as $i => $s)
                        <button type="button" @click="slide = {{ $i + 1 }}"
                            class="w-2 h-2 rounded-full transition-colors cursor-pointer"
                            :class="slide === {{ $i + 1 }} ? 'bg-primary' : 'bg-black/10'"></button>
                    @endforeach
                </div>

                <x-ui.button x-show="slide < {{ count($slides) }}" @click="slide = slide + 1" class="!px-5 !py-2">
                    {{ __('Lanjut') }} &rarr;
                </x-ui.button>
                <x-ui.button x-show="slide === {{ count($slides) }}" @click="step = 2" class="!px-5 !py-2">
                    {{ __('Pilih Paket') }} &rarr;
                </x-ui.button>
            </div>
        </x-ui.card>
    </div>

    {{-- ============ STEP 2: PILIH PAKET ============ --}}
    <div x-show="step === 2" x-transition.opacity x-cloak class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse ($this->packages as $package)
                <x-ui.card hover class="!p-6 space-y-4 flex flex-col" wire:key="ob-package-{{ $package->id }}">

                    <div class="w-11 h-11 rounded-xl brand-grad text-white flex items-center justify-center shrink-0">
                        <x-lucide-package class="w-5 h-5" />
                    </div>

                    <div class="space-y-1.5">
                        <h2 class="text-lg font-extrabold tracking-tight text-secondary">{{ $package->name }}</h2>
                        @if ($package->description)
                            <p class="text-sm leading-relaxed text-ink-muted">{{ $package->description }}</p>
                        @endif
                        <p class="text-xs text-ink-faint">
                            <span class="font-mono font-extrabold tabular-nums">{{ $package->quizzes_count }}</span>
                            {{ __('try out tersedia') }}
                        </p>
                    </div>

                    {{-- TIER HARGA (bulanan & tahunan) --}}
                    <div class="space-y-2.5 mt-auto pt-2">
                        @foreach ($package->plans as $plan)
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-tint" wire:key="ob-plan-{{ $plan->id }}">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-secondary">{{ $plan->name }}</p>
                                    <p class="text-xs text-ink-muted">{{ $plan->duration_days }} {{ __('hari akses') }}</p>
                                </div>
                                <p class="font-mono font-extrabold tabular-nums text-secondary shrink-0">
                                    Rp{{ number_format($plan->price, 0, ',', '.') }}
                                </p>
                                <x-ui.button class="!px-4 !py-2 shrink-0" wire:click="checkout({{ $plan->id }})"
                                    wire:loading.attr="disabled" wire:target="checkout({{ $plan->id }})">
                                    {{ __('Beli') }}
                                </x-ui.button>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card class="md:col-span-2 text-center py-10">
                    <p class="font-bold text-secondary">{{ __('Belum ada paket tersedia') }}</p>
                    <p class="text-sm text-ink-muted mt-1">{{ __('Lanjut ke dashboard dulu — paket akan segera hadir.') }}</p>
                </x-ui.card>
            @endforelse
        </div>

        <div class="text-center">
            <button type="button" wire:click="finish" wire:loading.attr="disabled"
                class="text-sm font-semibold text-ink-muted hover:text-primary transition-colors cursor-pointer">
                {{ __('Nanti saja, langsung ke dashboard') }} &rarr;
            </button>
        </div>
    </div>
</div>
