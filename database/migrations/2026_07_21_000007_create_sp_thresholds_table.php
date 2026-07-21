<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // SP 1, SP 2, SP 3
            $table->string('slug')->unique();
            $table->integer('min_points'); // ambang poin minimal
            $table->integer('max_points')->nullable(); // null = unlimited
            $table->string('color')->default('#ef4444');
            $table->text('default_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_thresholds');
    }
};
