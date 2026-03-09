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
      Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('rental_id')->nullable()->constrained('rentals')->onDelete('cascade');
            $table->date('inspection_date');
            $table->integer('odometer_reading');
            $table->boolean('insurance_claim')->default(false)->nullable(); 
            $table->string('status')->default('Sent to Garage'); 
            $table->string('repair_type')->default('routine');
            $table->string('job_code')->unique();
            $table->string('remarks');
            $table->json('images')->nullable(); 
            $table->enum('vehicle_status', ['arrived', 'freeze','completed'])->default('arrived')->nullable();        
            $table->enum('vehicle_condition', ['available', 'under_maintenance'])->default('available')->nullable();        
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
