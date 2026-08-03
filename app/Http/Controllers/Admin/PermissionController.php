<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * Permission yang TIDAK bisa diubah via halaman ini — khusus admin.
     */
    public const ADMIN_ONLY_PERMISSIONS = [
        'view-point-audit',
    ];

    public function index(): View
    {
        $permissions = DB::table('permissions')
            ->orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        $roles = ['admin', 'bk', 'wali_kelas', 'staff', 'other', 'kepala_sekolah', 'waka_kesiswaan', 'ketua_tim'];

        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role] = DB::table('role_permissions')
                ->where('role', $role)
                ->pluck('permission_id')
                ->toArray();
        }

        $adminOnlyKeys = self::ADMIN_ONLY_PERMISSIONS;

        return view('settings.permissions', compact('permissions', 'roles', 'rolePermissions', 'adminOnlyKeys'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
        ]);

        // Clear all role_permissions first
        DB::table('role_permissions')->delete();

        $now = now();

        if (! empty($data['permissions'])) {
            foreach ($data['permissions'] as $role => $permissionIds) {
                // Permission khusus admin tidak boleh diberikan ke role lain
                if ($role !== 'admin') {
                    $permissionIds = array_filter($permissionIds, function ($pid) {
                        $pid = (int) $pid;
                        if (! $pid) {
                            return false;
                        }
                        $key = DB::table('permissions')->where('id', $pid)->value('key');

                        return ! in_array($key, self::ADMIN_ONLY_PERMISSIONS, true);
                    });
                }

                foreach ($permissionIds as $permissionId) {
                    $permissionId = (int) $permissionId;
                    if (! $permissionId) {
                        continue;
                    }
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // Always give admin all permissions
        $allPermissionIds = DB::table('permissions')->pluck('id');
        foreach ($allPermissionIds as $pid) {
            DB::table('role_permissions')->insertOrIgnore([
                'role' => 'admin',
                'permission_id' => $pid,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Clear cache
        foreach (['admin', 'bk', 'wali_kelas', 'staff', 'other', 'kepala_sekolah', 'waka_kesiswaan', 'ketua_tim'] as $role) {
            Cache::forget('role_permissions:' . $role);
        }

        return redirect()->route('settings.permissions')
            ->with('success', 'Hak akses berhasil diperbarui.');
    }
}
