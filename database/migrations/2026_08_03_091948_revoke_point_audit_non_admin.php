<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Riwayat Poin khusus ADMIN: cabut akses dari semua role non-admin.
     * Admin tetap bisa via User::isAdmin() (canPermission selalu true).
     */
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');
        if (! $permissionId) {
            return;
        }

        DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');
        if (! $permissionId) {
            return;
        }

        foreach (['bk', 'waka_kesiswaan', 'ketua_tim', 'kepala_sekolah', 'wali_kelas', 'staff'] as $role) {
            DB::table('role_permissions')->updateOrInsert(
                ['role' => $role, 'permission_id' => $permissionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
};
