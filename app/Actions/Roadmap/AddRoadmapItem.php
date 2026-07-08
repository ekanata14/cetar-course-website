<?php

namespace App\Actions\Roadmap;

use App\Models\PackageModule;
use App\Models\RoadmapItem;

class AddRoadmapItem
{
    /**
     * Tambahkan konten/kuis ke akhir sebuah modul.
     * Idempotent — kombinasi yang sudah ada dikembalikan apa adanya (unique constraint).
     */
    public function execute(PackageModule $module, string $contentableType, int $contentableId, bool $locked = true): RoadmapItem
    {
        return RoadmapItem::firstOrCreate(
            [
                'module_id' => $module->id,
                'contentable_type' => $contentableType, // 'content' | 'quiz' (morph map)
                'contentable_id' => $contentableId,
            ],
            [
                'order' => ((int) $module->items()->max('order')) + 1,
                'is_locked_by_default' => $locked,
            ],
        );
    }
}
