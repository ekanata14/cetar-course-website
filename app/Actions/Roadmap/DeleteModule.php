<?php

namespace App\Actions\Roadmap;

use App\Models\PackageModule;
use Illuminate\Support\Facades\DB;

class DeleteModule
{
    /** Hapus modul (item + progress ikut via FK cascade) lalu rapatkan urutan modul tersisa */
    public function execute(PackageModule $module): void
    {
        DB::transaction(function () use ($module) {
            $package = $module->package;
            $module->delete();

            foreach ($package->modules()->get()->values() as $index => $remaining) {
                $remaining->update(['order' => $index + 1]);
            }
        });
    }
}
