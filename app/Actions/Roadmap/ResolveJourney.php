<?php

namespace App\Actions\Roadmap;

use App\Models\Package;
use App\Models\RoadmapItem;
use App\Models\User;
use Illuminate\Support\Collection;

class ResolveJourney
{
    /**
     * Muat roadmap sebuah paket beserta status per item untuk seorang user.
     *
     * Setiap item didekorasi dua atribut dinamis:
     *  - is_completed : user sudah menyelesaikan item ini
     *  - is_unlocked  : item boleh diakses (aturan gerbang sekuensial)
     *
     * Aturan gerbang: item terbuka jika TIDAK dikunci default, ATAU merupakan item
     * pertama dalam urutan (modul demi modul), ATAU item tepat sebelumnya selesai.
     */
    public function execute(User $user, Package $package): Collection
    {
        // Satu query eager: modul + item + konten + progress milik user ini saja (hindari N+1)
        $modules = $package->modules()->with([
            'items.contentable',
            'items.progress' => fn ($query) => $query->where('user_id', $user->id),
        ])->get();

        $previousCompleted = true; // Item pertama dalam perjalanan selalu terbuka

        foreach ($modules as $module) {
            $completedCount = 0;

            foreach ($module->items as $item) {
                $completed = (bool) ($item->progress->first()?->is_completed);
                $unlocked = ! $item->is_locked_by_default || $previousCompleted;

                $item->setAttribute('is_completed', $completed);
                $item->setAttribute('is_unlocked', $unlocked);

                $previousCompleted = $completed;
                $completedCount += $completed ? 1 : 0;
            }

            $total = $module->items->count();
            $module->setAttribute('progress_percent', $total > 0 ? (int) round($completedCount / $total * 100) : 0);
        }

        return $modules;
    }

    /** Cek cepat apakah satu item roadmap terbuka untuk user (memakai aturan yang sama) */
    public function isUnlocked(User $user, RoadmapItem $item): bool
    {
        $modules = $this->execute($user, $item->module->package);

        return (bool) $modules
            ->flatMap(fn ($module) => $module->items)
            ->firstWhere('id', $item->id)
            ?->is_unlocked;
    }
}
