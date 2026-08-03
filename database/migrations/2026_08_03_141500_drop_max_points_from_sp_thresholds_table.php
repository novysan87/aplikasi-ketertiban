<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * max_points tidak pernah dipakai dalam logika penerbitan SP
     * (trigger hanya membaca min_points). Hapus agar desain simpel.
     */
    public function up(): void
    {
        Schema::table('sp_thresholds', function (Blueprint $table) {
            $table->dropColumn('max_points');
        });
    }

    public function down(): void
    {
        Schema::table('sp_thresholds', function (Blueprint $table) {
            $table->integer('max_points')->nullable()->after('min_points');
        });
    }
};
