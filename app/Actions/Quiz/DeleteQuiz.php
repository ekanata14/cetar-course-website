<?php

namespace App\Actions\Quiz;

use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class DeleteQuiz
{
    public function execute(Quiz $quiz): void
    {
        DB::transaction(function () use ($quiz) {
            // Baris pivot package_content harus dilepas manual (morph pivot tanpa FK cascade ke quizzes)
            $quiz->packages()->detach();

            // Questions, attempts, dan answers ikut terhapus via FK cascade
            $quiz->delete();
        });
    }
}
