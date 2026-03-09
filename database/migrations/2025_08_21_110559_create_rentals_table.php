<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->nullable();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name');
            $table->string('salutation')->nullable();
            $table->dateTime('arrival_date');
            $table->dateTime('departure_date');
            $table->dateTime('vehicle_pickup');
            $table->integer('passengers');
            $table->text('notes')->nullable();
            $table->enum('status', ['booked','rented', 'arrived', 'completed','emergency_completed','cancelled'])->default('booked');
            $table->enum('repair_type', ['routine', 'emergency'])->nullable();
            $table->string('reference_no')->unique()->nullable();    
            $table->string('emer_booking_number')->nullable();
            $table->string('emer_customer_name')->nullable();
            $table->integer('emer_no_of_passengers')->nullable();
            $table->dateTime('emer_arrival_date')->nullable();
            $table->dateTime('emer_departure_date')->nullable();
            $table->dateTime('alternative_start_date')->nullable();
            $table->text('change_reason')->nullable();    
            $table->boolean('is_old_vehicle')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
