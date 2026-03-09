<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('md_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procurement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gm_review_id')->constrained()->cascadeOnDelete();
            $table->text('md_comment')->nullable();
            $table->enum('status', ['pending', 'sent_to_gm'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('md_reviews');
    }
};
