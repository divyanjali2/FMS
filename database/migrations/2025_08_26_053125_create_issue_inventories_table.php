<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('garage_report_id')->constrained('garage_reports')->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->foreignId('inbuild_issue_id')->constrained('garage_inbuild_issues')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_inventories');
    }
};

