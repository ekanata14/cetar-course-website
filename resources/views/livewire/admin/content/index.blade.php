<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Kelola Materi') }}</h1>
        </div>
        <x-ui.button wire:click="openCreate">
            <x-lucide-plus class="w-4 h-4" /> {{ __('Materi Baru') }}
        </x-ui.button>
    </div>

    {{-- SEARCH --}}
    <div class="max-w-sm">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari materi...') }}" />
    </div>

    {{-- TABEL MATERI --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">
                        <th class="px-5 py-3.5">{{ __('Materi') }}</th>
                        <th class="px-5 py-3.5">{{ __('Tipe') }}</th>
                        <th class="px-5 py-3.5">{{ __('Paket') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse ($contents as $content)
                        <tr wire:key="content-{{ $content->id }}" class="hover:bg-surface-tint transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-bold text-secondary">{{ $content->title }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $typeStyle = match ($content->type->value) {
                                        'text' => 'bg-secondary/10 text-secondary',
                                        'pdf' => 'bg-bad-soft text-bad',
                                        'video' => 'bg-primary/10 text-primary-dark',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $typeStyle }}">
                                    @if ($content->type->value === 'text') <x-lucide-file-text class="w-3 h-3" />
                                    @elseif ($content->type->value === 'pdf') <x-lucide-file class="w-3 h-3" />
                                    @else <x-lucide-play-circle class="w-3 h-3" /> @endif
                                    {{ $content->type->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1 max-w-[220px]">
                                    @forelse ($content->roadmapItems->pluck('module.package')->filter()->unique('id') as $package)
                                        <span class="text-[11px] font-semibold text-primary-dark bg-primary/10 px-2 py-0.5 rounded-full">
                                            {{ $package->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-ink-faint">{{ __('Belum masuk roadmap') }}</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" wire:click="openEdit({{ $content->id }})"
                                        class="p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer"
                                        aria-label="Edit">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="delete({{ $content->id }})"
                                        wire:confirm="{{ __('Hapus materi ini? Materi juga akan dilepas dari semua roadmap.') }}"
                                        class="p-2 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer"
                                        aria-label="Delete">
                                        <x-lucide-trash-2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-ink-muted">
                                {{ __('Belum ada materi. Buat materi pertamamu!') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contents->hasPages())
            <div class="px-5 py-4 border-t border-black/5">
                {{ $contents->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- MODAL FORM CREATE/EDIT --}}
    <x-ui.modal wire:model="showForm" title="{{ $editingId ? __('Edit Materi') : __('Materi Baru') }}">
        <form wire:submit="save" class="space-y-5">

            <x-ui.input label="{{ __('Judul Materi') }}" name="title" wire:model="title"
                placeholder="{{ __('Contoh: Pengantar TWK — Pilar Negara') }}" />

            <x-ui.select label="{{ __('Tipe Materi') }}" name="type" wire:model.live="type">
                <option value="text">{{ __('Materi Teks') }}</option>
                <option value="pdf">{{ __('Dokumen PDF') }}</option>
                <option value="video">{{ __('Video') }}</option>
            </x-ui.select>

            {{-- FIELD KONDISIONAL SESUAI TIPE --}}
            @if ($type === 'text')
                <x-ui.textarea label="{{ __('Isi Materi') }}" name="body" wire:model="body" rows="8"
                    placeholder="{{ __('Tulis isi materi di sini...') }}" />
            @elseif ($type === 'pdf')
                <div>
                    <label class="block text-sm font-semibold text-secondary mb-1.5">{{ __('File PDF') }}</label>
                    <input type="file" accept="application/pdf" wire:model="pdfFile"
                        class="w-full text-sm text-ink file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary-dark file:font-semibold file:cursor-pointer cursor-pointer" />
                    <div wire:loading wire:target="pdfFile" class="text-xs text-ink-muted mt-1.5">{{ __('Mengunggah...') }}</div>
                    @if ($editingId && ! $pdfFile)
                        <p class="text-xs text-ink-muted mt-1.5">{{ __('Kosongkan untuk mempertahankan file yang ada.') }}</p>
                    @endif
                    @error('pdfFile')
                        <p class="text-sm text-bad mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <x-ui.input label="{{ __('URL Video (YouTube)') }}" name="videoUrl" wire:model="videoUrl"
                    placeholder="https://www.youtube.com/watch?v=..." />
            @endif

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? __('Simpan Perubahan') : __('Buat Materi') }}</span>
                    <span wire:loading wire:target="save">{{ __('Menyimpan...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
