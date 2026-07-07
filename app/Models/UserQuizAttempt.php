<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'quiz_id', 'started_at', 'completed_at', 'score', 'status'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'integer',
            'status' => AttemptStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(UserQuizAnswer::class, 'attempt_id');
    }

    /** Attempt yang sedang berjalan (untuk resume ujian) */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', AttemptStatus::InProgress);
    }

    /** Batas waktu absolut ujian: started_at + durasi kuis (patokan timer Alpine & guard server) */
    public function deadline(): \Carbon\CarbonInterface
    {
        return $this->started_at->addMinutes($this->quiz->duration_minutes);
    }

    /** Apakah waktu ujian sudah habis (grace untuk toleransi latensi auto-save) */
    public function isExpired(int $graceSeconds = 0): bool
    {
        return now()->greaterThan($this->deadline()->addSeconds($graceSeconds));
    }
}
