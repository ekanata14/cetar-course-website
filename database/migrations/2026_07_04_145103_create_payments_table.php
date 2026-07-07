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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_plan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2); // Total tagihan
            $table->enum('status', ['pending', 'settled', 'failed', 'expired'])->default('pending');
            $table->string('external_id')->nullable()->unique(); // Invoice/order reference untuk matching webhook DOKU
            $table->string('payment_url')->nullable(); // URL checkout dari gateway (DOKU)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
