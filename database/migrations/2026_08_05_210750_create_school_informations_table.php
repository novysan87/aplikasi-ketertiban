<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_informations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('content')->nullable();
            $table->string('category', 50)->default('umum')->index(); // umum | akademik | kegiatan | uts | uas | lainnya
            $table->date('event_date')->nullable()->index(); // tanggal kegiatan (mis. tanggal UTS)
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'event_date']);
        });

        // ===== Permission: kelola Informasi Sekolah =====
        $now = now();
        $permId = DB::table('permissions')->insertGetId([
            'key' => 'manage-school-info',
            'group' => 'sekolah',
            'label' => 'Kelola Informasi Sekolah',
            'description' => 'Tambah/ubah/hapus informasi & pengumuman sekolah (ditampilkan di aplikasi wali murid)',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['admin', 'bk', 'waka_kesiswaan', 'kepala_sekolah', 'ketua_tim'] as $role) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => $role,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_informations');

        $ids = DB::table('permissions')->where('key', 'manage-school-info')->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->where('key', 'manage-school-info')->delete();
    }
};
