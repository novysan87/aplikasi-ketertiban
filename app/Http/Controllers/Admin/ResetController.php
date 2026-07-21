<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ResetController extends Controller
{
    public function index(): View
    {
        $stats = [
            'violations' => DB::table('violations')->count(),
            'evidences' => DB::table('violation_evidences')->count(),
            'sp_letters' => DB::table('sp_letters')->count(),
            'notifications' => DB::table('notifications')->count(),
            'students' => DB::table('students')->count(),
            'classes' => DB::table('classes')->count(),
            'settings' => DB::table('settings')->count(),
            'users_other' => DB::table('users')->where('id', '!=', 1)->count(),
        ];

        return view('settings.reset', compact('stats'));
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->confirm_password, $request->user()->password)) {
            return back()->with('error', 'Password salah. Reset dibatalkan.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('violation_evidences')->delete();
        DB::table('violations')->delete();
        DB::table('sp_letters')->delete();
        DB::table('notifications')->delete();
        DB::table('students')->delete();
        DB::table('classes')->delete();
        DB::table('violation_types')->delete();
        DB::table('violation_categories')->delete();
        DB::table('sp_thresholds')->delete();
        DB::table('settings')->delete();

        User::where('id', '!=', 1)->delete();
        User::where('id', 1)->update(['is_active' => true, 'role' => 'admin']);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $violationsDir = storage_path('app/public/violations');
        if (is_dir($violationsDir)) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($violationsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $file) {
                if ($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'Reset berhasil! Semua data pelanggaran, siswa, master data, dan user lain dihapus. Hanya akun admin yang tersisa.');
    }
}
