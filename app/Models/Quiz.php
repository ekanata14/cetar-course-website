<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    /** Paket-paket yang mendistribusikan kuis ini via pivot polimorfik `package_content` */
    public function packages(): MorphToMany
    {
        return $this->morphToMany(Package::class, 'contentable', 'package_content')->withTimestamps();
    }
}
