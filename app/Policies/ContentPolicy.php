<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

/**
 * Manajemen materi belajar hanya untuk Super Admin.
 * Semua ability return false — Super Admin lolos via Gate::before di AppServiceProvider.
 */
class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Content $content): bool
    {
        return false;
    }

    public function delete(User $user, Content $content): bool
    {
        return false;
    }
}
