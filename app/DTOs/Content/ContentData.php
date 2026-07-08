<?php

namespace App\DTOs\Content;

class ContentData
{
    public function __construct(
        public string $title,
        public string $type,          // text | pdf | video
        public ?string $body,         // type=text
        public ?string $filePath,     // type=pdf (path hasil upload, null = pertahankan file lama)
        public ?string $videoUrl,     // type=video
    ) {}
}
