<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed super admin + demo users dengan kode referral.
     */
    public function run(): void
    {
        // 1. Super Admin
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@cetar.id',
            'role' => 'super_admin',
            'referral_code' => 'CETARHQ1',
        ]);

        // 2. Demo users (10 orang), masing-masing punya kode referral unik
        User::factory()
            ->count(10)
            ->sequence(fn ($sequence) => [
                'email' => 'user'.($sequence->index + 1).'@cetar.id',
                'referral_code' => 'CETAR'.strtoupper(Str::random(5)),
            ])
            ->create();

        $this->command->info('Login Super Admin: admin@cetar.id / password');
    }
}
