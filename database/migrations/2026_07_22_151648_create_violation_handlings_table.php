<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_handlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained()->cascadeOnDelete();
            $table->string('handling_type', 50);
            $table->date('handling_date');
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('evidence', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_handlings');
    }
};
