<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan SEMUA permission inti aplikasi ada di tabel `permissions`.
     *
     * Latar belakang: beberapa permission (access-dashboard, input-violations,
     * view-violations, dll) sebelumnya hanya ada di DB produksi (di-insert manual)
     * dan TIDAK pernah di-seed lewat migrasi — akibatnya instalasi baru / DB test
     * kehilangan permission inti. Migrasi ini idempotent (updateOrInsert).
     */
    public function up(): void
    {
        $permissions = [
            // Akses umum
            ['access-dashboard', 'akses', 'Akses Dashboard', 'Membuka halaman dashboard'],
            ['view-notifications', 'akses', 'Lihat Notifikasi', 'Melihat notifikasi aplikasi'],

            // Pelanggaran
            ['input-violations', 'pelanggaran', 'Input Pelanggaran', 'Mencatat pelanggaran siswa'],
            ['view-violations', 'pelanggaran', 'Lihat Pelanggaran', 'Melihat data & detail pelanggaran'],
            ['view-sp-letters', 'pelanggaran', 'Lihat Surat Peringatan', 'Melihat surat peringatan siswa'],
            ['notify-parent-wa', 'pelanggaran', 'Notifikasi WA Orang Tua', 'Mengirim notifikasi WhatsApp ke orang tua'],

            // Data siswa
            ['view-students', 'data', 'Lihat Data Siswa', 'Melihat data siswa'],

            // Presensi
            ['manage-attendance', 'presensi', 'Kelola Presensi', 'Mengelola presensi siswa'],

            // Face ID
            ['face-scan', 'wajah', 'Scan Wajah', 'Verifikasi wajah saat input pelanggaran'],
            ['face-register', 'wajah', 'Registrasi Wajah', 'Mendaftarkan foto wajah siswa'],

            // Master data
            ['categories-manage', 'master-data', 'Kelola Kategori Pelanggaran', ''],
            ['violation-types-manage', 'master-data', 'Kelola Jenis Pelanggaran', ''],
            ['thresholds-manage', 'master-data', 'Kelola Ambang SP', ''],
            ['violations-export', 'master-data', 'Export Data Pelanggaran', ''],
            ['view-point-audit', 'master-data', 'Lihat Riwayat Perubahan Poin', 'Khusus admin'],

            // Administrasi
            ['settings-manage', 'administrasi', 'Pengaturan Aplikasi', ''],
            ['users-manage', 'administrasi', 'Manajemen User', ''],
            ['sync-data', 'administrasi', 'Sinkronisasi Data', ''],
            ['backup-database', 'administrasi', 'Backup Database', ''],
            ['reset-application', 'administrasi', 'Reset Aplikasi', ''],
            ['import-data', 'administrasi', 'Import Data', ''],
            ['export-master', 'administrasi', 'Export Master Data', ''],
        ];

        $now = now();

        foreach ($permissions as [$key, $group, $label, $description]) {
            $exists = DB::table('permissions')->where('key', $key)->exists();

            if ($exists) {
                DB::table('permissions')->where('key', $key)->update([
                    'group' => $group,
                    'label' => $label,
                    'description' => $description ?: null,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('permissions')->insert([
                    'key' => $key,
                    'group' => $group,
                    'label' => $label,
                    'description' => $description ?: null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak menghapus — hanya migrasi perbaikan data.
    }
};
