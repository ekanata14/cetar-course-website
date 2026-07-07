<?php

namespace App\Actions\Quiz;

use App\DTOs\Quiz\QuizData;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class CreateQuiz
{
    public function execute(QuizData $data): Quiz
    {
        return DB::transaction(function () use ($data) {
            $quiz = Quiz::create([
                'title' => $data->title,
                'description' => $data->description,
                'duration_minutes' => $data->durationMinutes,
            ]);

            // Distribusikan ke paket-paket terpilih via morph pivot package_content
            $quiz->packages()->sync($data->packageIds);

            return $quiz;
        });
    }
}
