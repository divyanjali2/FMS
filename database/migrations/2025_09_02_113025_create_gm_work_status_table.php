<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gm_work_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('issue_inventory_id')->nullable()->constrained('issue_inventories')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'work_done']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gm_work_status');
    }
};
