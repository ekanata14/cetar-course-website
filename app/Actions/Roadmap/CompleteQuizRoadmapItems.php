<?php

namespace App\Actions\Roadmap;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CompleteQuizRoadmapItems
{
    public function __construct(
        private MarkItemComplete $markComplete,
    ) {}

    /**
     * Setelah user menyelesaikan sebuah kuis, tandai semua item roadmap yang
     * menunjuk kuis itu sebagai selesai — sehingga item berikutnya terbuka.
     */
    public function execute(User $user, Quiz $quiz): void
    {
        $items = $quiz->roadmapItems()->with('module.package')->get();

        foreach ($items as $item) {
            try {
                $this->markComplete->execute($user, $item);
            } catch (AuthorizationException) {
                // Item di paket lain yang tidak dilanggan / masih terkunci — lewati saja
            }
        }
    }
}
