<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Seed paket produk utama (CPNS & SNBT) beserta tier harganya.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Pejuang CPNS 2026',
                'slug' => 'pejuang-cpns-2026',
                'description' => 'Persiapan lengkap SKD CPNS: TWK, TIU, dan TKP dengan try out CAT sesuai standar BKN.',
                'plans' => [
                    ['name' => '1 Bulan', 'duration_days' => 30, 'price' => 99000],
                    ['name' => '1 Tahun', 'duration_days' => 365, 'price' => 499000],
                ],
            ],
            [
                'name' => 'Juara SNBT 2026',
                'slug' => 'juara-snbt-2026',
                'description' => 'Persiapan UTBK-SNBT: Tes Potensi Skolastik, Literasi, dan Penalaran Matematika.',
                'plans' => [
                    ['name' => '1 Bulan', 'duration_days' => 30, 'price' => 79000],
                    ['name' => '1 Tahun', 'duration_days' => 365, 'price' => 399000],
                ],
            ],
        ];

        foreach ($packages as $data) {
            $plans = $data['plans'];
            unset($data['plans']);

            $package = Package::create($data);
            $package->plans()->createMany($plans);
        }
    }
}
