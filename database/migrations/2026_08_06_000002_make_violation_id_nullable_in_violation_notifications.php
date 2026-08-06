<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // violation_id boleh kosong untuk notifikasi non-pelanggaran
        // (mis. notifikasi kehadiran & SP yang belum punya violation terkait).
        Schema::table('violation_notifications', function (Blueprint $table) {
            $table->foreignId('violation_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('violation_notifications', function (Blueprint $table) {
            $table->foreignId('violation_id')->nullable(false)->change();
        });
    }
};
