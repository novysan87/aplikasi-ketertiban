<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $id = DB::table('permissions')->insertGetId([
            'key' => 'parents-verify',
            'group' => 'wali',
            'label' => 'Verifikasi Akun Wali Murid',
            'description' => 'Menyetujui/menolak akun wali murid yang mendaftar dari aplikasi mobile',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Role yang berhak memverifikasi
        foreach (['admin', 'bk', 'waka_kesiswaan', 'kepala_sekolah', 'ketua_tim'] as $role) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => $role,
                'permission_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')->where('key', 'parents-verify')->pluck('id');
        DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->where('key', 'parents-verify')->delete();
    }
};
