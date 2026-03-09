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
       
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('available_quantity');
            $table->integer('remaining_quantity')->nullable(); 
            $table->integer('min_stock_level')->default(1); 
            $table->enum('unit', ['pcs', 'kg', 'liters', 'boxes', 'packs'])->default('pcs'); 
            $table->date('purchase_date')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreignId('inventory_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
