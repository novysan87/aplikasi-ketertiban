<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handling_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handling_id')->constrained('violation_handlings')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');
            $table->string('role', 50)->nullable()->comment('peran dalam penanganan: Wakil Ketua Tim, Anggota Tim, Wali Kelas, Guru BK, Saksi, dll');
            $table->timestamps();
            $table->unique(['handling_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handling_participants');
    }
};
