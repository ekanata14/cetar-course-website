<?php

namespace App\Actions\Package;

use App\DTOs\Package\PackageData;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePackage
{
    public function execute(PackageData $data): Package
    {
        // Paket + plans disimpan atomik: gagal salah satu, batal semua
        return DB::transaction(function () use ($data) {
            $package = Package::create([
                'name' => $data->name,
                'slug' => $this->generateUniqueSlug($data->name),
                'description' => $data->description,
                'is_active' => $data->isActive,
            ]);

            $package->plans()->createMany($data->plans);

            return $package;
        });
    }

    /**
     * Slug unik dari nama paket; jika bentrok, tambahkan suffix angka (cpns, cpns-2, ...).
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Package::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
