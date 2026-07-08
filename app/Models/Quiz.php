<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'duration_minutes'];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(UserQuizAttempt::class);
    }

    /** Penempatan kuis ini di roadmap belajar */
    public function roadmapItems(): MorphMany
    {
        return $this->morphMany(RoadmapItem::class, 'contentable');
    }

    /** ID paket yang memuat kuis ini via roadmap (untuk gerbang akses) */
    public function packageIds(): Collection
    {
        return PackageModule::whereIn('id', $this->roadmapItems()->pluck('module_id'))
            ->pluck('package_id')
            ->unique()
            ->values();
    }
}
