<?php

namespace App\Actions\Quiz;

use App\Models\UserQuizAttempt;

class CalculateFinalScore
{
    /**
     * Hitung skor akhir attempt:
     * - tandai is_correct pada tiap jawaban (optimalisasi read untuk halaman review)
     * - skor = total poin soal yang dijawab benar
     */
    public function execute(UserQuizAttempt $attempt): int
    {
        // Kunci jawaban di-keyBy id untuk lookup O(1)
        $questions = $attempt->quiz->questions()
            ->get(['id', 'correct_answer', 'points'])
            ->keyBy('id');

        $score = 0;

        foreach ($attempt->answers()->get() as $answer) {
            $question = $questions->get($answer->question_id);

            if (! $question) {
                continue; // Soal sudah dihapus admin — abaikan
            }

            // Belum memilih opsi = tidak dinilai (is_correct tetap null)
            if ($answer->selected_option === null) {
                continue;
            }

            $isCorrect = $answer->selected_option === $question->correct_answer;
            $answer->update(['is_correct' => $isCorrect]);

            if ($isCorrect) {
                $score += $question->points;
            }
        }

        return $score;
    }
}
