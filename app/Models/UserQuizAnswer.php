<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['attempt_id', 'question_id', 'selected_option', 'is_doubtful', 'is_correct'];

    protected function casts(): array
    {
        return [
            'is_doubtful' => 'boolean',
            'is_correct' => 'boolean', // Nullable: baru diisi saat kalkulasi skor akhir
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(UserQuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
