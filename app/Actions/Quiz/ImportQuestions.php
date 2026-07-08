<?php

namespace App\Actions\Quiz;

use App\DTOs\Quiz\ImportResult;
use App\DTOs\Quiz\QuestionData;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportQuestions
{
    /**
     * Impor soal dari file Excel/CSV template ke sebuah kuis.
     *
     * Aturan all-or-nothing: satu baris saja tidak valid → seluruh impor
     * dibatalkan, agar kuis tidak terisi setengah dan admin bisa memperbaiki
     * file lalu mengunggah ulang.
     */
    public function execute(Quiz $quiz, string $path): ImportResult
    {
        $rows = SimpleExcelReader::create($path)->getRows()->values();

        if ($rows->isEmpty()) {
            return new ImportResult(0, ['File tidak berisi baris soal.']);
        }

        $errors = [];
        $dataObjects = [];

        foreach ($rows as $index => $row) {
            // Baris 1 = header, jadi baris data pertama = baris 2 di Excel
            $rowNumber = $index + 2;

            $normalized = $this->normalizeRow($row);

            $validator = Validator::make($normalized, $this->rules($normalized), [], $this->attributes());

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Baris {$rowNumber}: {$message}";
                }

                continue;
            }

            $dataObjects[] = new QuestionData(
                section: $normalized['section'] !== '' ? strtoupper($normalized['section']) : null,
                passage: $normalized['passage'] !== '' ? $normalized['passage'] : null,
                text: $normalized['text'],
                optionA: $normalized['option_a'],
                optionB: $normalized['option_b'],
                optionC: $normalized['option_c'],
                optionD: $normalized['option_d'],
                optionE: $normalized['option_e'] !== '' ? $normalized['option_e'] : null,
                correctAnswer: strtoupper($normalized['correct_answer']),
                points: (int) $normalized['points'],
                explanation: $normalized['explanation'] !== '' ? $normalized['explanation'] : null,
                imageUrl: $normalized['image_url'] !== '' ? $normalized['image_url'] : null,
            );
        }

        if ($errors !== []) {
            return new ImportResult(0, $errors);
        }

        DB::transaction(function () use ($quiz, $dataObjects) {
            foreach ($dataObjects as $data) {
                $quiz->questions()->create($data->toColumns());
            }
        });

        return new ImportResult(count($dataObjects), []);
    }

    /**
     * Samakan bentuk baris: semua kolom template hadir sebagai string ter-trim.
     *
     * @return array<string, string>
     */
    private function normalizeRow(array $row): array
    {
        $columns = [
            'section', 'passage', 'text', 'option_a', 'option_b', 'option_c',
            'option_d', 'option_e', 'correct_answer', 'points', 'explanation', 'image_url',
        ];

        $normalized = [];

        foreach ($columns as $column) {
            $normalized[$column] = trim((string) ($row[$column] ?? ''));
        }

        // Poin kosong → default 5
        if ($normalized['points'] === '') {
            $normalized['points'] = '5';
        }

        return $normalized;
    }

    /** Aturan validasi per baris — cermin form Kelola Soal */
    private function rules(array $normalized): array
    {
        return [
            'section' => 'nullable|string|max:50',
            'passage' => 'nullable|string',
            'text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'option_e' => 'nullable|string',
            // Opsi E kosong → jawaban benar tidak boleh E
            'correct_answer' => $normalized['option_e'] === ''
                ? 'required|in:A,B,C,D,a,b,c,d'
                : 'required|in:A,B,C,D,E,a,b,c,d,e',
            'points' => 'required|integer|min:0|max:100',
            'explanation' => 'nullable|string',
            'image_url' => 'nullable|url|max:2048',
        ];
    }

    private function attributes(): array
    {
        return [
            'text' => 'pertanyaan (text)',
            'option_a' => 'opsi A (option_a)',
            'option_b' => 'opsi B (option_b)',
            'option_c' => 'opsi C (option_c)',
            'option_d' => 'opsi D (option_d)',
            'correct_answer' => 'jawaban benar (correct_answer)',
            'points' => 'poin (points)',
            'image_url' => 'URL gambar (image_url)',
        ];
    }
}
