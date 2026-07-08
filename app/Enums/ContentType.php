<?php

namespace App\Enums;

enum ContentType: string
{
    case Text = 'text';
    case Pdf = 'pdf';
    case Video = 'video';

    /** Label tampilan berbahasa Indonesia */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Materi Teks',
            self::Pdf => 'Dokumen PDF',
            self::Video => 'Video',
        };
    }
}
