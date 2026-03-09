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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained()->onDelete('cascade');
            $table->json('procurement_ids'); 
            $table->foreignId('payment_coordinator_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['equal', 'custom']);
            $table->json('options'); 
            $table->enum('status', ['send_to_gm', 'paid'])->default('send_to_gm');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
