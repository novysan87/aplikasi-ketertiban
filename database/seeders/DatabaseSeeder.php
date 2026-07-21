<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ViolationCategory;
use App\Models\ViolationType;
use App\Models\SpThreshold;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@ketertiban.local',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'BK Sekolah',
            'username' => 'bk',
            'email' => 'bk@ketertiban.local',
            'password' => Hash::make('Bk123!'),
            'role' => 'bk',
            'is_active' => true,
        ]);

        // Categories
        $ringan = ViolationCategory::create(['name' => 'Ringan', 'slug' => 'ringan', 'color' => '#22c55e', 'description' => 'Pelanggaran ringan — poin 1-15', 'sort_order' => 1, 'is_active' => true]);
        $sedang = ViolationCategory::create(['name' => 'Sedang', 'slug' => 'sedang', 'color' => '#eab308', 'description' => 'Pelanggaran sedang — poin 15-50', 'sort_order' => 2, 'is_active' => true]);
        $berat = ViolationCategory::create(['name' => 'Berat', 'slug' => 'berat', 'color' => '#ef4444', 'description' => 'Pelanggaran berat — poin 50-100', 'sort_order' => 3, 'is_active' => true]);

        $this->createTypes($ringan->id, [
            ['Terlambat datang ke sekolah', 2, 'Teguran lisan, catat nama'],
            ['Terlambat masuk kelas setelah jam istirahat', 2, 'Teguran lisan'],
            ['Tidak memakai ikat pinggang', 3, 'Teguran, pinjam ikat pinggang'],
            ['Tidak memakai dasi', 3, 'Teguran, pinjam dasi'],
            ['Tidak memakai topi sekolah (hari topi)', 3, 'Teguran lisan'],
            ['Baju tidak dimasukkan', 3, 'Dimasukkan saat itu juga'],
            ['Celana/rok digulung', 4, 'Dibetulkan saat itu juga'],
            ['Sepatu tidak sesuai ketentuan', 4, 'Teguran, catat nama'],
            ['Kaos kaki tidak sesuai ketentuan', 3, 'Teguran lisan'],
            ['Rambut tidak rapi', 5, 'Teguran, potong rambut'],
            ['Rambut dicat/diwarnai', 10, 'Cat rambut hitam kembali'],
            ['Kuku panjang / dicat', 3, 'Potong kuku saat itu juga'],
            ['Make-up / kosmetik berlebihan', 5, 'Bersihkan saat itu juga'],
            ['Aksesoris tidak sesuai aturan', 5, 'Lepas, aksesoris disita'],
            ['Makan/minum di kelas saat jam pelajaran', 3, 'Teguran lisan'],
            ['Membuang sampah sembarangan', 5, 'Bersihkan area sekitarnya'],
            ['Berkata tidak sopan / kotor', 5, 'Teguran, menulis pernyataan'],
            ['Berteriak / ribut di dalam kelas', 3, 'Teguran lisan'],
            ['Di koridor saat jam pelajaran tanpa izin', 4, 'Kembali ke kelas'],
            ['Tidak membawa buku/catatan pelajaran', 3, 'Teguran, catat nama'],
            ['Tidak mengerjakan PR/tugas', 5, 'Kerjakan saat istirahat'],
            ['Mencontek saat ulangan', 10, 'Nilai dikurangi, teguran'],
            ['Tidak ikut upacara tanpa keterangan', 8, 'Teguran, catat nama'],
        ]);

        $this->createTypes($sedang->id, [
            ['Membolos jam pelajaran', 20, 'Panggilan orang tua, surat pernyataan'],
            ['Meninggalkan sekolah tanpa izin', 25, 'Panggilan orang tua'],
            ['Tidak masuk tanpa keterangan (alpha)', 15, 'Panggilan orang tua via wali kelas'],
            ['Memalsukan surat izin / tanda tangan', 30, 'Panggilan orang tua, surat pernyataan'],
            ['Merokok di lingkungan sekolah', 35, 'Skorsing 1 hari, panggilan orang tua'],
            ['HP menyala/dipakai saat jam pelajaran', 20, 'HP disita 1 minggu'],
            ['Merekam/memfoto teman tanpa izin', 25, 'HP disita, hapus rekaman'],
            ['Berkelahi ringan (dorong, adu mulut fisik)', 30, 'Skorsing 1 hari, panggilan orang tua'],
            ['Mengancam / mengintimidasi verbal', 30, 'Konseling BK, panggilan orang tua'],
            ['Mengejek / membully teman', 25, 'Surat pernyataan, panggilan orang tua'],
            ['Membawa alat perjudian', 40, 'Skorsing 3 hari, panggilan orang tua'],
            ['Membawa barang terlarang (petasan dll)', 30, 'Barang disita, panggilan orang tua'],
            ['Corat-coret fasilitas sekolah', 20, 'Bersihkan / cat ulang'],
            ['Merusak fasilitas sekolah ringan', 25, 'Ganti rugi + perbaiki'],
            ['Menyontek massal / menyebar jawaban', 30, 'Nilai 0, panggilan orang tua'],
        ]);

        $this->createTypes($berat->id, [
            ['Berkelahi/tawuran berat', 75, 'Skorsing 1 minggu, panggilan orang tua'],
            ['Membawa senjata tajam', 100, 'Dikeluarkan dari sekolah, laporan polisi'],
            ['Membawa/mengonsumsi minuman keras', 100, 'Dikeluarkan, laporan polisi'],
            ['Membawa/menggunakan narkoba', 100, 'Dikeluarkan, laporan polisi'],
            ['Pencurian', 80, 'Skorsing, panggilan orang tua, laporan polisi'],
            ['Perusakan fasilitas sekolah berat', 60, 'Ganti rugi penuh, skorsing 3 hari'],
            ['Penganiayaan', 100, 'Dikeluarkan, laporan polisi'],
            ['Pemerasan / perampasan barang', 80, 'Skorsing, panggilan orang tua, laporan polisi'],
            ['Perbuatan asusila / pelecehan seksual', 100, 'Dikeluarkan, laporan polisi'],
            ['Mengedarkan konten pornografi', 80, 'Skorsing, panggilan orang tua'],
            ['Ancaman kekerasan serius', 100, 'Dikeluarkan, laporan polisi'],
            ['Merusak sistem informasi sekolah', 90, 'Skorsing, panggilan orang tua'],
        ]);

        // SP Thresholds
        SpThreshold::create(['name' => 'SP 1', 'slug' => 'sp1', 'min_points' => 50, 'max_points' => 99, 'color' => '#eab308', 'default_description' => 'Surat Peringatan 1: Teguran resmi pertama']);
        SpThreshold::create(['name' => 'SP 2', 'slug' => 'sp2', 'min_points' => 100, 'max_points' => 149, 'color' => '#f97316', 'default_description' => 'Surat Peringatan 2: Panggilan orang tua wajib']);
        SpThreshold::create(['name' => 'SP 3', 'slug' => 'sp3', 'min_points' => 150, 'max_points' => null, 'color' => '#ef4444', 'default_description' => 'Surat Peringatan 3: Ancaman dikeluarkan']);

        // School settings
        Setting::setValue('school_name', 'SMK Negeri 1 Contoh', 'school', 'Nama sekolah');
        Setting::setValue('school_address', 'Jl. Contoh No. 1, Kota', 'school', 'Alamat sekolah');
        Setting::setValue('school_phone', '(021) 1234567', 'school', 'No. telepon sekolah');
        Setting::setValue('school_logo', null, 'school', 'Logo sekolah');
        Setting::setValue('kepala_sekolah_name', 'Drs. H. Contoh', 'school', 'Nama Kepala Sekolah');
        Setting::setValue('kepala_sekolah_nip', '196001011990011001', 'school', 'NIP Kepala Sekolah');
        Setting::setValue('bk_koordinator_name', 'Drs. BK', 'school', 'Nama Koordinator BK');
    }

    private function createTypes(int $categoryId, array $types): void
    {
        foreach ($types as [$name, $points, $sanction]) {
            ViolationType::create([
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => Str::slug($name),
                'points' => $points,
                'default_sanction' => $sanction,
                'is_active' => true,
            ]);
        }
    }
}
