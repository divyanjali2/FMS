<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_post_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('gm_work_status_id')->constrained('gm_work_status')->onDelete('cascade');
            $table->foreignId('issue_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('fault_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('verified')->default(false);
            $table->text('remarks')->nullable();
            $table->enum('status', ['send_to_fm', 'send_back_to_garage'])->default('send_to_fm');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_post_checks');
    }
};
