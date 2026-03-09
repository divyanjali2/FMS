<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('temp_inspections', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('fault_id')->nullable();
            $table->enum('status', [
                'scratch','dent','tear','crack','broken','missing','not_working','less_fuel','exceed','dirty'
            ])->nullable();
            $table->enum('type', ['routine', 'emergency']);
            $table->enum('job_status', ['not completed', 'completed'])->default('not completed');
            $table->timestamps();
            $table->foreign('inspection_id')->references('id')->on('inspections')->onDelete('cascade');
            $table->foreign('fault_id')->references('id')->on('faults')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temp_inspections');
    }
};

