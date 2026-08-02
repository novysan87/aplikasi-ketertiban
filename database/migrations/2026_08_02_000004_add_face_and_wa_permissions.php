<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ===== 1) Permission baru untuk fitur Face ID & Notifikasi WA =====
        $newPermissions = [
            // [key, group, label, description]
            ['face-scan', 'wajah', 'Scan Wajah', 'Scan wajah siswa untuk verifikasi identitas'],
            ['face-register', 'wajah', 'Registrasi Wajah', 'Registrasi/perbarui foto wajah siswa'],
            ['notify-parent-wa', 'pelanggaran', 'Notifikasi WA Orang Tua', 'Kirim notifikasi WhatsApp ke orang tua/wali'],
        ];

        $permIds = [];
        foreach ($newPermissions as [$key, $group, $label, $desc]) {
            $id = DB::table('permissions')->insertGetId([
                'key' => $key,
                'group' => $group,
                'label' => $label,
                'description' => $desc,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $permIds[$key] = $id;
        }

        // ===== 2) Default grant: role yang punya akses terkait ikut dapat =====
        // face-scan & face-register → role yang punya input-violations
        $inputRoles = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('permissions.key', 'input-violations')
            ->pluck('role_permissions.role')
            ->unique();

        foreach (['face-scan', 'face-register'] as $key) {
            foreach ($inputRoles as $role) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role' => $role,
                    'permission_id' => $permIds[$key],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // notify-parent-wa → role yang punya view-violations
        $viewRoles = DB::table('role_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('permissions.key', 'view-violations')
            ->pluck('role_permissions.role')
            ->unique();

        foreach ($viewRoles as $role) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => $role,
                'permission_id' => $permIds['notify-parent-wa'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Admin selalu full — pastikan 3 permission baru masuk admin
        foreach ($permIds as $pid) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => 'admin',
                'permission_id' => $pid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('key', ['face-scan', 'face-register', 'notify-parent-wa'])
            ->delete();
    }
};
