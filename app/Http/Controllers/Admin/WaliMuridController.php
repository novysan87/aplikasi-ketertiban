<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliMuridController extends Controller
{
    /**
     * Data akun wali murid (terpisah dari user internal sekolah).
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'parent')
            ->with([
                'parentStudents' => fn ($q) => $q->with('student:id,nisn,full_name,class_name'),
                'parentDevices',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        $total = User::where('role', 'parent')->count();
        $totalAktif = User::where('role', 'parent')
            ->whereHas('parentStudents', fn ($q) => $q->where('status', 'active'))
            ->count();
        $pending = ParentStudent::where('status', 'pending')->count();
        $baruMingguIni = User::where('role', 'parent')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return view('parents.index', compact('users', 'total', 'totalAktif', 'pending', 'baruMingguIni'));
    }

    /**
     * Logout jarak jauh: cabut semua token Sanctum akun wali
     * → app di semua perangkat langsung 401 → otomatis kembali ke login.
     */
    public function forceLogout(int $userId): RedirectResponse
    {
        $user = User::where('role', 'parent')->findOrFail($userId);

        $user->tokens()->delete();
        $user->forceFill(['active_session_token' => null])->save();

        return back()->with('success', 'Semua perangkat akun "' . $user->name . '" telah di-logout dari jarak jauh.');
    }

    /**
     * Hapus akun wali murid beserta tautan anak & perangkatnya.
     * Hanya akun role=parent yang boleh dihapus lewat sini.
     */
    public function destroy(int $userId): RedirectResponse
    {
        $user = User::where('role', 'parent')->findOrFail($userId);

        $user->parentDevices()->delete();
        $user->parentStudents()->delete();
        $user->tokens()->delete(); // cabut semua token login → app otomatis 401
        $user->delete();

        return back()->with('success', 'Akun wali "' . $user->name . '" beserta tautan & perangkatnya telah dihapus.');
    }
}
