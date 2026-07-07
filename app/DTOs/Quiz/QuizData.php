<?php

namespace App\DTOs\Quiz;

class QuizData
{
    /**
     * @param  array<int, int>  $packageIds  Paket yang mendistribusikan kuis ini (morph pivot package_content)
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public int $durationMinutes,
        public array $packageIds = [],
    ) {}
}
