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
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('issue_inventory_id')->constrained()->onDelete('cascade');
            $table->string('po_id')->nullable();
            $table->enum('status', ['from_stock', 'outsourced'])->default('outsourced');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('price', 12, 2)->nullable();
            $table->text('remark')->nullable();
            $table->integer('fulfilled_qty')->default(0);
            $table->string('bill_path')->nullable();
            $table->enum('procurement_status', ['draft', 'send_to_accountant','send_to_fleet','cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
