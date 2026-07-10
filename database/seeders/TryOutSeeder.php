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
