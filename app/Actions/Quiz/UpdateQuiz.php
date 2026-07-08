<?php

namespace App\Actions\Quiz;

use App\DTOs\Quiz\QuizData;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class UpdateQuiz
{
    public function execute(Quiz $quiz, QuizData $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data) {
            $quiz->update([
                'title' => $data->title,
                'description' => $data->description,
                'duration_minutes' => $data->durationMinutes,
            ]);

            return $quiz->refresh();
        });
    }
}
