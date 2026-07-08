<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Tier harga (1 Bulan, 1 Tahun, dst.) */
    public function plans(): HasMany
    {
        return $this->hasMany(PackagePlan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /** Modul roadmap belajar, sesuai urutan */
    public function modules(): HasMany
    {
        return $this->hasMany(PackageModule::class)->orderBy('order');
    }

    /** Seluruh item roadmap paket ini (lintas modul) */
    public function roadmapItems(): HasManyThrough
    {
        return $this->hasManyThrough(RoadmapItem::class, PackageModule::class, 'package_id', 'module_id');
    }

    /** Item roadmap bertipe kuis — dipakai untuk `withCount(['quizItems as quizzes_count'])` */
    public function quizItems(): HasManyThrough
    {
        return $this->roadmapItems()->where('contentable_type', 'quiz');
    }

    /** Query kuis yang terdaftar di roadmap paket ini (pengganti relasi pivot lama) */
    public function quizzes(): Builder
    {
        return Quiz::whereIn('id', $this->quizItems()->pluck('roadmap_items.contentable_id'));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
