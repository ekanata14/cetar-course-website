<?php

namespace App\Actions\Quiz;

use App\DTOs\Quiz\QuizData;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class CreateQuiz
{
    public function execute(QuizData $data): Quiz
    {
        // Penempatan ke paket dilakukan lewat Roadmap Builder, bukan di sini
        return DB::transaction(function () use ($data) {
            return Quiz::create([
                'title' => $data->title,
                'description' => $data->description,
                'duration_minutes' => $data->durationMinutes,
            ]);
        });
    }
}
