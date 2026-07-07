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
        Schema::create('user_quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('user_quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->char('selected_option', 1)->nullable(); // 'A' - 'E', null = belum dijawab
            $table->boolean('is_doubtful')->default(false); // Fitur "Ragu-ragu"
            $table->boolean('is_correct')->nullable(); // Diisi saat kalkulasi akhir (optimalisasi read)
            $table->timestamps();

            // Satu jawaban per soal per attempt — membuat upsert auto-save aman
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_answers');
    }
};
