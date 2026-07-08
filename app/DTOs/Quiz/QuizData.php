<?php

namespace App\DTOs\Quiz;

class QuizData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $durationMinutes,
    ) {}
}
