<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perluas akses view-point-audit ke role yang dipakai sekolah ini
     * (tidak ada user role 'bk' di produksi — yang dipakai: ketua_tim,
     * kepala_sekolah, wali_kelas, staff). Wali kelas tetap ter-scope ke kelasnya.
     */
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');
        if (! $permissionId) {
            return;
        }

        foreach (['ketua_tim', 'kepala_sekolah', 'wali_kelas', 'staff'] as $role) {
            DB::table('role_permissions')->updateOrInsert(
                ['role' => $role, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');
        if (! $permissionId) {
            return;
        }

        DB::table('role_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role', ['ketua_tim', 'kepala_sekolah', 'wali_kelas', 'staff'])
            ->delete();
    }
};
