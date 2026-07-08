<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoadmapItem extends Model
{
    use HasFactory;

    protected $fillable = ['module_id', 'contentable_type', 'contentable_id', 'order', 'is_locked_by_default'];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_locked_by_default' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(PackageModule::class, 'module_id');
    }

    /** Konten yang ditunjuk item ini: Content (materi) atau Quiz (try out) */
    public function contentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserProgress::class);
    }

    /** Apakah item ini menunjuk sebuah kuis (try out)? */
    public function isQuiz(): bool
    {
        return $this->contentable_type === 'quiz';
    }
}
