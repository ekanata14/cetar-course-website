<?php

namespace App\Actions\Quiz;

use App\Actions\Roadmap\CompleteQuizRoadmapItems;
use App\Enums\AttemptStatus;
use App\Models\UserQuizAttempt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class SubmitQuizAttempt
{
    public function __construct(
        private CalculateFinalScore $calculateFinalScore,
        private CompleteQuizRoadmapItems $completeRoadmapItems,
    ) {}

    /**
     * Finalisasi attempt: hitung skor, tandai completed.
     * Idempotent — dipanggil dua kali (submit manual + auto-submit timer) tidak masalah.
     */
    public function execute(UserQuizAttempt $attempt): UserQuizAttempt
    {
        // Guard: attempt harus milik user yang sedang login (defense-in-depth)
        if (auth()->check() && $attempt->user_id !== auth()->id()) {
            throw new AuthorizationException('Attempt bukan milik user ini.');
        }

        if ($attempt->status === AttemptStatus::Completed) {
            return $attempt;
        }

        $attempt = DB::transaction(function () use ($attempt) {
            $score = $this->calculateFinalScore->execute($attempt);

            $attempt->update([
                'score' => $score,
                'completed_at' => now(),
                'status' => AttemptStatus::Completed,
            ]);

            return $attempt->refresh();
        });

        // Buka gerbang roadmap: item yang menunjuk kuis ini ditandai selesai
        $this->completeRoadmapItems->execute($attempt->user, $attempt->quiz);

        return $attempt;
    }
}
