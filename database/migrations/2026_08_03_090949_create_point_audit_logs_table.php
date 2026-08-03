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
        Schema::create('point_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('violation_id')->nullable();
            $table->string('action', 20); // created | deleted | adjusted
            $table->integer('points_before')->default(0);
            $table->integer('points_after')->default(0);
            $table->integer('points_delta')->default(0); // bertanda (+ / -)
            $table->string('description')->nullable();
            $table->json('metadata')->nullable(); // snapshot data saat aksi
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('violation_id')->references('id')->on('violations')->nullOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['student_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_audit_logs');
    }
};
