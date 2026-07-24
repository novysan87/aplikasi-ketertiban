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
    public function index(): View
    {
        $permissions = DB::table('permissions')
            ->orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        $roles = ['admin', 'bk', 'wali_kelas', 'staff', 'other'];

        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role] = DB::table('role_permissions')
                ->where('role', $role)
                ->pluck('permission_id')
                ->toArray();
        }

        return view('settings.permissions', compact('permissions', 'roles', 'rolePermissions'));
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
                foreach ($permissionIds as $permissionId) {
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => (int) $permissionId,
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
        foreach (['admin', 'bk', 'wali_kelas', 'staff', 'other'] as $role) {
            Cache::forget('role_permissions:' . $role);
        }

        return redirect()->route('settings.permissions')
            ->with('success', 'Hak akses berhasil diperbarui.');
    }
}
