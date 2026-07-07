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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('section')->nullable(); // Contoh: 'TWK', 'TIU', 'TKP' (untuk grouping/tabs)
            $table->text('passage')->nullable(); // Teks bacaan panjang
            $table->text('text'); // Pertanyaan utama
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->text('option_e')->nullable();
            $table->char('correct_answer', 1); // 'A' - 'E'
            $table->unsignedInteger('points')->default(5); // Bobot nilai (TKP di-handle khusus di scoring Action)
            $table->text('explanation')->nullable(); // Pembahasan jawaban
            $table->timestamps();

            // Query CBT paling sering: ambil soal per kuis, dikelompokkan per section
            $table->index(['quiz_id', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
