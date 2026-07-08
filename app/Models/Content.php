<?php

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Content extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'body', 'file_path', 'video_url'];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
        ];
    }

    /** Penempatan materi ini di roadmap (bisa dipakai lebih dari satu modul) */
    public function roadmapItems(): MorphMany
    {
        return $this->morphMany(RoadmapItem::class, 'contentable');
    }

    /** URL embed YouTube dari berbagai format tautan (watch, embed, youtu.be) */
    public function videoEmbedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/', $this->video_url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return $this->video_url;
    }
}
