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
        Schema::create('fleet_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspection_id'); 
            $table->unsignedBigInteger('garage_report_id');
            $table->unsignedBigInteger('issue_id')->nullable();
            $table->unsignedBigInteger('fault_id')->nullable();
            $table->enum('decision', ['inbuild', 'outsource']);
            $table->enum('type', ['fleet', 'garage']);
            $table->enum('status', ['pending', 'sent_to_garage','owner_repair'])->default('pending');
            $table->unsignedBigInteger('supplier_id')->nullable(); 
            $table->timestamps();

            $table->foreign('garage_report_id')->references('id')->on('garage_reports')->onDelete('cascade');
            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->foreign('issue_id')->references('id')->on('issues')->onDelete('cascade');
            $table->foreign('fault_id')->references('id')->on('faults')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_decisions');
    }
};
