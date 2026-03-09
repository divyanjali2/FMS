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
        Schema::create('garage_inbuild_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('garage_report_id');
            $table->unsignedBigInteger('issue_id')->nullable();
            $table->unsignedBigInteger('fault_id')->nullable();
            $table->enum('type', ['fleet', 'garage']);
            $table->timestamps();

            $table->foreign('garage_report_id')->references('id')->on('garage_reports')->onDelete('cascade');
            $table->foreign('issue_id')->references('id')->on('issues')->onDelete('cascade');
            $table->foreign('fault_id')->references('id')->on('faults')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garage_inbuild_issues');
    }
};
