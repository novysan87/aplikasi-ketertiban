<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add class_name to attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('class_name', 100)->nullable()->after('student_id');
        });

        // Add student_class to violations (can't use 'class' — reserved word)
        Schema::table('violations', function (Blueprint $table) {
            $table->string('student_class', 100)->nullable()->after('student_id');
        });

        // Backfill existing records with current student class_name
        DB::statement("UPDATE attendances a
            JOIN students s ON s.id = a.student_id
            SET a.class_name = s.class_name
            WHERE a.class_name IS NULL");

        DB::statement("UPDATE violations v
            JOIN students s ON s.id = v.student_id
            SET v.student_class = s.class_name
            WHERE v.student_class IS NULL");
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('student_class');
        });
    }
};
