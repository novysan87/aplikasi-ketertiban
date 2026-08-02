<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('channel', 20)->default('whatsapp');
            $table->string('recipient', 30)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('sent');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['violation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_notifications');
    }
};
