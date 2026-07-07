<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Kelola Kuis') }}</h1>
        </div>
        <x-ui.button wire:click="openCreate">
            <x-lucide-plus class="w-4 h-4" /> {{ __('Kuis Baru') }}
        </x-ui.button>
    </div>

    {{-- SEARCH --}}
    <div class="max-w-sm">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari kuis...') }}" />
    </div>

    {{-- TABEL KUIS --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">
                        <th class="px-5 py-3.5">{{ __('Kuis') }}</th>
                        <th class="px-5 py-3.5">{{ __('Soal') }}</th>
                        <th class="px-5 py-3.5">{{ __('Durasi') }}</th>
                        <th class="px-5 py-3.5">{{ __('Paket') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse ($quizzes as $quiz)
                        <tr wire:key="quiz-{{ $quiz->id }}" class="hover:bg-surface-tint transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-bold text-secondary">{{ $quiz->title }}</p>
                                @if ($quiz->description)
                                    <p class="text-xs text-ink-muted truncate max-w-xs">{{ $quiz->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold tabular-nums text-secondary">
                                {{ $quiz->questions_count }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono font-extrabold tabular-nums text-secondary">{{ $quiz->duration_minutes }}</span>
                                <span class="text-xs text-ink-muted">{{ __('menit') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1 max-w-[220px]">
                                    @forelse ($quiz->packages as $package)
                                        <span class="text-[11px] font-semibold text-primary-dark bg-primary/10 px-2 py-0.5 rounded-full">
                                            {{ $package->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-ink-faint">{{ __('Belum didistribusikan') }}</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Kelola soal --}}
                                    <a href="{{ route('admin.quizzes.questions', $quiz) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-primary-dark bg-primary/10 hover:brand-grad hover:text-white transition-all">
                                        <x-lucide-list-checks class="w-4 h-4" /> {{ __('Soal') }}
                                    </a>
                                    <button type="button" wire:click="openEdit({{ $quiz->id }})"
                                        class="p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer"
                                        aria-label="Edit">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $quiz->id }})"
                                        wire:confirm="{{ __('Hapus kuis ini? Semua soal dan riwayat pengerjaan ikut terhapus.') }}"
                                        class="p-2 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer"
                                        aria-label="Delete">
                                        <x-lucide-trash-2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-ink-muted">
                                {{ __('Belum ada kuis. Buat kuis pertamamu!') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quizzes->hasPages())
            <div class="px-5 py-4 border-t border-black/5">
                {{ $quizzes->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- MODAL FORM CREATE/EDIT --}}
    <x-ui.modal wire:model="showForm" title="{{ $editingId ? __('Edit Kuis') : __('Kuis Baru') }}">
        <form wire:submit="save" class="space-y-5">

            <x-ui.input label="{{ __('Judul Kuis') }}" name="title" wire:model="title"
                placeholder="{{ __('Contoh: Try Out Akbar SKD CPNS #1') }}" />

            <x-ui.textarea label="{{ __('Deskripsi') }}" name="description" wire:model="description"
                placeholder="{{ __('Deskripsi singkat kuis (opsional)') }}" />

            <x-ui.input label="{{ __('Durasi (menit)') }}" name="durationMinutes" type="number"
                wire:model="durationMinutes" min="1" max="600" />

            {{-- DISTRIBUSI KE PAKET (morph pivot package_content) --}}
            <div>
                <p class="block text-sm font-semibold text-secondary mb-1.5">{{ __('Distribusikan ke Paket') }}</p>
                <div class="space-y-2 max-h-44 overflow-y-auto p-3 rounded-xl bg-surface-tint">
                    @forelse ($this->packageOptions as $package)
                        <label class="flex items-center gap-2.5 cursor-pointer select-none"
                            wire:key="pkg-option-{{ $package->id }}">
                            <input type="checkbox" value="{{ $package->id }}" wire:model="packageIds"
                                class="w-4 h-4 rounded accent-[#F5872A]">
                            <span class="text-sm text-ink">{{ $package->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-ink-muted">{{ __('Belum ada paket — buat paket dulu di menu Paket.') }}</p>
                    @endforelse
                </div>
                @error('packageIds')
                    <p class="text-sm text-bad mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? __('Simpan Perubahan') : __('Buat Kuis') }}</span>
                    <span wire:loading wire:target="save">{{ __('Menyimpan...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
