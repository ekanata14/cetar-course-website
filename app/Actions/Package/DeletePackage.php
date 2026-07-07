<?php

namespace App\Actions\Package;

use App\Models\Package;

class DeletePackage
{
    public function execute(Package $package): void
    {
        // Plans, subscriptions, dan baris pivot package_content ikut terhapus via FK cascade
        $package->delete();
    }
}
