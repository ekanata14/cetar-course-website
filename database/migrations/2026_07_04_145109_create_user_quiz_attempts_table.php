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
        Schema::create('user_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at'); // Patokan absolut untuk countdown timer Alpine.js
            $table->timestamp('completed_at')->nullable(); // Diisi saat submit / waktu habis
            $table->unsignedInteger('score')->default(0); // Hasil kalkulasi akhir
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();

            // Query paling sering: cari attempt in_progress milik user untuk resume ujian
            $table->index(['user_id', 'quiz_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_attempts');
    }
};
