<?php

namespace App\DTOs\Quiz;

class QuestionData
{
    public function __construct(
        public ?string $section,
        public ?string $passage,
        public string $text,
        public string $optionA,
        public string $optionB,
        public string $optionC,
        public string $optionD,
        public ?string $optionE,
        public string $correctAnswer,
        public int $points,
        public ?string $explanation,
        public ?string $imageUrl = null,
    ) {}

    /**
     * Bentuk array kolom database (dipakai create & update).
     *
     * @return array<string, mixed>
     */
    public function toColumns(): array
    {
        return [
            'section' => $this->section,
            'passage' => $this->passage,
            'image_url' => $this->imageUrl,
            'text' => $this->text,
            'option_a' => $this->optionA,
            'option_b' => $this->optionB,
            'option_c' => $this->optionC,
            'option_d' => $this->optionD,
            'option_e' => $this->optionE,
            'correct_answer' => $this->correctAnswer,
            'points' => $this->points,
            'explanation' => $this->explanation,
        ];
    }
}
