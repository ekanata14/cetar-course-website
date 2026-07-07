<?php

namespace App\Actions\Quiz;

use App\Enums\AttemptStatus;
use App\Models\Question;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizAttempt;

class SaveUserAnswer
{
    /**
     * Auto-save satu jawaban (dipanggil setiap user memilih opsi / toggle ragu-ragu).
     * Return false (tanpa exception) jika tidak boleh disimpan — UI tetap jalan,
     * submit final tetap menghitung dari data yang tersimpan.
     */
    public function execute(
        UserQuizAttempt $attempt,
        int $questionId,
        ?string $selectedOption,
        bool $isDoubtful = false,
    ): bool {
        // Guard: attempt harus milik user yang sedang login (defense-in-depth
        // di luar proteksi serialisasi Livewire)
        if (auth()->check() && $attempt->user_id !== auth()->id()) {
            return false;
        }

        // Guard: hanya attempt yang masih berjalan
        if ($attempt->status !== AttemptStatus::InProgress) {
            return false;
        }

        // Guard: waktu habis (grace 10 detik untuk toleransi latensi klik terakhir)
        if ($attempt->isExpired(graceSeconds: 10)) {
            return false;
        }

        // Guard: soal harus milik kuis yang sedang dikerjakan
        $belongsToQuiz = Question::where('id', $questionId)
            ->where('quiz_id', $attempt->quiz_id)
            ->exists();

        if (! $belongsToQuiz) {
            return false;
        }

        // Upsert aman berkat unique(attempt_id, question_id)
        UserQuizAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
            ['selected_option' => $selectedOption, 'is_doubtful' => $isDoubtful],
        );

        return true;
    }
}
