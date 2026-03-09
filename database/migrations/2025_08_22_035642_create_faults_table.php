<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faults', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['interior', 'exterior','tires & wheels','glass & lights','odometer & fuel','engine & fluid','accessories & documents']);
            $table->foreignId('category_id')->constrained('defect_categories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faults');
    }
};
