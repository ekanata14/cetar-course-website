<?php

namespace App\Actions\Quiz;

use App\Actions\Roadmap\ResolveJourney;
use App\Models\Quiz;
use App\Models\User;

class CheckQuizAccess
{
    public function __construct(
        private ResolveJourney $journey,
    ) {}

    /** User memegang langganan aktif pada salah satu paket yang memuat kuis ini */
    public function subscribed(User $user, Quiz $quiz): bool
    {
        return $user->subscriptions()
            ->active()
            ->whereIn('package_id', $quiz->packageIds())
            ->exists();
    }

    /** Kuis terbuka bila minimal satu penempatan roadmap-nya (di paket yang dilanggan) sudah unlocked */
    public function unlocked(User $user, Quiz $quiz): bool
    {
        return $quiz->roadmapItems()
            ->with('module.package')
            ->get()
            ->contains(function ($item) use ($user) {
                return $user->hasActiveSubscription($item->module->package_id)
                    && $this->journey->isUnlocked($user, $item);
            });
    }
}
