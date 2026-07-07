<div class="space-y-6">

    {{-- HEADER + BREADCRUMB --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <a href="{{ route('admin.quizzes') }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
                <x-lucide-arrow-left class="w-4 h-4" /> {{ __('Kembali ke Kuis') }}
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ $quiz->title }}</h1>
            <p class="text-sm text-ink-muted">
                {{ $this->questions->count() }} {{ __('soal ditampilkan') }} ·
                <span class="font-mono font-extrabold tabular-nums">{{ $quiz->duration_minutes }}</span> {{ __('menit') }}
            </p>
        </div>
        <x-ui.button wire:click="openCreate">
            <x-lucide-plus class="w-4 h-4" /> {{ __('Tambah Soal') }}
        </x-ui.button>
    </div>

    {{-- TAB FILTER SECTION --}}
    @if ($this->sections->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="$set('activeSection', '')"
                class="px-4 py-2 rounded-full text-sm font-semibold transition-all cursor-pointer {{ $activeSection === '' ? 'brand-grad text-white shadow-card' : 'bg-surface text-secondary shadow-card hover:shadow-hover' }}">
                {{ __('Semua') }}
            </button>
            @foreach ($this->sections as $sectionName)
                <button type="button" wire:click="$set('activeSection', '{{ $sectionName }}')"
                    wire:key="section-tab-{{ $sectionName }}"
                    class="px-4 py-2 rounded-full text-sm font-semibold transition-all cursor-pointer {{ $activeSection === $sectionName ? 'brand-grad text-white shadow-card' : 'bg-surface text-secondary shadow-card hover:shadow-hover' }}">
                    {{ $sectionName }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- DAFTAR SOAL --}}
    <div class="space-y-3">
        @forelse ($this->questions as $index => $question)
            <x-ui.card wire:key="question-{{ $question->id }}" class="space-y-3">
                <div class="flex items-start gap-3">
                    {{-- Nomor --}}
                    <span class="w-8 h-8 rounded-lg bg-secondary text-white font-mono font-extrabold tabular-nums text-sm flex items-center justify-center shrink-0">
                        {{ $index + 1 }}
                    </span>

                    <div class="flex-1 min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($question->section)
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-primary-dark bg-primary/10 px-2 py-0.5 rounded-full">
                                    {{ $question->section }}
                                </span>
                            @endif
                            <span class="text-[11px] font-semibold text-ink-faint">
                                {{ $question->points }} {{ __('poin') }} · {{ __('Kunci') }}:
                                <span class="font-mono font-extrabold text-ok">{{ $question->correct_answer }}</span>
                            </span>
                        </div>
                        <p class="text-[15px] leading-relaxed text-ink/90">{{ $question->text }}</p>
                    </div>

                    {{-- Aksi --}}
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" wire:click="openEdit({{ $question->id }})"
                            class="p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer"
                            aria-label="Edit">
                            <x-lucide-pencil class="w-4 h-4" />
                        </button>
                        <button type="button" wire:click="delete({{ $question->id }})"
                            wire:confirm="{{ __('Hapus soal ini?') }}"
                            class="p-2 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer"
                            aria-label="Delete">
                            <x-lucide-trash-2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="text-center py-10">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-surface-soft text-ink-faint flex items-center justify-center mb-4">
                    <x-lucide-file-question-mark class="w-6 h-6" />
                </div>
                <p class="font-bold text-secondary">{{ __('Belum ada soal') }}</p>
                <p class="text-sm text-ink-muted mt-1">{{ __('Tambahkan soal pertama untuk kuis ini.') }}</p>
            </x-ui.card>
        @endforelse
    </div>

    {{-- MODAL FORM SOAL --}}
    <x-ui.modal wire:model="showForm" title="{{ $editingId ? __('Edit Soal') : __('Tambah Soal') }}" maxWidth="max-w-3xl">
        <form wire:submit="save" class="space-y-5">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input label="{{ __('Section') }}" name="section" wire:model="section"
                    placeholder="{{ __('Contoh: TWK / TIU / TKP') }}" />
                <x-ui.input label="{{ __('Poin') }}" name="points" type="number" wire:model="points" min="0" max="100" />
            </div>

            <x-ui.textarea label="{{ __('Teks Bacaan / Passage (opsional)') }}" name="passage" wire:model="passage"
                rows="3" placeholder="{{ __('Untuk soal dengan bacaan panjang') }}" />

            <x-ui.textarea label="{{ __('Pertanyaan') }}" name="text" wire:model="text" rows="3"
                placeholder="{{ __('Tulis pertanyaan utama di sini...') }}" />

            {{-- OPSI JAWABAN A-E --}}
            <div class="space-y-3">
                <p class="text-sm font-semibold text-secondary">{{ __('Opsi Jawaban') }} <span class="text-ink-faint font-normal">({{ __('E opsional') }})</span></p>

                @foreach (['A' => 'optionA', 'B' => 'optionB', 'C' => 'optionC', 'D' => 'optionD', 'E' => 'optionE'] as $letter => $property)
                    <div class="flex items-start gap-3" wire:key="option-{{ $letter }}">
                        {{-- Radio kunci jawaban: klik huruf untuk menandai jawaban benar --}}
                        <label class="shrink-0 cursor-pointer select-none pt-1.5" title="{{ __('Jadikan kunci jawaban') }}">
                            <input type="radio" wire:model="correctAnswer" value="{{ $letter }}" class="sr-only peer">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center font-mono font-extrabold text-sm transition-all
                                peer-checked:brand-grad peer-checked:text-white bg-surface-soft text-secondary hover:bg-primary/10">
                                {{ $letter }}
                            </span>
                        </label>
                        <div class="flex-1">
                            <x-ui.input name="{{ $property }}" wire:model="{{ $property }}"
                                placeholder="{{ __('Isi opsi') }} {{ $letter }}{{ $letter === 'E' ? ' ('.__('kosongkan jika hanya 4 opsi').')' : '' }}" />
                        </div>
                    </div>
                @endforeach

                @error('correctAnswer')
                    <p class="text-sm text-bad">{{ $message }}</p>
                @enderror
                <p class="text-xs text-ink-muted">{{ __('Klik huruf untuk menandai kunci jawaban. Kunci saat ini:') }}
                    <span class="font-mono font-extrabold text-ok">{{ $correctAnswer }}</span>
                </p>
            </div>

            <x-ui.textarea label="{{ __('Pembahasan (opsional)') }}" name="explanation" wire:model="explanation"
                rows="3" placeholder="{{ __('Penjelasan kenapa jawaban tersebut benar') }}" />

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? __('Simpan Perubahan') : __('Tambah Soal') }}</span>
                    <span wire:loading wire:target="save">{{ __('Menyimpan...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
