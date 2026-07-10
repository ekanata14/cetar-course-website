<?php

namespace App\Console\Commands;

use App\Models\PackageModule;
use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ImportQuestionFile extends Command
{
    protected $signature = 'questions:import
        {json : Path file JSON hasil scripts/build_questions.py}
        {--quiz-title= : Judul kuis yang dibuat (wajib)}
        {--slug= : Slug folder gambar di /storage/questions/{slug}}
        {--duration=100 : Durasi kuis dalam menit}
        {--module= : ID PackageModule untuk menempelkan kuis ke roadmap (opsional)}';

    protected $description = 'Impor soal dari JSON ke kuis baru + arsip xlsx dalam format template';

    public function handle(): int
    {
        $path = $this->argument('json');
        $title = $this->option('quiz-title');

        if (! $title) {
            $this->error('Opsi --quiz-title wajib diisi.');

            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($path), true);

        if (! is_array($rows) || $rows === []) {
            $this->error('JSON kosong atau tidak valid.');

            return self::FAILURE;
        }

        // Idempoten: kuis dengan judul sama dianggap sudah diimpor
        if (Quiz::where('title', $title)->exists()) {
            $this->warn("'{$title}' sudah ada — dilewati.");

            return self::SUCCESS;
        }

        $slug = $this->option('slug');

        $quiz = DB::transaction(function () use ($rows, $title, $slug) {
            $quiz = Quiz::create([
                'title' => $title,
                'description' => 'Latihan soal '.$title.' — dilengkapi pembahasan.',
                'duration_minutes' => (int) $this->option('duration'),
            ]);

            foreach ($rows as $row) {
                $quiz->questions()->create([
                    'section' => $row['section'] ?? null,
                    'passage' => ($row['passage'] ?? null) ?: null,
                    'image_url' => $this->resolveImageValue($row['image'] ?? null, $slug),
                    'text' => $row['text'],
                    'option_a' => $this->resolveImageValue($row['option_a'], $slug),
                    'option_b' => $this->resolveImageValue($row['option_b'], $slug),
                    'option_c' => $this->resolveImageValue($row['option_c'], $slug),
                    'option_d' => $this->resolveImageValue($row['option_d'], $slug),
                    'option_e' => $this->resolveImageValue(($row['option_e'] ?? null) ?: null, $slug),
                    'correct_answer' => strtoupper($row['correct_answer']),
                    'points' => 5,
                    'explanation' => ($row['explanation'] ?? null) ?: null,
                ]);
            }

            if ($moduleId = $this->option('module')) {
                $module = PackageModule::findOrFail($moduleId);
                $module->items()->create([
                    'contentable_type' => 'quiz',
                    'contentable_id' => $quiz->id,
                    'order' => ($module->items()->max('order') ?? 0) + 1,
                    'is_locked_by_default' => false, // bank try out: bebas dikerjakan
                ]);
            }

            return $quiz;
        });

        $this->writeXlsxArchive($quiz, $rows, $slug);

        $this->info("'{$quiz->title}': {$quiz->questions()->count()} soal diimpor (quiz id {$quiz->id}).");

        return self::SUCCESS;
    }

    /**
     * Ekspansi nilai gambar: nama file gambar polos (mis. "img2.png") menjadi
     * path storage "/storage/questions/{slug}/img2.png". Dipakai untuk field
     * image maupun opsi jawaban bergambar (soal figural TIU). Teks biasa
     * (kalimat opsi) tidak cocok regex → dikembalikan apa adanya.
     */
    private function resolveImageValue(?string $value, ?string $slug): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($slug && preg_match('/^[\w.\-]+\.(png|jpe?g|gif|webp)$/i', $value)) {
            return "/storage/questions/{$slug}/{$value}";
        }

        return $value;
    }

    /** Arsip xlsx dalam format template impor — catatan permanen untuk admin */
    private function writeXlsxArchive(Quiz $quiz, array $rows, ?string $slug): void
    {
        $dir = storage_path('app/question-import/xlsx');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer = SimpleExcelWriter::create($dir.'/'.($slug ?: 'quiz-'.$quiz->id).'.xlsx');

        foreach ($rows as $row) {
            $writer->addRow([
                'section' => $row['section'] ?? '',
                'passage' => $row['passage'] ?? '',
                'text' => $row['text'],
                'option_a' => $this->resolveImageValue($row['option_a'], $slug),
                'option_b' => $this->resolveImageValue($row['option_b'], $slug),
                'option_c' => $this->resolveImageValue($row['option_c'], $slug),
                'option_d' => $this->resolveImageValue($row['option_d'], $slug),
                'option_e' => $this->resolveImageValue($row['option_e'] ?? '', $slug) ?? '',
                'correct_answer' => strtoupper($row['correct_answer']),
                'points' => 5,
                'explanation' => $row['explanation'] ?? '',
                'image_url' => $this->resolveImageValue($row['image'] ?? null, $slug) ?? '',
            ]);
        }

        $writer->close();
    }
}
