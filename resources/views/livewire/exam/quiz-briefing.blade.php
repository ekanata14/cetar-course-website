<div class="max-w-3xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="space-y-1">
        <a href="{{ $this->backPackage ? route('user.journey', $this->backPackage) : route('user.dashboard') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-muted hover:text-primary transition-colors">
            <x-lucide-arrow-left class="w-3.5 h-3.5" /> {{ __('Kembali ke Roadmap') }}
        </a>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark">{{ __('Persiapan Ujian') }}</p>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ $quiz->title }}</h1>
        @if ($quiz->description)
            <p class="text-sm leading-relaxed text-ink-muted">{{ $quiz->description }}</p>
        @endif
    </div>

    {{-- RINGKASAN UJIAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-ui.card class="text-center space-y-1">
            <x-lucide-file-text class="w-5 h-5 mx-auto text-primary" />
            <p class="font-mono font-extrabold tabular-nums text-2xl text-secondary">{{ $quiz->questions_count }}</p>
            <p class="text-xs text-ink-muted">{{ __('Soal') }}</p>
        </x-ui.card>
        <x-ui.card class="text-center space-y-1">
            <x-lucide-timer class="w-5 h-5 mx-auto text-primary" />
            <p class="font-mono font-extrabold tabular-nums text-2xl text-secondary">{{ $quiz->duration_minutes }}</p>
            <p class="text-xs text-ink-muted">{{ __('Menit') }}</p>
        </x-ui.card>
        <x-ui.card class="text-center space-y-1">
            <x-lucide-target class="w-5 h-5 mx-auto text-primary" />
            <p class="font-mono font-extrabold tabular-nums text-2xl text-secondary">{{ $this->stats['total_points'] }}</p>
            <p class="text-xs text-ink-muted">{{ __('Total Poin') }}</p>
        </x-ui.card>
    </div>

    {{-- KOMPOSISI SECTION --}}
    @if ($this->stats['sections']->isNotEmpty())
        <x-ui.card class="space-y-3">
            <p class="font-bold text-secondary">{{ __('Komposisi Soal') }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($this->stats['sections'] as $section => $total)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-secondary bg-surface-tint px-3 py-1.5 rounded-full">
                        {{ $section }}
                        <span class="font-mono font-extrabold tabular-nums text-primary-dark">{{ $total }}</span>
                    </span>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    {{-- PERATURAN UJIAN --}}
    <x-ui.card class="space-y-4">
        <div class="flex items-center gap-2">
            <x-lucide-scroll-text class="w-5 h-5 text-primary" />
            <p class="font-bold text-secondary">{{ __('Peraturan Ujian') }}</p>
        </div>

        <ol class="space-y-3">
            @foreach ([
                __('Timer berjalan terus sejak ujian dimulai dan tidak bisa dijeda. Me-refresh atau menutup halaman TIDAK mereset waktu.'),
                __('Jawabanmu tersimpan otomatis setiap kali memilih opsi — tidak perlu menekan tombol simpan.'),
                __('Gunakan tanda "Ragu-ragu" untuk menandai soal yang ingin kamu tinjau kembali sebelum waktu habis.'),
                __('Saat waktu habis, ujian otomatis dikumpulkan dengan jawaban yang sudah tersimpan.'),
                __('Jangan berpindah halaman atau menutup tab selama ujian berlangsung agar pengerjaanmu tidak terganggu.'),
                __('Pastikan koneksi internet stabil sebelum memulai.'),
                __('Kerjakan dengan jujur seperti ujian sesungguhnya — hasilnya adalah cermin kesiapanmu.'),
            ] as $rule)
                <li class="flex gap-3">
                    <span class="w-6 h-6 rounded-full bg-primary/10 text-primary-dark flex items-center justify-center text-xs font-extrabold shrink-0">
                        {{ $loop->iteration }}
                    </span>
                    <p class="text-sm leading-relaxed text-ink flex-1 pt-0.5">{{ $rule }}</p>
                </li>
            @endforeach
        </ol>
    </x-ui.card>

    {{-- NOTICE SESI BERJALAN --}}
    @if ($this->inProgressAttempt)
        <div class="flex items-start gap-3 rounded-2xl bg-warning/10 border border-warning/30 p-4">
            <x-lucide-alarm-clock class="w-5 h-5 text-warning shrink-0 mt-0.5" />
            <div class="text-sm leading-relaxed text-ink">
                <p class="font-bold text-secondary">{{ __('Kamu punya sesi yang sedang berjalan!') }}</p>
                <p>
                    {{ __('Timer sudah menyala sejak') }}
                    {{ $this->inProgressAttempt->started_at->translatedFormat('H:i') }} —
                    {{ __('batas pengumpulan') }}
                    <span class="font-mono font-extrabold tabular-nums">{{ $this->inProgressAttempt->deadline()->translatedFormat('H:i') }}</span>.
                    {{ __('Lanjutkan sekarang agar tidak kehabisan waktu.') }}
                </p>
            </div>
        </div>
    @endif

    {{-- CTA MULAI --}}
    <x-ui.card class="text-center space-y-3 !py-8">
        <x-ui.button wire:click="start" wire:loading.attr="disabled" class="mx-auto !px-8 !py-3.5 text-base">
            <span wire:loading.remove wire:target="start" class="flex items-center gap-2">
                <x-lucide-play class="w-5 h-5" />
                {{ $this->inProgressAttempt ? __('Lanjutkan Ujian') : __('Mulai Ujian') }}
            </span>
            <span wire:loading wire:target="start">{{ __('Menyiapkan...') }}</span>
        </x-ui.button>
        <p class="text-xs text-ink-muted">
            @if ($this->inProgressAttempt)
                {{ __('Kamu akan kembali ke sesi yang sedang berjalan — timer tidak direset.') }}
            @else
                {{ __('Timer dimulai saat kamu menekan tombol ini.') }}
            @endif
        </p>
    </x-ui.card>
</div>
