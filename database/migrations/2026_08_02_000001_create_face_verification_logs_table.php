<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('violation_id')->nullable()->constrained('violations')->nullOnDelete();
            $table->boolean('faceid_matched')->default(false);
            $table->boolean('faceid_ambiguous')->default(false);
            $table->string('faceid_reason')->nullable(); // cocok / tidak_cocok / kembar_terdeteksi / skor_berdekatan
            $table->decimal('faceid_score', 5, 4)->nullable();
            $table->string('photo_hash', 64)->nullable(); // sha256 foto, tanpa menyimpan foto asli
            $table->json('top_candidates')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_verification_logs');
    }
};
