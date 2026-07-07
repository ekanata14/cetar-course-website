<?php

namespace App\Actions\Quiz;

use App\Enums\AttemptStatus;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserQuizAttempt;

class StartQuizAttempt
{
    /**
     * Mulai attempt baru, atau lanjutkan attempt in_progress yang sudah ada
     * (refresh halaman / koneksi putus tidak mereset timer — started_at tetap).
     */
    public function execute(User $user, Quiz $quiz): UserQuizAttempt
    {
        $existing = $user->quizAttempts()
            ->inProgress()
            ->where('quiz_id', $quiz->id)
            ->latest('started_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $user->quizAttempts()->create([
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'status' => AttemptStatus::InProgress,
        ]);
    }
}
