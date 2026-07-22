<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_active');
        });

        // Mark existing alpha & terlambat types as system
        DB::table('violation_types')
            ->whereIn('id', function ($q) {
                $q->select('id')
                  ->from('violation_types')
                  ->where('name', 'like', '%alpha%')
                  ->orWhere('slug', 'terlambat-datang-ke-sekolah-1');
            })
            ->update(['is_system' => true]);

        // Set clean slugs for system types
        DB::table('violation_types')
            ->where('name', 'like', '%alpha%')
            ->update(['slug' => 'alpha']);

        DB::table('violation_types')
            ->where('slug', 'terlambat-datang-ke-sekolah-1')
            ->update(['slug' => 'terlambat']);
    }

    public function down(): void
    {
        // Restore slugs
        DB::table('violation_types')
            ->where('slug', 'alpha')
            ->update(['slug' => 'tidak-masuk-tanpa-keterangan-alpha']);

        DB::table('violation_types')
            ->where('slug', 'terlambat')
            ->update(['slug' => 'terlambat-datang-ke-sekolah']);

        Schema::table('violation_types', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
