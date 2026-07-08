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
        <div class="flex items-center gap-2">
            <x-ui.button variant="ghost" wire:click="openImport">
                <x-lucide-upload class="w-4 h-4" /> {{ __('Impor Excel') }}
            </x-ui.button>
            <x-ui.button wire:click="openCreate">
                <x-lucide-plus class="w-4 h-4" /> {{ __('Tambah Soal') }}
            </x-ui.button>
        </div>
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
                        @if ($question->image_url)
                            <img src="{{ $question->imageDisplayUrl() }}" alt="{{ __('Gambar soal') }}"
                                class="max-h-32 rounded-lg border border-black/10 object-contain"
                                loading="lazy" onerror="this.style.display='none';">
                        @endif
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

            <div class="space-y-2">
                <x-ui.input label="{{ __('URL Gambar (opsional)') }}" name="imageUrl" wire:model.blur="imageUrl"
                    placeholder="https://drive.google.com/file/d/... {{ __('atau URL gambar lain') }}" />
                @if ($imageUrl)
                    <img src="{{ (new \App\Models\Question(['image_url' => $imageUrl]))->imageDisplayUrl() }}"
                        alt="{{ __('Pratinjau gambar') }}" class="max-h-40 rounded-lg border border-black/10 object-contain"
                        onerror="this.style.display='none';">
                @endif
            </div>

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

    {{-- MODAL IMPOR EXCEL --}}
    <x-ui.modal wire:model="showImport" title="{{ __('Impor Soal dari Excel') }}" maxWidth="max-w-xl">
        <form wire:submit="import" class="space-y-5">

            <div class="rounded-xl bg-surface-tint p-4 text-sm leading-relaxed text-ink-muted space-y-2">
                <p>{{ __('Gunakan template resmi agar kolom terbaca dengan benar. Kolom wajib: pertanyaan, opsi A–D, dan kunci jawaban.') }}</p>
                <p>{{ __('Gambar soal? Isi kolom image_url dengan tautan Google Drive (set akses "Siapa saja dengan link") atau URL gambar lain.') }}</p>
                <a href="{{ route('admin.quizzes.import-template') }}"
                    class="inline-flex items-center gap-2 font-semibold text-primary hover:text-primary-dark transition-colors">
                    <x-lucide-download class="w-4 h-4" /> {{ __('Unduh Template Excel') }}
                </a>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-semibold text-secondary">{{ __('File Excel (.xlsx / .csv)') }}</label>
                <input type="file" wire:model="importFile" accept=".xlsx,.csv"
                    class="block w-full text-sm text-ink file:me-4 file:px-4 file:py-2.5 file:rounded-xl file:border-0
                        file:bg-surface-soft file:text-sm file:font-semibold file:text-secondary file:cursor-pointer
                        hover:file:bg-surface-tint cursor-pointer">
                <div wire:loading wire:target="importFile" class="text-xs text-ink-muted">{{ __('Mengunggah file...') }}</div>
                @error('importFile')
                    <p class="text-sm text-bad">{{ $message }}</p>
                @enderror
            </div>

            {{-- ERROR PER BARIS --}}
            @if ($importErrors !== [])
                <div class="rounded-xl bg-bad-soft p-4 space-y-1.5 max-h-48 overflow-y-auto">
                    <p class="text-sm font-bold text-bad">{{ __('Impor dibatalkan — tidak ada soal yang disimpan:') }}</p>
                    @foreach ($importErrors as $importError)
                        <p class="text-xs text-bad/90">{{ $importError }}</p>
                    @endforeach
                </div>
            @endif

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="import">
                        <span class="flex items-center gap-2"><x-lucide-upload class="w-4 h-4" /> {{ __('Impor Soal') }}</span>
                    </span>
                    <span wire:loading wire:target="import">{{ __('Mengimpor...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
