<?php

namespace App\Actions\Quiz;

use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class DeleteQuiz
{
    public function execute(Quiz $quiz): void
    {
        DB::transaction(function () use ($quiz) {
            // Item roadmap yang menunjuk kuis ini dilepas manual (morph tanpa FK cascade ke quizzes);
            // user_progress ikut terhapus via FK cascade pada roadmap_item_id
            $quiz->roadmapItems()->delete();

            // Questions, attempts, dan answers ikut terhapus via FK cascade
            $quiz->delete();
        });
    }
}
