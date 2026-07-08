<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'section',
        'passage',
        'image_url',
        'text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_answer',
        'points',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * URL gambar siap-embed. Link share Google Drive (file/d/{id}, open?id=,
     * uc?id=) dinormalisasi ke host embed lh3; URL lain diteruskan apa adanya.
     */
    public function imageDisplayUrl(): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?(?:export=\w+&)?id=)([\w-]+)/', $this->image_url, $matches)) {
            return 'https://lh3.googleusercontent.com/d/'.$matches[1];
        }

        return $this->image_url;
    }
}
