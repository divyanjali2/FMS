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
        Schema::create('payment_coordinators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained()->onDelete('cascade');
            $table->json('procurement_ids'); 
            $table->decimal('total_price', 12, 2)->nullable();
            $table->enum('status', ['send_to_cashier', 'approved', 'rejected'])->default('send_to_cashier');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_coordinators');
    }
};
