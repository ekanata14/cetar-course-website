<?php

namespace App\Actions\Quiz;

use App\Models\Question;

class DeleteQuestion
{
    public function execute(Question $question): void
    {
        // Jawaban user atas soal ini ikut terhapus via FK cascade
        $question->delete();
    }
}
