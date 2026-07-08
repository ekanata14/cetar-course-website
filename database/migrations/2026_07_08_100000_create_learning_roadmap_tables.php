<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roadmap belajar terstruktur: paket → modul berurutan → item roadmap
     * (materi teks/PDF/video atau kuis) dengan gerbang penyelesaian sekuensial.
     */
    public function up(): void
    {
        // Materi pembelajaran mandiri (dilampirkan ke modul via roadmap_items)
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['text', 'pdf', 'video']);
            $table->longText('body')->nullable();      // type=text
            $table->string('file_path')->nullable();   // type=pdf
            $table->string('video_url')->nullable();   // type=video (embed YouTube)
            $table->timestamps();
        });

        Schema::create('package_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('order');
            $table->timestamps();

            $table->index(['package_id', 'order']);
        });

        Schema::create('roadmap_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('package_modules')->cascadeOnDelete();
            $table->morphs('contentable'); // 'content' atau 'quiz' (morph map)
            $table->unsignedSmallInteger('order');
            $table->boolean('is_locked_by_default')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'contentable_type', 'contentable_id'], 'roadmap_items_unique');
            $table->index(['module_id', 'order']);
        });

        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('roadmap_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'roadmap_item_id']);
        });

        // Lipat data pivot lama: setiap paket yang punya konten mendapat "Modul 1"
        // berisi kuis-kuisnya secara berurutan, lalu pivot dibuang.
        $pivotRows = DB::table('package_content')
            ->orderBy('package_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('package_id');

        foreach ($pivotRows as $packageId => $rows) {
            $moduleId = DB::table('package_modules')->insertGetId([
                'package_id' => $packageId,
                'title'      => 'Modul 1',
                'order'      => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($rows->values() as $index => $row) {
                DB::table('roadmap_items')->insert([
                    'module_id'            => $moduleId,
                    'contentable_type'     => $row->contentable_type,
                    'contentable_id'       => $row->contentable_id,
                    'order'                => $index + 1,
                    'is_locked_by_default' => true,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }

        Schema::drop('package_content');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress');
        Schema::dropIfExists('roadmap_items');
        Schema::dropIfExists('package_modules');
        Schema::dropIfExists('contents');

        // Pulihkan struktur pivot lama (tanpa data)
        Schema::create('package_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->morphs('contentable');
            $table->timestamps();

            $table->unique(['package_id', 'contentable_type', 'contentable_id'], 'package_content_unique');
        });
    }
};
