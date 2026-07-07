<div class="space-y-6">

    {{-- HERO SKOR: banner-grad --}}
    <div class="banner-grad rounded-2xl p-6 md:p-8 flex flex-col md:flex-row md:items-center gap-6">
        <div class="flex-1 space-y-1.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark">{{ __('Hasil Ujian') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ $attempt->quiz->title }}</h1>
            <p class="text-[15px] leading-relaxed text-ink/80">
                {{ __('Dikerjakan') }} {{ $attempt->completed_at->translatedFormat('d M Y, H:i') }} ·
                <span class="font-mono font-extrabold tabular-nums">{{ $this->summary['duration_used'] }}</span> {{ __('menit') }}
            </p>
        </div>

        {{-- Skor besar --}}
        <div class="bg-surface rounded-2xl shadow-hover px-8 py-6 text-center shrink-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Skor Kamu') }}</p>
            <p class="text-5xl font-mono font-extrabold tabular-nums text-secondary leading-tight">{{ $attempt->score }}</p>
            <p class="text-xs text-ink-muted">{{ __('dari') }} {{ $this->summary['max_score'] }}</p>
        </div>
    </div>

    {{-- RINGKASAN BENAR/SALAH/KOSONG --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-ui.stat title="{{ __('Benar') }}" value="{{ $this->summary['correct'] }}">
            <x-slot:icon><x-lucide-check class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="{{ __('Salah') }}" value="{{ $this->summary['wrong'] }}">
            <x-slot:icon><x-lucide-x class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="{{ __('Tidak Dijawab') }}" value="{{ $this->summary['blank'] }}">
            <x-slot:icon><x-lucide-circle-slash class="w-5 h-5" /></x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- REKAP PER SECTION --}}
    <x-ui.card class="space-y-4">
        <h2 class="font-extrabold tracking-tight text-secondary">{{ __('Rekap per Section') }}</h2>
        <div class="space-y-3">
            @foreach ($this->sectionStats as $stat)
                <div class="space-y-1.5" wire:key="section-stat-{{ $stat['section'] }}">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-secondary">{{ $stat['section'] }}</span>
                        <span class="font-mono font-extrabold tabular-nums text-secondary">
                            {{ $stat['earned'] }}/{{ $stat['max'] }}
                            <span class="font-sans font-normal text-xs text-ink-muted">({{ $stat['correct'] }}/{{ $stat['total'] }} {{ __('benar') }})</span>
                        </span>
                    </div>
                    {{-- Progress bar skor section --}}
                    <div class="h-2 rounded-full bg-surface-soft overflow-hidden">
                        <div class="h-full brand-grad rounded-full"
                            style="width: {{ $stat['max'] > 0 ? round($stat['earned'] / $stat['max'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- REVIEW SOAL + PEMBAHASAN --}}
    <div class="space-y-3">
        <h2 class="font-extrabold tracking-tight text-lg text-secondary">{{ __('Review Jawaban') }}</h2>

        @foreach ($this->questions as $index => $question)
            @php
                $answer = $this->answersByQuestion->get($question->id);
                $selected = $answer?->selected_option;
                $isCorrect = (bool) $answer?->is_correct;
            @endphp
            <x-ui.card wire:key="review-{{ $question->id }}" class="space-y-4">
                <div class="flex items-start gap-3">
                    {{-- Ikon status literal: ok = benar, bad = salah, gridgrey = kosong --}}
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-white
                        {{ $selected === null ? 'bg-gridgrey' : ($isCorrect ? 'bg-ok' : 'bg-bad') }}">
                        @if ($selected === null)
                            <x-lucide-minus class="w-4 h-4" />
                        @elseif ($isCorrect)
                            <x-lucide-check class="w-4 h-4" />
                        @else
                            <x-lucide-x class="w-4 h-4" />
                        @endif
                    </span>

                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-secondary">{{ __('Soal') }} {{ $index + 1 }}</span>
                            @if ($question->section)
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark bg-primary/10 px-2 py-0.5 rounded-full">
                                    {{ $question->section }}
                                </span>
                            @endif
                        </div>

                        @if ($question->passage)
                            <div class="p-3 rounded-xl bg-surface-tint text-sm leading-relaxed text-ink/80 whitespace-pre-line">
                                {{ $question->passage }}
                            </div>
                        @endif

                        <p class="text-[15px] leading-relaxed text-ink/90 whitespace-pre-line">{{ $question->text }}</p>

                        {{-- Opsi dengan penanda kunci & pilihan user --}}
                        <div class="space-y-1.5">
                            @foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $letter => $column)
                                @continue($question->{$column} === null)
                                @php
                                    $isKey = $question->correct_answer === $letter;
                                    $isPicked = $selected === $letter;
                                @endphp
                                <div class="flex items-center gap-2.5 p-2.5 rounded-lg text-sm
                                    {{ $isKey ? 'bg-ok-soft' : ($isPicked ? 'bg-bad-soft' : '') }}">
                                    <span class="w-6 h-6 rounded flex items-center justify-center font-mono font-extrabold text-xs shrink-0
                                        {{ $isKey ? 'bg-ok text-white' : ($isPicked ? 'bg-bad text-white' : 'bg-surface-soft text-secondary') }}">
                                        {{ $letter }}
                                    </span>
                                    <span class="text-ink/90">{{ $question->{$column} }}</span>
                                    @if ($isPicked)
                                        <span class="ms-auto text-[11px] font-semibold uppercase tracking-wider {{ $isKey ? 'text-ok' : 'text-bad' }} shrink-0">
                                            {{ __('Pilihanmu') }}
                                        </span>
                                    @elseif ($isKey)
                                        <span class="ms-auto text-[11px] font-semibold uppercase tracking-wider text-ok shrink-0">
                                            {{ __('Kunci') }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Pembahasan --}}
                        @if ($question->explanation)
                            <div class="p-3.5 rounded-xl banner-grad text-sm leading-relaxed text-ink/90">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark mb-1">{{ __('Pembahasan') }}</p>
                                <p class="whitespace-pre-line">{{ $question->explanation }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    {{-- FOOTER AKSI --}}
    <div class="flex justify-center pb-6">
        <x-ui.button :href="route('user.dashboard')">
            <x-lucide-layout-dashboard class="w-4 h-4" /> {{ __('Kembali ke Dashboard') }}
        </x-ui.button>
    </div>
</div>
