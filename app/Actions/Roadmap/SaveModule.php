<?php

namespace App\Actions\Roadmap;

use App\Models\Package;
use App\Models\PackageModule;

class SaveModule
{
    /** Buat modul baru (order otomatis di akhir) atau ganti judul modul yang ada */
    public function execute(Package $package, ?int $moduleId, string $title): PackageModule
    {
        if ($moduleId) {
            $module = $package->modules()->findOrFail($moduleId);
            $module->update(['title' => $title]);

            return $module;
        }

        return $package->modules()->create([
            'title' => $title,
            'order' => ((int) $package->modules()->max('order')) + 1,
        ]);
    }
}
