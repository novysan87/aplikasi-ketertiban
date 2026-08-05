<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // relation: father | mother | guardian (sesuai student_parents di kesiswaan)
            $table->string('relation', 20)->nullable();
            // status: pending (menunggu verifikasi admin) | active | rejected
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_students');
    }
};
