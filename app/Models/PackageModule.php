<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageModule extends Model
{
    use HasFactory;

    protected $fillable = ['package_id', 'title', 'order'];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** Item roadmap dalam modul ini, sesuai urutan belajar */
    public function items(): HasMany
    {
        return $this->hasMany(RoadmapItem::class, 'module_id')->orderBy('order');
    }
}
