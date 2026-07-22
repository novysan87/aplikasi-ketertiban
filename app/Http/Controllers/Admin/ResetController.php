<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ViolationCategory;
use App\Models\ViolationType;
use App\Models\SpThreshold;
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
            'categories' => DB::table('violation_categories')->count(),
            'types' => DB::table('violation_types')->count(),
            'thresholds' => DB::table('sp_thresholds')->count(),
            'users_other' => DB::table('users')->where('role', '!=', 'admin')->count(),
        ];

        return view('settings.reset', compact('stats'));
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_password' => ['required', 'string'],
            'reset_items' => ['required', 'array', 'min:1'],
            'reset_items.*' => ['in:violations,evidences,sp_letters,notifications,students,classes,categories,types,thresholds,settings,users'],
        ]);

        if (!Hash::check($request->confirm_password, $request->user()->password)) {
            return back()->with('error', 'Password salah. Reset dibatalkan.');
        }

        $items = $request->reset_items;
        $admin = User::where('role', 'admin')->orderBy('id')->first();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $cleared = [];

        if (in_array('evidences', $items)) {
            DB::table('violation_evidences')->delete();
            $cleared[] = 'Foto bukti';
        }

        if (in_array('violations', $items)) {
            DB::table('violations')->delete();
            $cleared[] = 'Pelanggaran';
        }

        if (in_array('sp_letters', $items)) {
            DB::table('sp_letters')->delete();
            $cleared[] = 'Surat Peringatan';
        }

        if (in_array('notifications', $items)) {
            DB::table('notifications')->delete();
            $cleared[] = 'Notifikasi';
        }

        if (in_array('students', $items)) {
            DB::table('students')->delete();
            $cleared[] = 'Data siswa';
        }

        if (in_array('classes', $items)) {
            DB::table('classes')->delete();
            $cleared[] = 'Data kelas';
        }

        if (in_array('types', $items)) {
            DB::table('violation_types')->delete();
            $cleared[] = 'Jenis pelanggaran';
        }

        if (in_array('categories', $items)) {
            DB::table('violation_categories')->delete();
            $cleared[] = 'Kategori pelanggaran';
        }

        if (in_array('thresholds', $items)) {
            DB::table('sp_thresholds')->delete();
            $cleared[] = 'Ambang SP';
        }

        if (in_array('settings', $items)) {
            DB::table('settings')->delete();
            $cleared[] = 'Pengaturan sekolah';
        }

        if (in_array('users', $items)) {
            DB::table('users')->where('role', '!=', 'admin')->delete();
            $cleared[] = 'User lain';
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Auto-seed defaults jika kategori/tipe/threshold dihapus
        if (in_array('categories', $items) && ViolationCategory::count() === 0) {
            $this->seedDefaultCategories();
        }
        if (in_array('thresholds', $items) && SpThreshold::count() === 0) {
            $this->seedDefaultThresholds();
        }

        // Hapus file foto jika evidences dihapus
        if (in_array('evidences', $items)) {
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
        }

        $msg = 'Reset berhasil! Data yang dibersihkan: ' . implode(', ', $cleared) . '.';

        if ($admin) {
            $admin->update(['is_active' => true]);
        }

        return redirect()->route('settings.reset')->with('success', $msg);
    }

    private function seedDefaultCategories(): void
    {
        $categories = [
            ['name' => 'Ringan', 'description' => 'Pelanggaran ringan (poin 1-15)', 'color' => '#eab308', 'min_points' => 1, 'max_points' => 15, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sedang', 'description' => 'Pelanggaran sedang (poin 15-50)', 'color' => '#f97316', 'min_points' => 15, 'max_points' => 50, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Berat', 'description' => 'Pelanggaran berat (poin 50-100)', 'color' => '#ef4444', 'min_points' => 50, 'max_points' => 100, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('violation_categories')->insert($categories);
    }

    private function seedDefaultThresholds(): void
    {
        $thresholds = [
            ['name' => 'SP 1', 'slug' => 'sp-1', 'min_points' => 50, 'max_points' => 99, 'color' => '#eab308', 'is_active' => true, 'description' => 'Surat Peringatan 1 — poin mencapai 50', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SP 2', 'slug' => 'sp-2', 'min_points' => 100, 'max_points' => 149, 'color' => '#f97316', 'is_active' => true, 'description' => 'Surat Peringatan 2 — poin mencapai 100', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SP 3', 'slug' => 'sp-3', 'min_points' => 150, 'max_points' => null, 'color' => '#ef4444', 'is_active' => true, 'description' => 'Surat Peringatan 3 — poin mencapai 150', 'created_at' => now(), 'updated_at' => now()],
        ];
        DB::table('sp_thresholds')->insert($thresholds);
    }
}
