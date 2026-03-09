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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('reg_no')->unique();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignId('vehicle_category_id')->nullable()->constrained('vehicle_categories');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->year('year_of_manufacture')->nullable();
            $table->string('color')->nullable();
            $table->string('vin')->unique()->nullable();
            $table->string('engine_no')->unique()->nullable();
            $table->foreignId('fuel_type_id')->constrained('fuel_types');
            $table->foreignId('transmission_id')->constrained('transmissions');
            $table->integer('seating_capacity')->nullable();
            $table->integer('odometer_at_registration')->nullable();
            $table->foreignId('ownership_type_id')->constrained('ownership_types');
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_no')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('emission_test_expiry')->nullable();
            $table->date('revenue_license_expiry')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->nullable();
            // $table->decimal('current_value', 12, 2)->nullable();
            // $table->string('loan_emi_details')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            // Documents
            $table->string('revenue_license_file')->nullable();
            $table->string('insurance_file')->nullable();
            $table->string('emission_test_file')->nullable();
            $table->string('other_doc_file')->nullable();
            // Vehicle images
            $table->string('vehicle_front')->nullable();
            $table->string('vehicle_back')->nullable();
            $table->string('vehicle_left')->nullable();
            $table->string('vehicle_right')->nullable();
            $table->enum('status', ['active', 'disabled'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
