{{-- RUANG BELAJAR: player dua kolom ala course platform.
     Livewire menangani data & gerbang akses; Alpine menangani accordion,
     tab kanan, dan catatan lokal — tanpa round-trip server. --}}
<div class="min-h-screen flex flex-col"
    x-data="{
        sidebarOpen: false,
        leftTab: 'materi',
        rightTab: 'deskripsi',
        open: { {{ $this->modules->map(fn ($m) => $m->id.': '.($this->activeItem && $this->activeItem->module_id === $m->id ? 'true' : 'false'))->implode(', ') }} },
        note: '',
        noteKey: null,
        noteSaved: false,
        loadNote(itemId) {
            this.noteKey = `cetar-note-${itemId}`;
            this.note = localStorage.getItem(this.noteKey) ?? '';
            this.noteSaved = false;
        },
        saveNote() {
            if (!this.noteKey) return;
            localStorage.setItem(this.noteKey, this.note);
            this.noteSaved = true;
            setTimeout(() => this.noteSaved = false, 1500);
        }
    }">

    {{-- ===== TOP BAR ===== --}}
    <header class="sticky top-0 z-20 bg-surface shadow-card px-4 md:px-8 py-3.5 flex items-center gap-3">
        <a href="{{ route('user.dashboard') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-secondary hover:text-primary transition-colors shrink-0">
            <x-lucide-arrow-left class="w-4 h-4" /> {{ __('Kembali') }}
        </a>

        {{-- Tombol Sebelumnya / Selanjutnya --}}
        <div class="flex items-center gap-1 ms-2">
            <button type="button" wire:click="goToPrev"
                {{ $this->prevItemId ? '' : 'disabled' }}
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                    {{ $this->prevItemId ? 'text-secondary hover:bg-surface-tint cursor-pointer' : 'text-ink-faint cursor-not-allowed opacity-50' }}">
                <x-lucide-chevron-left class="w-4 h-4" /> {{ __('Sebelumnya') }}
            </button>
            <button type="button" wire:click="goToNext"
                {{ $this->nextItemId ? '' : 'disabled' }}
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all
                    {{ $this->nextItemId ? 'text-secondary hover:bg-surface-tint cursor-pointer' : 'text-ink-faint cursor-not-allowed opacity-50' }}">
                {{ __('Selanjutnya') }} <x-lucide-chevron-right class="w-4 h-4" />
            </button>
        </div>

        {{-- Toggle sidebar (mobile) --}}
        <button type="button" @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden ms-auto p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer"
            aria-label="Daftar materi">
            <x-lucide-list class="w-5 h-5" />
        </button>

        <a href="{{ route('user.dashboard') }}"
            class="hidden lg:inline-flex ms-auto p-2 rounded-lg text-ink-muted hover:bg-surface-soft hover:text-secondary transition-all"
            aria-label="Tutup">
            <x-lucide-x class="w-5 h-5" />
        </a>
    </header>

    {{-- ===== GRID UTAMA ===== --}}
    <div class="flex-1 w-full max-w-7xl mx-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-[minmax(300px,30%)_1fr] gap-6 items-start">

        {{-- ================= SIDEBAR KIRI ================= --}}
        <aside class="bg-surface rounded-2xl shadow-card p-5 space-y-5 lg:sticky lg:top-20"
            :class="sidebarOpen ? 'block' : 'hidden lg:block'">

            {{-- HEADER --}}
            <div class="space-y-1">
                <h1 class="text-xl font-extrabold tracking-tight text-secondary">{{ __('Konten Belajar') }}</h1>
                <p class="text-sm text-ink-muted">{{ $package->name }}</p>
            </div>

            {{-- KARTU PROGRES --}}
            <div class="rounded-xl border border-black/10 p-4 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-secondary">{{ __('Progresmu') }}</p>
                    <p class="text-xs text-ink-muted">
                        <span class="font-mono font-extrabold tabular-nums">{{ $this->overall['completed'] }}</span>
                        {{ __('dari') }}
                        <span class="font-mono font-extrabold tabular-nums">{{ $this->overall['total'] }}</span>
                        {{ __('pelajaran selesai') }}
                    </p>
                </div>
                <div class="h-1.5 rounded-full bg-surface-soft overflow-hidden">
                    <div class="h-full brand-grad rounded-full transition-all duration-500" style="width: {{ $this->overall['percent'] }}%"></div>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-ink-muted"><span class="font-mono font-extrabold tabular-nums">{{ $this->overall['percent'] }}%</span> {{ __('selesai') }}</span>
                    <span class="font-semibold text-primary-dark">{{ $this->overall['percent'] >= 100 ? __('Tuntas! 🏆') : __('Semangat! 🎯') }}</span>
                </div>
            </div>

            {{-- TAB STRIP: Materi | Try Out --}}
            <div class="border-b border-black/10 flex items-center gap-5">
                <button type="button" @click="leftTab = 'materi'"
                    class="pb-2 text-sm font-bold border-b-2 transition-colors cursor-pointer"
                    :class="leftTab === 'materi' ? 'text-secondary border-primary' : 'text-ink-faint border-transparent hover:text-secondary'">
                    {{ __('Materi') }}
                </button>
                <button type="button" @click="leftTab = 'tryout'"
                    class="pb-2 text-sm font-bold border-b-2 transition-colors cursor-pointer"
                    :class="leftTab === 'tryout' ? 'text-secondary border-primary' : 'text-ink-faint border-transparent hover:text-secondary'">
                    {{ __('Try Out') }}
                    <span class="ms-1 font-mono text-[11px] tabular-nums">{{ $this->quizItems->count() }}</span>
                </button>
            </div>

            {{-- ACCORDION MODUL --}}
            <div class="space-y-3" x-show="leftTab === 'materi'">
                @foreach ($this->modules as $module)
                    <div wire:key="module-{{ $module->id }}" class="rounded-xl border border-black/10 overflow-hidden">

                        {{-- HEADER MODUL (toggle Alpine, tanpa server) --}}
                        <button type="button" @click="open[{{ $module->id }}] = !open[{{ $module->id }}]"
                            class="w-full flex items-center gap-2.5 px-4 py-3.5 hover:bg-surface-tint transition-colors cursor-pointer text-left">
                            <x-lucide-chevron-down class="w-4 h-4 text-ink-muted shrink-0 transition-transform duration-200"
                                ::class="open[{{ $module->id }}] ? 'rotate-180' : ''" />
                            <span class="flex-1 text-sm font-bold text-secondary">
                                {{ str_pad($module->order, 2, '0', STR_PAD_LEFT) }} {{ $module->title }}
                            </span>
                            <span class="text-[11px] font-mono font-extrabold tabular-nums text-ink-faint">{{ $module->progress_percent }}%</span>
                        </button>

                        {{-- DAFTAR ITEM --}}
                        <div x-show="open[{{ $module->id }}]" x-collapse x-cloak class="border-t border-black/5">
                            @forelse ($module->items as $item)
                                @php
                                    $isQuiz = $item->isQuiz();
                                    $type = $isQuiz ? 'quiz' : $item->contentable?->type?->value;
                                    $isActive = $activeItemId === $item->id;
                                    $meta = $isQuiz
                                        ? __('Try Out').' · '.$item->contentable?->duration_minutes.' '.__('menit')
                                        : $item->contentable?->type?->label();
                                @endphp

                                @if ($item->is_unlocked)
                                    {{-- Item terbuka: bisa diklik; state aktif = aksen kiri oranye seperti referensi --}}
                                    <button type="button" wire:click="selectItem({{ $item->id }})" wire:key="item-{{ $item->id }}"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors cursor-pointer border-l-4
                                            {{ $isActive ? 'bg-primary/10 border-primary' : 'border-transparent hover:bg-surface-tint' }}">
                                        <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                            {{ $item->is_completed ? 'bg-ok-soft text-ok' : ($isActive ? 'brand-grad text-white' : 'bg-surface-soft text-secondary') }}">
                                            @if ($item->is_completed) <x-lucide-check class="w-4 h-4" />
                                            @elseif ($type === 'video') <x-lucide-play-circle class="w-4 h-4" />
                                            @elseif ($type === 'pdf') <x-lucide-file class="w-4 h-4" />
                                            @elseif ($type === 'quiz') <x-lucide-help-circle class="w-4 h-4" />
                                            @else <x-lucide-file-text class="w-4 h-4" /> @endif
                                        </span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm font-semibold truncate {{ $isActive ? 'text-primary-dark' : 'text-secondary' }}">
                                                {{ $item->contentable?->title ?? '—' }}
                                            </span>
                                            <span class="block text-[11px] text-ink-muted">
                                                {{ $meta }}
                                                @if ($item->is_completed) · <span class="text-ok font-semibold">{{ __('Selesai') }}</span> @endif
                                            </span>
                                        </span>
                                    </button>
                                @else
                                    {{-- Item terkunci: redup + gembok, tidak bisa diklik --}}
                                    <div wire:key="item-{{ $item->id }}"
                                        class="flex items-center gap-3 px-4 py-3 border-l-4 border-transparent opacity-50 cursor-not-allowed"
                                        title="{{ __('Selesaikan item sebelumnya untuk membuka') }}">
                                        <span class="w-8 h-8 rounded-full bg-surface-soft text-ink-faint flex items-center justify-center shrink-0">
                                            <x-lucide-lock class="w-3.5 h-3.5" />
                                        </span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm font-semibold text-ink-muted truncate">{{ $item->contentable?->title ?? '—' }}</span>
                                            <span class="block text-[11px] text-ink-faint">{{ $meta }} · {{ __('Terkunci') }}</span>
                                        </span>
                                    </div>
                                @endif
                            @empty
                                <p class="px-4 py-4 text-xs text-ink-muted">{{ __('Modul ini belum berisi materi.') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- PANEL TRY OUT: daftar kuis + riwayat pengerjaan --}}
            <div class="space-y-3" x-show="leftTab === 'tryout'" x-cloak>
                @forelse ($this->quizItems as $quizItem)
                    @php
                        $quiz = $quizItem->contentable;
                        $history = $this->attemptsByQuiz->get($quiz?->id, collect());
                        $completedAttempts = $history->where('status', \App\Enums\AttemptStatus::Completed);
                        $bestScore = $completedAttempts->max('score');
                    @endphp

                    <div wire:key="tryout-{{ $quizItem->id }}" class="rounded-xl border border-black/10 p-4 space-y-3
                        {{ $quizItem->is_unlocked ? '' : 'opacity-60' }}">

                        {{-- HEADER KUIS --}}
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center shrink-0
                                {{ $quizItem->is_completed ? 'bg-ok-soft text-ok' : ($quizItem->is_unlocked ? 'brand-grad text-white' : 'bg-surface-soft text-ink-faint') }}">
                                @if (! $quizItem->is_unlocked) <x-lucide-lock class="w-4 h-4" />
                                @elseif ($quizItem->is_completed) <x-lucide-check class="w-4 h-4" />
                                @else <x-lucide-help-circle class="w-4 h-4" /> @endif
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-secondary leading-snug">{{ $quiz?->title ?? '—' }}</p>
                                <p class="text-[11px] text-ink-muted mt-0.5">
                                    <span class="font-mono font-extrabold tabular-nums">{{ $quiz?->questions_count ?? $quiz?->questions()->count() }}</span> {{ __('soal') }}
                                    · <span class="font-mono font-extrabold tabular-nums">{{ $quiz?->duration_minutes }}</span> {{ __('menit') }}
                                    @if (! $quizItem->is_unlocked)
                                        · <span class="text-ink-faint">{{ __('Terkunci') }}</span>
                                    @elseif ($bestScore !== null)
                                        · {{ __('Skor terbaik') }}: <span class="font-mono font-extrabold tabular-nums text-primary-dark">{{ $bestScore }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- RIWAYAT PENGERJAAN --}}
                        @if ($quizItem->is_unlocked)
                            @if ($history->isNotEmpty())
                                <div class="space-y-1.5 border-t border-black/5 pt-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Riwayat') }}</p>
                                    @foreach ($history->take(5) as $attempt)
                                        <div wire:key="attempt-{{ $attempt->id }}" class="flex items-center gap-2 text-xs">
                                            <span class="text-ink-muted flex-1 truncate">{{ $attempt->started_at->translatedFormat('d M Y H:i') }}</span>
                                            @if ($attempt->status === \App\Enums\AttemptStatus::Completed)
                                                <span class="font-mono font-extrabold tabular-nums text-secondary">{{ $attempt->score }}</span>
                                                <a href="{{ route('user.exam.result', $attempt) }}"
                                                    class="font-semibold text-primary hover:text-primary-dark transition-colors shrink-0">
                                                    {{ __('Lihat Hasil') }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-warn shrink-0">{{ __('Berlangsung') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-ink-faint border-t border-black/5 pt-3">{{ __('Belum ada riwayat pengerjaan.') }}</p>
                            @endif

                            {{-- CTA --}}
                            <a href="{{ route('user.exam.prepare', $quiz) }}"
                                class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
                                {{ $completedAttempts->isNotEmpty() ? __('Kerjakan Lagi') : __('Kerjakan') }}
                                <x-lucide-arrow-right class="w-3.5 h-3.5" />
                            </a>
                        @else
                            <p class="text-xs text-ink-faint border-t border-black/5 pt-3">{{ __('Selesaikan item sebelumnya untuk membuka try out ini.') }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-ink-muted py-4 text-center">{{ __('Paket ini belum memiliki try out.') }}</p>
                @endforelse
            </div>
        </aside>

        {{-- ================= PANEL KANAN ================= --}}
        <main class="space-y-5 min-w-0">
            @if ($this->activeItem)
                @php
                    $active = $this->activeItem;
                    $activeIsQuiz = $active->isQuiz();
                    $activeType = $activeIsQuiz ? 'quiz' : $active->contentable?->type?->value;
                @endphp

                {{-- ===== AREA MEDIA ===== --}}
                @if ($activeType === 'video')
                    <div class="rounded-2xl overflow-hidden shadow-card bg-secondary aspect-video">
                        <iframe src="{{ $active->contentable->videoEmbedUrl() }}" class="w-full h-full"
                            title="{{ $active->contentable->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                @elseif ($activeType === 'pdf')
                    <div class="rounded-2xl overflow-hidden shadow-card bg-surface">
                        @if ($active->contentable->file_path)
                            <iframe src="{{ Storage::url($active->contentable->file_path) }}" class="w-full h-[65vh]"
                                title="{{ $active->contentable->title }}"></iframe>
                        @else
                            <div class="p-10 text-center text-sm text-ink-muted">{{ __('File PDF belum diunggah.') }}</div>
                        @endif
                    </div>
                @elseif ($activeType === 'quiz')
                    {{-- HERO TRY OUT: masuk lewat halaman persiapan --}}
                    <div class="rounded-2xl shadow-card bg-secondary text-white p-8 md:p-10 space-y-6">
                        <div class="space-y-1.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-light">{{ __('Try Out') }}</p>
                            <h2 class="text-2xl font-extrabold tracking-tight">{{ $active->contentable->title }}</h2>
                            @if ($active->contentable->description)
                                <p class="text-sm text-white/70 leading-relaxed">{{ $active->contentable->description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <p class="font-mono font-extrabold tabular-nums text-2xl">{{ $active->contentable->questions()->count() }}</p>
                                <p class="text-xs text-white/60">{{ __('Soal') }}</p>
                            </div>
                            <div>
                                <p class="font-mono font-extrabold tabular-nums text-2xl">{{ $active->contentable->duration_minutes }}</p>
                                <p class="text-xs text-white/60">{{ __('Menit') }}</p>
                            </div>
                            <div>
                                <p class="font-mono font-extrabold tabular-nums text-2xl">{{ (int) $active->contentable->questions()->sum('points') }}</p>
                                <p class="text-xs text-white/60">{{ __('Total Poin') }}</p>
                            </div>
                        </div>
                        <x-ui.button :href="route('user.exam.prepare', $active->contentable_id)" class="!px-6 !py-3">
                            {{ $active->is_completed ? __('Kerjakan Lagi') : __('Mulai Try Out') }}
                            <x-lucide-arrow-right class="w-4 h-4" />
                        </x-ui.button>
                    </div>
                @else
                    {{-- MATERI TEKS --}}
                    <div class="rounded-2xl shadow-card bg-surface p-6 md:p-8">
                        <h2 class="text-xl font-extrabold tracking-tight text-secondary mb-4">{{ $active->contentable->title }}</h2>
                        <div class="prose prose-sm max-w-none text-ink leading-relaxed whitespace-pre-line">{{ $active->contentable->body }}</div>
                    </div>
                @endif

                {{-- ===== BAR TANDAI SELESAI (materi yang belum tuntas) ===== --}}
                @if (! $activeIsQuiz && ! $active->is_completed)
                    <div class="rounded-2xl shadow-card bg-surface p-4 flex flex-col sm:flex-row items-center gap-3">
                        <p class="flex-1 text-sm text-ink-muted text-center sm:text-left">
                            {{ __('Sudah selesai mempelajari materi ini? Tandai untuk membuka item berikutnya.') }}
                        </p>
                        <x-ui.button wire:click="markAsComplete" wire:loading.attr="disabled" class="shrink-0">
                            <span wire:loading.remove wire:target="markAsComplete" class="flex items-center gap-2">
                                <x-lucide-check class="w-4 h-4" /> {{ __('Tandai Selesai') }}
                            </span>
                            <span wire:loading wire:target="markAsComplete">{{ __('Menyimpan...') }}</span>
                        </x-ui.button>
                    </div>
                @endif

                {{-- ===== TAB + KONTEN BAWAH ===== --}}
                <div class="rounded-2xl shadow-card bg-surface p-5 md:p-6 space-y-5">

                    {{-- PILL TABS (Alpine murni) --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach (['deskripsi' => __('Deskripsi'), 'catatan' => __('Catatan'), 'sumber' => __('Sumber')] as $tab => $label)
                            <button type="button" @click="rightTab = '{{ $tab }}'"
                                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all cursor-pointer"
                                :class="rightTab === '{{ $tab }}' ? 'brand-grad text-white shadow-card' : 'bg-surface-soft text-secondary hover:bg-surface-tint'">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- BANNER CETAR AI (placeholder — integrasi LLM menyusul) --}}
                    <div class="flex items-start gap-3 rounded-xl bg-primary/10 p-4">
                        <x-lucide-sparkles class="w-5 h-5 text-primary shrink-0 mt-0.5" />
                        <div class="text-sm leading-relaxed">
                            <p class="font-bold text-secondary">{{ __('BIMBEL CETAR AI Study Assistant') }}</p>
                            <p class="text-ink-muted">
                                {{ __('Ringkasan materi & catatan otomatis segera hadir.') }}
                                <span class="font-semibold text-primary-dark">{{ __('Coba segera!') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- PANEL: DESKRIPSI --}}
                    <div x-show="rightTab === 'deskripsi'" x-transition.opacity>
                        <dl class="space-y-3 text-sm">
                            <div class="flex gap-3">
                                <dt class="w-24 shrink-0 font-semibold text-ink-muted">{{ __('Judul') }}</dt>
                                <dd class="text-secondary font-semibold">{{ $active->contentable?->title }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="w-24 shrink-0 font-semibold text-ink-muted">{{ __('Modul') }}</dt>
                                <dd class="text-ink">{{ $active->module->title }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="w-24 shrink-0 font-semibold text-ink-muted">{{ __('Tipe') }}</dt>
                                <dd class="text-ink">{{ $activeIsQuiz ? __('Try Out') : $active->contentable?->type?->label() }}</dd>
                            </div>
                            @if ($activeIsQuiz && $active->contentable?->description)
                                <div class="flex gap-3">
                                    <dt class="w-24 shrink-0 font-semibold text-ink-muted">{{ __('Deskripsi') }}</dt>
                                    <dd class="text-ink leading-relaxed">{{ $active->contentable->description }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- PANEL: CATATAN (localStorage, per item).
                         wire:key memaksa Alpine re-init saat item aktif berganti → catatan item baru dimuat --}}
                    <div x-show="rightTab === 'catatan'" x-transition.opacity x-cloak class="space-y-2"
                        wire:key="note-panel-{{ $active->id }}" x-init="loadNote({{ $active->id }})">
                        <textarea x-model="note" @input.debounce.500ms="saveNote()" rows="6"
                            placeholder="{{ __('Tulis catatan belajarmu di sini...') }}"
                            class="w-full rounded-xl bg-surface-soft border-0 p-4 text-sm text-ink focus:ring-2 focus:ring-primary/40 resize-y"></textarea>
                        <p class="text-[11px] text-ink-faint flex items-center gap-1.5">
                            <span x-show="!noteSaved">{{ __('Catatan tersimpan otomatis di perangkat ini.') }}</span>
                            <span x-show="noteSaved" x-transition.opacity class="text-ok font-semibold flex items-center gap-1">
                                <x-lucide-check class="w-3 h-3" /> {{ __('Tersimpan!') }}
                            </span>
                        </p>
                    </div>

                    {{-- PANEL: SUMBER --}}
                    <div x-show="rightTab === 'sumber'" x-transition.opacity x-cloak>
                        @if ($activeType === 'pdf' && $active->contentable->file_path)
                            <a href="{{ Storage::url($active->contentable->file_path) }}" target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-soft text-sm font-semibold text-secondary hover:bg-surface-tint transition-all">
                                <x-lucide-download class="w-4 h-4 text-primary" /> {{ __('Unduh PDF') }}: {{ $active->contentable->title }}
                            </a>
                        @elseif ($activeType === 'video' && $active->contentable->video_url)
                            <a href="{{ $active->contentable->video_url }}" target="_blank"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-soft text-sm font-semibold text-secondary hover:bg-surface-tint transition-all">
                                <x-lucide-external-link class="w-4 h-4 text-primary" /> {{ __('Buka video di YouTube') }}
                            </a>
                        @else
                            <p class="text-sm text-ink-muted">{{ __('Tidak ada berkas untuk materi ini.') }}</p>
                        @endif
                    </div>
                </div>
            @else
                {{-- ROADMAP KOSONG --}}
                <div class="rounded-2xl shadow-card bg-surface p-12 text-center space-y-3">
                    <x-lucide-map class="w-10 h-10 mx-auto text-ink-faint" />
                    <p class="font-bold text-secondary">{{ __('Roadmap belum tersedia') }}</p>
                    <p class="text-sm text-ink-muted">{{ __('Materi paket ini sedang disusun. Cek lagi nanti, ya!') }}</p>
                </div>
            @endif
        </main>
    </div>
</div>
