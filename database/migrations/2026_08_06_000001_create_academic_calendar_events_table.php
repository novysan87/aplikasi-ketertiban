<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->date('date_end')->nullable();
            $table->string('title');
            $table->string('category', 20)->default('kegiatan'); // libur | hari-besar | kegiatan | ujian
            $table->string('semester', 10)->nullable(); // ganjil | genap
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('date');
        });

        // ===== Data Kalender Pendidikan SMKN 1 Wonorejo 2026/2027 =====
        // (sumber: PDF Kalender Pendidikan TA 2026/2027)
        $events = [
            // ---- Semester Ganjil ----
            ['2026-07-04', null, 'Kembalian Santri Al-Yasini', 'kegiatan', 'ganjil'],
            ['2026-07-08', null, 'Mukim Santri Baru 2026', 'kegiatan', 'ganjil'],
            ['2026-07-13', null, 'Awal Masuk / Permulaan PBM 2026/2027', 'kegiatan', 'ganjil'],
            ['2026-07-13', '2026-07-18', 'MPLS (Masa Pengenalan Lingkungan Sekolah)', 'kegiatan', 'ganjil'],
            ['2026-08-01', null, 'Orientasi Guru & Pegawai', 'kegiatan', 'ganjil'],
            ['2026-08-13', null, 'Jalan Sehat Yayasan', 'kegiatan', 'ganjil'],
            ['2026-08-17', null, 'HUT ke-81 Republik Indonesia', 'hari-besar', 'ganjil'],
            ['2026-08-18', '2026-08-22', 'Perkiraan Lomba-lomba', 'kegiatan', 'ganjil'],
            ['2026-08-18', '2026-09-27', 'Pendaftaran TKA', 'ujian', 'ganjil'],
            ['2026-08-25', null, 'Maulid Nabi Muhammad SAW', 'hari-besar', 'ganjil'],
            ['2026-09-09', null, 'Ngaji Guru', 'kegiatan', 'ganjil'],
            ['2026-09-21', '2026-09-27', 'Simulasi TKA', 'ujian', 'ganjil'],
            ['2026-09-26', '2026-10-18', 'Gladi Bersih TKA', 'ujian', 'ganjil'],
            ['2026-10-05', '2026-10-08', 'Kegiatan Tengah Semester (KTS)', 'kegiatan', 'ganjil'],
            ['2026-10-22', null, 'Peringatan Hari Santri Nasional (HSN) ke-12', 'hari-besar', 'ganjil'],
            ['2026-10-24', null, 'Ngaji Guru', 'kegiatan', 'ganjil'],
            ['2026-10-26', '2026-11-05', 'Pelaksanaan TKA', 'ujian', 'ganjil'],
            ['2026-11-08', '2026-12-22', 'Pengolahan Hasil TKA', 'ujian', 'ganjil'],
            ['2026-11-10', null, 'Peringatan Hari Pahlawan', 'hari-besar', 'ganjil'],
            ['2026-11-16', '2026-11-29', 'TKA Susulan', 'ujian', 'ganjil'],
            ['2026-11-28', null, 'Ngaji Guru', 'kegiatan', 'ganjil'],
            ['2026-12-02', '2026-12-19', 'Input Nilai SAS', 'ujian', 'ganjil'],
            ['2026-12-07', '2026-12-15', 'Sumatif Akhir Semester (SAS) Ganjil', 'ujian', 'ganjil'],
            ['2026-12-13', null, 'Haul Masayikh ke-24', 'kegiatan', 'ganjil'],
            ['2026-12-16', '2026-12-17', 'Remidial SAS', 'ujian', 'ganjil'],
            ['2026-12-21', '2026-12-22', 'Cetak Rapor SAS', 'kegiatan', 'ganjil'],
            ['2026-12-23', null, 'Pengumuman Hasil & Pembagian Rapor Semester Ganjil', 'kegiatan', 'ganjil'],
            ['2026-12-24', null, 'Cuti Bersama', 'libur', 'ganjil'],
            ['2026-12-25', null, 'Hari Raya Natal', 'hari-besar', 'ganjil'],
            ['2026-12-26', '2026-12-31', 'Libur Semester Ganjil', 'libur', 'ganjil'],

            // ---- Semester Genap ----
            ['2027-01-01', null, 'Tahun Baru Masehi 2027', 'hari-besar', 'genap'],
            ['2027-01-04', null, 'Awal Masuk Semester Genap 2026/2027', 'kegiatan', 'genap'],
            ['2027-01-05', null, 'Isra Mikraj Nabi Muhammad SAW', 'hari-besar', 'genap'],
            ['2027-01-30', null, 'Ngaji Guru', 'kegiatan', 'genap'],
            ['2027-02-06', null, 'Tahun Baru Imlek 2578 Kongzili', 'hari-besar', 'genap'],
            ['2027-02-07', '2027-02-09', 'Libur Awal Ramadhan 1448 H', 'libur', 'genap'],
            ['2027-02-28', null, 'Pulangan Santri & Buka Bersama Guru-Pegawai', 'kegiatan', 'genap'],
            ['2027-02-28', '2027-03-24', 'Libur Hari Raya Idulfitri 1448 H', 'libur', 'genap'],
            ['2027-03-09', null, 'Hari Raya Nyepi (Tahun Baru Saka 1949)', 'hari-besar', 'genap'],
            ['2027-03-10', '2027-03-11', 'Hari Raya Idulfitri 1448 H', 'hari-besar', 'genap'],
            ['2027-03-26', null, 'Wafat Isa Almasih', 'hari-besar', 'genap'],
            ['2027-03-28', null, 'Hari Raya Paskah', 'hari-besar', 'genap'],
            ['2027-04-01', '2027-04-12', 'Perkiraan Rentang Waktu PSAJ 2027', 'ujian', 'genap'],
            ['2027-04-17', null, 'Ngaji Guru', 'kegiatan', 'genap'],
            ['2027-05-01', null, 'Hari Buruh Internasional', 'hari-besar', 'genap'],
            ['2027-05-06', null, 'Kenaikan Yesus Kristus', 'hari-besar', 'genap'],
            ['2027-05-17', null, 'Pemberangkatan P2S', 'kegiatan', 'genap'],
            ['2027-05-17', null, 'Hari Raya Idul Adha 1448 H', 'hari-besar', 'genap'],
            ['2027-05-20', null, 'Hari Raya Waisak 2571 BE', 'hari-besar', 'genap'],
            ['2027-05-22', null, 'Ngaji Guru', 'kegiatan', 'genap'],
            ['2027-05-24', '2027-06-03', 'Sumatif Akhir Tahun (SAT)', 'ujian', 'genap'],
            ['2027-05-25', '2027-06-07', 'Input Nilai Rapor SAT', 'ujian', 'genap'],
            ['2027-06-01', null, 'Hari Lahir Pancasila', 'hari-besar', 'genap'],
            ['2027-06-05', '2027-06-07', 'Remidial SAT', 'ujian', 'genap'],
            ['2027-06-06', null, 'Tahun Baru Islam 1449 H', 'hari-besar', 'genap'],
            ['2027-06-08', null, 'Pleno Kenaikan Kelas', 'kegiatan', 'genap'],
            ['2027-06-09', '2027-06-10', 'Cetak & TTD Rapor', 'kegiatan', 'genap'],
            ['2027-06-12', null, 'Penyerahan Rapor Semester Genap', 'kegiatan', 'genap'],
            ['2027-06-14', '2027-07-10', 'Libur Semester Genap', 'libur', 'genap'],
            ['2027-06-19', null, 'Haflah Akhirussanah ke-77 (Putra)', 'kegiatan', 'genap'],
            ['2027-06-20', null, 'Haflah Akhirussanah ke-77 (Putri)', 'kegiatan', 'genap'],
            ['2027-07-06', '2027-07-07', 'Pengembalian Santri Putra & Putri', 'kegiatan', 'genap'],
            ['2027-07-07', null, 'Mukim Santri Baru 2027', 'kegiatan', 'genap'],
            ['2027-07-12', null, 'Awal Masuk / PBM 2027/2028', 'kegiatan', 'genap'],
            ['2027-07-12', '2027-07-15', 'MPLS / MATAMUDA TA 2027/2028', 'kegiatan', 'genap'],

            // ---- Perkiraan (tanpa tanggal pasti) ----
            ['2027-02-01', '2027-04-30', 'Perkiraan Pelaksanaan UKK (Februari–April 2027)', 'ujian', 'genap'],
        ];

        foreach ($events as $e) {
            DB::table('academic_calendar_events')->insert([
                'date' => $e[0],
                'date_end' => $e[1],
                'title' => $e[2],
                'category' => $e[3],
                'semester' => $e[4],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_events');
    }
};
