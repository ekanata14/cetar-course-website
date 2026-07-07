<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pivot polimorfik: menghubungkan konten (Quiz, dan tipe lain ke depannya) ke banyak paket
        Schema::create('package_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->morphs('contentable'); // contentable_type + contentable_id (sudah ter-index)
            $table->timestamps();

            // Cegah duplikasi konten yang sama dalam satu paket
            $table->unique(['package_id', 'contentable_type', 'contentable_id'], 'package_content_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_content');
    }
};
