<?php

namespace App\Actions\Roadmap;

use App\Models\RoadmapItem;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Auth\Access\AuthorizationException;

class MarkItemComplete
{
    public function __construct(
        private ResolveJourney $journey,
    ) {}

    /**
     * Tandai satu item roadmap selesai untuk user.
     * Idempotent — completed_at pertama dipertahankan saat dipanggil ulang.
     *
     * @throws AuthorizationException user tanpa langganan aktif atau item masih terkunci
     */
    public function execute(User $user, RoadmapItem $item): UserProgress
    {
        if (! $user->hasActiveSubscription($item->module->package_id)) {
            throw new AuthorizationException('Kamu belum berlangganan paket yang memuat materi ini.');
        }

        if (! $this->journey->isUnlocked($user, $item)) {
            throw new AuthorizationException('Selesaikan materi sebelumnya terlebih dahulu.');
        }

        $progress = UserProgress::firstOrCreate(
            ['user_id' => $user->id, 'roadmap_item_id' => $item->id],
            ['is_completed' => true, 'completed_at' => now()],
        );

        if (! $progress->is_completed) {
            $progress->update(['is_completed' => true, 'completed_at' => now()]);
        }

        return $progress;
    }
}
