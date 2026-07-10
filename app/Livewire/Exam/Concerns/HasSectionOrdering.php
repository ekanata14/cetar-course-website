<?php

namespace App\Livewire\Exam\Concerns;

use Illuminate\Support\Collection;

/**
 * Urutan & label section CBT (TWK/TIU/TKP dst.) dipakai bersama oleh
 * QuizEngine (ruang ujian) dan QuizResult (halaman hasil) agar tab & rekap
 * selalu konsisten.
 */
trait HasSectionOrdering
{
    /** Prioritas tampil section baku SKD CPNS. */
    private const SECTION_PRIORITY = ['TWK' => 0, 'TIU' => 1, 'TKP' => 2];

    /** Kunci tab untuk soal tanpa section (section = null). */
    public const GENERAL_SECTION = 'UMUM';

    /** Petakan section mentah (nullable) ke kunci tab yang stabil. */
    public function sectionKey(?string $section): string
    {
        return $section !== null && $section !== '' ? $section : self::GENERAL_SECTION;
    }

    /**
     * Urutkan kunci section: TWK → TIU → TKP → section lain (A-Z) → UMUM.
     *
     * @param  Collection<int, string>  $keys
     * @return Collection<int, string>
     */
    protected function orderSections(Collection $keys): Collection
    {
        return $keys->unique()->sortBy(fn (string $key) => match (true) {
            $key === self::GENERAL_SECTION => [3, ''],
            isset(self::SECTION_PRIORITY[$key]) => [0, self::SECTION_PRIORITY[$key]],
            default => [1, $key],
        })->values();
    }

    /** Label lengkap untuk tab section (fallback ke kode mentah). */
    public function sectionLabel(string $key): string
    {
        return match ($key) {
            'TWK' => 'Tes Wawasan Kebangsaan (TWK)',
            'TIU' => 'Tes Intelegensi Umum (TIU)',
            'TKP' => 'Tes Karakteristik Pribadi (TKP)',
            self::GENERAL_SECTION => __('Umum'),
            default => $key,
        };
    }
}
