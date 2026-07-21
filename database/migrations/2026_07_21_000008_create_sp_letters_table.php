<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sp_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sp_threshold_id')->constrained('sp_thresholds')->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('letter_number')->unique();
            $table->string('title');
            $table->text('body')->nullable();
            $table->integer('total_points_at_time')->default(0);
            $table->json('violations_included')->nullable();
            $table->string('status')->default('draft'); // draft, printed, signed, delivered
            $table->string('file_path')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sp_letters');
    }
};
