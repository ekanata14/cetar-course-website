<?php

namespace App\Actions\Roadmap;

use App\Models\PackageModule;
use Illuminate\Support\Facades\DB;

class ReorderModule
{
    /** Geser modul satu posisi ke atas/bawah dengan menukar `order` bersama tetangganya */
    public function execute(PackageModule $module, string $direction): void
    {
        $neighbor = $module->package->modules()
            ->when(
                $direction === 'up',
                fn ($q) => $q->where('order', '<', $module->order)->reorder()->orderByDesc('order'),
                fn ($q) => $q->where('order', '>', $module->order)->reorder()->orderBy('order'),
            )
            ->first();

        if (! $neighbor) {
            return; // Sudah paling atas/bawah
        }

        DB::transaction(function () use ($module, $neighbor) {
            [$a, $b] = [$module->order, $neighbor->order];
            $module->update(['order' => $b]);
            $neighbor->update(['order' => $a]);
        });
    }
}
