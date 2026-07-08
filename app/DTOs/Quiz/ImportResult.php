<?php

namespace App\DTOs\Quiz;

class ImportResult
{
    /**
     * @param  int  $imported  Jumlah soal yang berhasil disimpan
     * @param  array<int, string>  $errors  Pesan error per baris ("Baris N: ...")
     */
    public function __construct(
        public int $imported,
        public array $errors,
    ) {}

    public function failed(): bool
    {
        return $this->errors !== [];
    }
}
