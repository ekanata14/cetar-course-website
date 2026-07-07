<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

/**
 * Manajemen paket hanya untuk Super Admin.
 * Semua ability return false — Super Admin lolos via Gate::before di AppServiceProvider.
 */
class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Package $package): bool
    {
        return false;
    }

    public function delete(User $user, Package $package): bool
    {
        return false;
    }
}
