<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <a href="{{ route('admin.packages') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-muted hover:text-primary transition-colors">
                <x-lucide-arrow-left class="w-3.5 h-3.5" /> {{ __('Kembali ke Paket') }}
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">
                {{ __('Roadmap') }}: {{ $package->name }}
            </h1>
            <p class="text-sm text-ink-muted">
                {{ __('Susun modul dan urutan materi. Siswa harus menyelesaikan item secara berurutan sebelum try out terbuka.') }}
            </p>
        </div>
        <x-ui.button wire:click="openCreateModule">
            <x-lucide-plus class="w-4 h-4" /> {{ __('Modul Baru') }}
        </x-ui.button>
    </div>

    {{-- DAFTAR MODUL --}}
    <div class="space-y-4">
        @forelse ($this->modules as $module)
            <x-ui.card wire:key="module-{{ $module->id }}" class="!p-0 overflow-hidden">

                {{-- HEADER MODUL --}}
                <div class="flex items-center gap-3 px-5 py-4 bg-surface-tint">
                    <span class="w-8 h-8 rounded-full brand-grad text-white flex items-center justify-center text-sm font-extrabold shrink-0">
                        {{ $module->order }}
                    </span>
                    <p class="flex-1 font-bold text-secondary truncate">{{ $module->title }}</p>

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="moveModule({{ $module->id }}, 'up')"
                            class="p-1.5 rounded-lg text-ink-muted hover:bg-surface-soft transition-all cursor-pointer {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}"
                            aria-label="Naik">
                            <x-lucide-chevron-up class="w-4 h-4" />
                        </button>
                        <button type="button" wire:click="moveModule({{ $module->id }}, 'down')"
                            class="p-1.5 rounded-lg text-ink-muted hover:bg-surface-soft transition-all cursor-pointer {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}"
                            aria-label="Turun">
                            <x-lucide-chevron-down class="w-4 h-4" />
                        </button>
                        <button type="button" wire:click="openEditModule({{ $module->id }})"
                            class="p-1.5 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer" aria-label="Edit">
                            <x-lucide-pencil class="w-4 h-4" />
                        </button>
                        <button type="button" wire:click="deleteModule({{ $module->id }})"
                            wire:confirm="{{ __('Hapus modul ini? Semua item dan progres siswa di dalamnya ikut terhapus.') }}"
                            class="p-1.5 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer" aria-label="Delete">
                            <x-lucide-trash-2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {{-- ITEM ROADMAP --}}
                <div class="divide-y divide-black/5">
                    @forelse ($module->items as $item)
                        <div wire:key="item-{{ $item->id }}" class="flex items-center gap-3 px-5 py-3">
                            <span class="text-xs font-mono font-extrabold tabular-nums text-ink-faint w-5 text-center">{{ $item->order }}</span>

                            {{-- Ikon tipe --}}
                            @if ($item->isQuiz())
                                <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary-dark flex items-center justify-center shrink-0">
                                    <x-lucide-list-checks class="w-4 h-4" />
                                </span>
                            @elseif ($item->contentable?->type?->value === 'video')
                                <span class="w-8 h-8 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                                    <x-lucide-play-circle class="w-4 h-4" />
                                </span>
                            @elseif ($item->contentable?->type?->value === 'pdf')
                                <span class="w-8 h-8 rounded-lg bg-bad-soft text-bad flex items-center justify-center shrink-0">
                                    <x-lucide-file class="w-4 h-4" />
                                </span>
                            @else
                                <span class="w-8 h-8 rounded-lg bg-secondary/10 text-secondary flex items-center justify-center shrink-0">
                                    <x-lucide-file-text class="w-4 h-4" />
                                </span>
                            @endif

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-secondary truncate">{{ $item->contentable?->title ?? '—' }}</p>
                                <p class="text-[11px] text-ink-muted">
                                    {{ $item->isQuiz() ? __('Try Out') : $item->contentable?->type?->label() }}
                                </p>
                            </div>

                            {{-- Toggle kunci --}}
                            <button type="button" wire:click="toggleLock({{ $item->id }})"
                                class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-1 rounded-full transition-all cursor-pointer
                                    {{ $item->is_locked_by_default ? 'bg-secondary/10 text-secondary' : 'bg-ok-soft text-ok' }}"
                                title="{{ $item->is_locked_by_default ? __('Terkunci sampai item sebelumnya selesai') : __('Selalu terbuka') }}">
                                @if ($item->is_locked_by_default)
                                    <x-lucide-lock class="w-3 h-3" /> {{ __('Berurutan') }}
                                @else
                                    <x-lucide-lock-open class="w-3 h-3" /> {{ __('Bebas') }}
                                @endif
                            </button>

                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="moveItem({{ $item->id }}, 'up')"
                                    class="p-1.5 rounded-lg text-ink-muted hover:bg-surface-soft transition-all cursor-pointer {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}"
                                    aria-label="Naik">
                                    <x-lucide-chevron-up class="w-4 h-4" />
                                </button>
                                <button type="button" wire:click="moveItem({{ $item->id }}, 'down')"
                                    class="p-1.5 rounded-lg text-ink-muted hover:bg-surface-soft transition-all cursor-pointer {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}"
                                    aria-label="Turun">
                                    <x-lucide-chevron-down class="w-4 h-4" />
                                </button>
                                <button type="button" wire:click="removeItem({{ $item->id }})"
                                    wire:confirm="{{ __('Lepas item ini dari modul? Progres siswa pada item ini ikut terhapus.') }}"
                                    class="p-1.5 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer" aria-label="Remove">
                                    <x-lucide-x class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-ink-muted">{{ __('Modul masih kosong.') }}</p>
                    @endforelse

                    {{-- TOMBOL TAMBAH ITEM --}}
                    <div class="px-5 py-3">
                        <button type="button" wire:click="openAddItem({{ $module->id }})"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-dark hover:text-primary transition-colors cursor-pointer">
                            <x-lucide-plus class="w-4 h-4" /> {{ __('Tambah Materi / Try Out') }}
                        </button>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card>
                <div class="py-10 text-center space-y-2">
                    <x-lucide-map class="w-10 h-10 mx-auto text-ink-faint" />
                    <p class="text-sm text-ink-muted">{{ __('Belum ada modul. Mulai susun roadmap belajar paket ini!') }}</p>
                </div>
            </x-ui.card>
        @endforelse
    </div>

    {{-- MODAL MODUL --}}
    <x-ui.modal wire:model="showModuleForm" title="{{ $editingModuleId ? __('Edit Modul') : __('Modul Baru') }}">
        <form wire:submit="saveModule" class="space-y-5">
            <x-ui.input label="{{ __('Judul Modul') }}" name="moduleTitle" wire:model="moduleTitle"
                placeholder="{{ __('Contoh: Modul 1 — Persiapan Dasar') }}" />

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    {{ $editingModuleId ? __('Simpan Perubahan') : __('Tambah Modul') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- MODAL TAMBAH ITEM --}}
    <x-ui.modal wire:model="showItemForm" title="{{ __('Tambah Item ke Modul') }}">
        <form wire:submit="addItem" class="space-y-5">

            <x-ui.select label="{{ __('Jenis Item') }}" name="itemType" wire:model.live="itemType">
                <option value="content">{{ __('Materi (Teks / PDF / Video)') }}</option>
                <option value="quiz">{{ __('Try Out (Kuis)') }}</option>
            </x-ui.select>

            @if ($itemType === 'quiz')
                <x-ui.select label="{{ __('Pilih Try Out') }}" name="itemId" wire:model="itemId">
                    <option value="">{{ __('— pilih kuis —') }}</option>
                    @foreach ($this->quizOptions as $quiz)
                        <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                    @endforeach
                </x-ui.select>
            @else
                <x-ui.select label="{{ __('Pilih Materi') }}" name="itemId" wire:model="itemId">
                    <option value="">{{ __('— pilih materi —') }}</option>
                    @foreach ($this->contentOptions as $content)
                        <option value="{{ $content->id }}">{{ $content->title }} ({{ $content->type->label() }})</option>
                    @endforeach
                </x-ui.select>
            @endif

            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" wire:model="itemLocked" class="w-4 h-4 rounded accent-[#F5872A]">
                <span class="text-sm text-ink">{{ __('Terkunci sampai item sebelumnya selesai (gerbang berurutan)') }}</span>
            </label>

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">{{ __('Tambahkan') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
