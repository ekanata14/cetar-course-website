<?php

namespace App\Actions\Quiz;

use App\DTOs\Quiz\QuestionData;
use App\Models\Question;
use App\Models\Quiz;

class SaveQuestion
{
    /**
     * Create atau update soal pada sebuah kuis.
     * $question null = buat baru; terisi = update soal tersebut.
     */
    public function execute(Quiz $quiz, QuestionData $data, ?Question $question = null): Question
    {
        if ($question) {
            $question->update($data->toColumns());

            return $question->refresh();
        }

        return $quiz->questions()->create($data->toColumns());
    }
}
