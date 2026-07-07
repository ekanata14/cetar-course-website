{{--
    CBT ENGINE — halaman ujian full-screen.
    Livewire: navigasi soal, auto-save jawaban, submit.
    Alpine: countdown timer murni client-side dari deadline absolut (epoch ms) —
            nol server round-trip untuk detik berjalan; auto-submit saat habis.
--}}
<div class="min-h-screen flex flex-col"
    x-data="{
        deadline: {{ $this->deadlineMs }},
        remaining: 0,
        expired: false,
        tick() {
            this.remaining = Math.max(0, this.deadline - Date.now());
            // Auto-submit sekali saat waktu habis (guard server tetap memvalidasi)
            if (!this.expired && this.remaining <= 0) {
                this.expired = true;
                $wire.submitQuiz();
            }
        },
        get danger() { return this.remaining < 5 * 60 * 1000 }, // < 5 menit: pulse merah
        get display() {
            const total = Math.ceil(this.remaining / 1000);
            const h = Math.floor(total / 3600), m = Math.floor((total % 3600) / 60), s = total % 60;
            const pad = (n) => String(n).padStart(2, '0');
            return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
        }
    }"
    x-init="tick(); setInterval(() => tick(), 500)">

    {{-- ================= TOPBAR STICKY: judul + timer ================= --}}
    <header class="sticky top-0 z-30 bg-surface shadow-card px-4 md:px-8 py-3 flex items-center gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Sedang Ujian') }}</p>
            <h1 class="font-extrabold tracking-tight text-secondary truncate">{{ $quiz->title }}</h1>
        </div>

        {{-- TIMER: pill navy, font mono; ring merah berdenyut di bawah 5 menit --}}
        <div class="flex items-center gap-2 px-4 py-2 bg-secondary text-white rounded-full shadow-card transition-all"
            :class="danger ? 'ring-2 ring-bad animate-pulse' : ''">
            <x-lucide-timer class="w-4 h-4" ::class="danger ? 'text-bad' : 'text-primary-light'" />
            <span x-text="display" class="font-mono font-extrabold tabular-nums text-lg tracking-wider"></span>
        </div>
    </header>

    {{-- ================= KONTEN ================= --}}
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- -------- KARTU SOAL -------- --}}
        <div class="lg:col-span-8 space-y-4">
            @if ($this->currentQuestion)
                <x-ui.card class="!p-6 md:!p-8 space-y-5">

                    {{-- Meta soal --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-secondary">
                            {{ __('Soal') }}
                            <span class="font-mono font-extrabold tabular-nums">{{ $currentIndex + 1 }}</span>
                            / {{ $this->questions->count() }}
                        </span>
                        @if ($this->currentQuestion->section)
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark bg-primary/10 px-2 py-0.5 rounded-full">
                                {{ $this->currentQuestion->section }}
                            </span>
                        @endif
                    </div>

                    {{-- Passage / teks bacaan --}}
                    @if ($this->currentQuestion->passage)
                        <div class="p-4 rounded-xl bg-surface-tint text-[15px] leading-relaxed text-ink/90 whitespace-pre-line">
                            {{ $this->currentQuestion->passage }}
                        </div>
                    @endif

                    {{-- Pertanyaan --}}
                    <p class="text-[15px] md:text-base leading-relaxed text-ink font-medium whitespace-pre-line">{{ $this->currentQuestion->text }}</p>

                    {{-- Opsi jawaban: terpilih -> brand-grad + teks putih (design_system.md) --}}
                    <div class="space-y-2.5">
                        @foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $letter => $column)
                            @continue($this->currentQuestion->{$column} === null)
                            @php
                                $isSelected = ($answers[$this->currentQuestion->id]['selected'] ?? null) === $letter;
                            @endphp
                            <button type="button" wire:key="opt-{{ $this->currentQuestion->id }}-{{ $letter }}"
                                wire:click="selectAnswer({{ $this->currentQuestion->id }}, '{{ $letter }}')"
                                class="w-full flex items-center gap-3 text-left p-3.5 rounded-xl transition-all cursor-pointer
                                    {{ $isSelected ? 'brand-grad text-white shadow-card' : 'bg-surface-soft hover:bg-primary/10' }}">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center font-mono font-extrabold text-sm shrink-0 transition-all
                                    {{ $isSelected ? 'bg-white text-primary-dark' : 'bg-surface text-secondary shadow-card' }}">
                                    {{ $letter }}
                                </span>
                                <span class="text-[15px] leading-relaxed {{ $isSelected ? 'text-white' : 'text-ink/90' }}">
                                    {{ $this->currentQuestion->{$column} }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Baris aksi: ragu-ragu + prev/next --}}
                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        @php
                            $isDoubtful = $answers[$this->currentQuestion->id]['doubtful'] ?? false;
                        @endphp
                        <button type="button" wire:click="toggleDoubt({{ $this->currentQuestion->id }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all cursor-pointer
                                {{ $isDoubtful ? 'bg-warn text-secondary-dark shadow-card' : 'bg-warn-soft text-warn-dark hover:bg-warn/30' }}">
                            <x-lucide-flag class="w-4 h-4" />
                            {{ $isDoubtful ? __('Ragu-ragu ✓') : __('Tandai Ragu-ragu') }}
                        </button>

                        <div class="ms-auto flex items-center gap-2">
                            <x-ui.button variant="secondary" wire:click="previous" :disabled="$currentIndex === 0"
                                class="!px-4 disabled:opacity-40 disabled:pointer-events-none">
                                <x-lucide-chevron-left class="w-4 h-4" /> {{ __('Sebelumnya') }}
                            </x-ui.button>
                            <x-ui.button wire:click="next" :disabled="$currentIndex === $this->questions->count() - 1"
                                class="!px-4 disabled:opacity-40 disabled:pointer-events-none">
                                {{ __('Selanjutnya') }} <x-lucide-chevron-right class="w-4 h-4" />
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @else
                <x-ui.card class="text-center py-10">
                    <p class="font-bold text-secondary">{{ __('Kuis ini belum memiliki soal.') }}</p>
                </x-ui.card>
            @endif
        </div>

        {{-- -------- GRID NAVIGASI SOAL -------- --}}
        <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
            <x-ui.card class="space-y-4">
                <p class="text-sm font-bold text-secondary">{{ __('Navigasi Soal') }}</p>

                {{-- Sel status: ok = dijawab · warn = ragu · gridgrey = kosong · ring = aktif --}}
                <div class="grid grid-cols-8 sm:grid-cols-10 lg:grid-cols-6 xl:grid-cols-8 gap-1.5">
                    @foreach ($this->questions as $index => $question)
                        @php
                            $answer = $answers[$question->id] ?? null;
                            $cellColor = match (true) {
                                (bool) ($answer['doubtful'] ?? false) => 'bg-warn text-secondary-dark',
                                ($answer['selected'] ?? null) !== null => 'bg-ok text-white',
                                default => 'bg-gridgrey/30 text-secondary',
                            };
                        @endphp
                        <button type="button" wire:key="cell-{{ $question->id }}" wire:click="goTo({{ $index }})"
                            class="aspect-square rounded-lg font-mono font-extrabold tabular-nums text-xs flex items-center justify-center transition-all cursor-pointer
                                {{ $cellColor }} {{ $index === $currentIndex ? 'ring-2 ring-primary ring-offset-2' : '' }}">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-ink-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-ok"></span> {{ __('Dijawab') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-warn"></span> {{ __('Ragu-ragu') }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gridgrey/30"></span> {{ __('Belum') }}</span>
                </div>
            </x-ui.card>

            {{-- Ringkasan + submit --}}
            <x-ui.card class="space-y-4">
                @php
                    $answeredCount = collect($answers)->filter(fn ($a) => $a['selected'] !== null)->count();
                @endphp
                <div class="flex items-center justify-between text-sm">
                    <span class="text-ink-muted">{{ __('Terjawab') }}</span>
                    <span class="font-mono font-extrabold tabular-nums text-secondary">
                        {{ $answeredCount }}/{{ $this->questions->count() }}
                    </span>
                </div>

                <x-ui.button class="w-full" wire:click="submitQuiz"
                    wire:confirm="{{ __('Selesaikan ujian sekarang? Jawaban yang belum diisi akan dianggap kosong.') }}"
                    wire:loading.attr="disabled">
                    <x-lucide-send class="w-4 h-4" />
                    <span wire:loading.remove wire:target="submitQuiz">{{ __('Selesaikan Ujian') }}</span>
                    <span wire:loading wire:target="submitQuiz">{{ __('Mengirim...') }}</span>
                </x-ui.button>
            </x-ui.card>
        </div>
    </div>
</div>
