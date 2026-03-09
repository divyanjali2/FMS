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
        Schema::create('fleet_vehicle_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fleet_post_check_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['vehicle_release'])->default('vehicle_release');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_vehicle_releases');
    }
};
