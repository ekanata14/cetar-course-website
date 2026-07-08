<?php

namespace App\Actions\Roadmap;

use App\Models\RoadmapItem;
use Illuminate\Support\Facades\DB;

class RemoveRoadmapItem
{
    /** Lepas item dari modul (progress ikut via FK cascade) lalu rapatkan urutan item tersisa */
    public function execute(RoadmapItem $item): void
    {
        DB::transaction(function () use ($item) {
            $module = $item->module;
            $item->delete();

            foreach ($module->items()->get()->values() as $index => $remaining) {
                $remaining->update(['order' => $index + 1]);
            }
        });
    }
}
