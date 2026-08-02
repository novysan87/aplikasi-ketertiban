<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $pid = fn (string $key) => DB::table('permissions')->where('key', $key)->value('id');

        $grant = function (string $role, array $keys) use ($now, $pid) {
            foreach ($keys as $key) {
                $id = $pid($key);
                if ($id) {
                    DB::table('role_permissions')->insertOrIgnore([
                        'role' => $role,
                        'permission_id' => $id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        };

        // ===== Kepala Sekolah: view + export + scan (bukan input harian) =====
        $grant('kepala_sekolah', [
            'access-dashboard',
            'view-violations',
            'view-students',
            'view-sp-letters',
            'view-notifications',
            'violations-export',
            'face-scan',
        ]);

        // ===== Waka Kesiswaan: setara BK =====
        $bkPerms = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role', 'bk')
            ->pluck('permissions.key')
            ->toArray();
        $grant('waka_kesiswaan', $bkPerms);

        // ===== Ketua Tim: setara Staff =====
        $staffPerms = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role', 'staff')
            ->pluck('permissions.key')
            ->toArray();
        $grant('ketua_tim', $staffPerms);
    }

    public function down(): void
    {
        DB::table('role_permissions')
            ->whereIn('role', ['kepala_sekolah', 'waka_kesiswaan', 'ketua_tim'])
            ->delete();
    }
};
