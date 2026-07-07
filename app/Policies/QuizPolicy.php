<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

/**
 * Manajemen kuis & soal hanya untuk Super Admin.
 * Semua ability return false — Super Admin lolos via Gate::before di AppServiceProvider.
 */
class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return false;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return false;
    }
}
