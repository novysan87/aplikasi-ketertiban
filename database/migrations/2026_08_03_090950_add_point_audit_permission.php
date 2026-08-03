<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permission baru: view-point-audit (Riwayat Perubahan Poin).
     */
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insert([
            'key' => 'view-point-audit',
            'group' => 'master-data',
            'label' => 'Lihat Riwayat Perubahan Poin',
            'description' => 'Melihat audit log penambahan/pengurangan poin siswa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');

        // BK & Waka Kesiswaan (waka disetarakan dengan BK)
        foreach (['bk', 'waka_kesiswaan'] as $role) {
            DB::table('role_permissions')->insert([
                'role' => $role,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');

        DB::table('role_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('key', 'view-point-audit')->delete();
    }
};
