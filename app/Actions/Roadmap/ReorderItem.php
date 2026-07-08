<?php

namespace App\Actions\Roadmap;

use App\Models\RoadmapItem;
use Illuminate\Support\Facades\DB;

class ReorderItem
{
    /** Geser item satu posisi ke atas/bawah di dalam modulnya dengan menukar `order` */
    public function execute(RoadmapItem $item, string $direction): void
    {
        $neighbor = $item->module->items()
            ->when(
                $direction === 'up',
                fn ($q) => $q->where('order', '<', $item->order)->reorder()->orderByDesc('order'),
                fn ($q) => $q->where('order', '>', $item->order)->reorder()->orderBy('order'),
            )
            ->first();

        if (! $neighbor) {
            return; // Sudah paling atas/bawah
        }

        DB::transaction(function () use ($item, $neighbor) {
            [$a, $b] = [$item->order, $neighbor->order];
            $item->update(['order' => $b]);
            $neighbor->update(['order' => $a]);
        });
    }
}
