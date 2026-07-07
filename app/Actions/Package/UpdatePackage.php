<?php

namespace App\Actions\Package;

use App\DTOs\Package\PackageData;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class UpdatePackage
{
    public function execute(Package $package, PackageData $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            // Slug sengaja TIDAK diubah saat rename agar link/pembayaran lama tetap valid
            $package->update([
                'name' => $data->name,
                'description' => $data->description,
                'is_active' => $data->isActive,
            ]);

            $this->syncPlans($package, $data->plans);

            return $package->refresh();
        });
    }

    /**
     * Sinkronisasi plans dari form:
     * - baris dengan id  -> update plan lama
     * - baris tanpa id   -> buat plan baru
     * - plan lama yang tidak ada di form -> hapus
     *
     * @param  array<int, array{id?: int|null, name: string, duration_days: int, price: string|float|int}>  $plans
     */
    private function syncPlans(Package $package, array $plans): void
    {
        $keptIds = [];

        foreach ($plans as $planData) {
            $plan = $package->plans()->updateOrCreate(
                ['id' => $planData['id'] ?? null],
                [
                    'name' => $planData['name'],
                    'duration_days' => $planData['duration_days'],
                    'price' => $planData['price'],
                ],
            );

            $keptIds[] = $plan->id;
        }

        $package->plans()->whereNotIn('id', $keptIds)->delete();
    }
}
