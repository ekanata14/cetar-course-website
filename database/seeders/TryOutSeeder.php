<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class TryOutSeeder extends Seeder
{
    /**
     * Impor bank try out CPNS dari data soal tetap (database/seeders/data).
     * Menempel ke modul "Bank Try Out CPNS" pada paket Pejuang CPNS agar bisa
     * direproduksi lewat migrate:fresh --seed. Reuse command questions:import
     * yang sudah menangani ekspansi opsi bergambar (soal figural TIU).
     */
    public function run(): void
    {
        $package = Package::where('slug', 'pejuang-cpns-2026')->first();

        if (! $package) {
            return; // katalog belum di-seed → tidak ada tempat menempel
        }

        // Modul bank try out: bebas dikerjakan, terpisah dari "Modul 1" yang bergerbang
        $module = $package->modules()->firstOrCreate(
            ['title' => 'Bank Try Out CPNS'],
            ['order' => ($package->modules()->max('order') ?? 0) + 1],
        );

        $tryouts = [
            ['slug' => 'cpns-paket-01', 'title' => 'CPNS Paket 01', 'duration' => 100],
            ['slug' => 'cpns-paket-02', 'title' => 'CPNS Paket 02', 'duration' => 40],
            ['slug' => 'cpns-paket-03', 'title' => 'CPNS Paket 03', 'duration' => 40],
            ['slug' => 'cpns-paket-04', 'title' => 'CPNS Paket 04', 'duration' => 40],
            ['slug' => 'cpns-paket-05', 'title' => 'CPNS Paket 05', 'duration' => 40],
            ['slug' => 'cpns-paket-06', 'title' => 'CPNS Paket 06', 'duration' => 40],
            ['slug' => 'cpns-paket-07', 'title' => 'CPNS Paket 07', 'duration' => 40],
            ['slug' => 'cpns-paket-08', 'title' => 'CPNS Paket 08', 'duration' => 40],
            ['slug' => 'cpns-paket-09', 'title' => 'CPNS Paket 09', 'duration' => 40],
            ['slug' => 'cpns-paket-10', 'title' => 'CPNS Paket 10', 'duration' => 40],
        ];

        foreach ($tryouts as $tryout) {
            // Sediakan gambar figural di public disk (di sana .gitignore),
            // bersumber dari data seed yang di-track. Idempoten.
            $source = database_path("seeders/data/questions/{$tryout['slug']}");

            if (File::isDirectory($source)) {
                File::copyDirectory($source, storage_path("app/public/questions/{$tryout['slug']}"));
            }

            // Reuse importer — idempoten by judul kuis (dilewati jika sudah ada).
            Artisan::call('questions:import', [
                'json' => database_path("seeders/data/{$tryout['slug']}.json"),
                '--quiz-title' => $tryout['title'],
                '--slug' => $tryout['slug'],
                '--module' => $module->id,
                '--duration' => $tryout['duration'],
                '--no-archive' => true,
            ]);
        }
    }
}
