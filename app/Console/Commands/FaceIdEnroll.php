<?php

namespace App\Console\Commands;

use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;

/**
 * Enroll massal embedding wajah siswa (fase pilot).
 *
 * Format CSV (header wajib):
 *   student_id,name,photo1,photo2,photo3,twin_group
 *   2026001,Budi Santoso,budi-1.jpg,budi-2.jpg,budi-3.jpg,
 *   2026002,Ayu Lestari,ayu-1.jpg,ayu-2.jpg,ayu-3.jpg,TW-07
 *
 * Jalankan:
 *   php artisan faceid:enroll /path/data.csv --photos=/path/foto
 */
class FaceIdEnroll extends Command
{
    protected $signature = 'faceid:enroll
        {csv : Path file CSV (kolom: student_id,name,photo1,photo2,photo3,twin_group)}
        {--photos= : Direktori basis foto; path relatif di CSV di-resolve dari sini}';

    protected $description = 'Enroll massal embedding wajah siswa dari CSV + folder foto (fase pilot).';

    public function handle(FaceRecognitionService $faceid): int
    {
        $csv = $this->argument('csv');
        if (! is_file($csv)) {
            $this->error("File CSV tidak ditemukan: {$csv}");

            return self::FAILURE;
        }
        if (! $faceid->isConfigured()) {
            $this->error('FaceID belum dikonfigurasi (FACEID_BASE_URL / FACEID_API_KEY di .env).');

            return self::FAILURE;
        }

        $photosDir = $this->option('photos') ?: dirname($csv);
        $rows = array_map('str_getcsv', file($csv));
        $header = array_shift($rows);
        if (! $header) {
            $this->error('CSV kosong / header tidak ditemukan.');

            return self::FAILURE;
        }
        $idx = array_flip($header);

        $ok = 0;
        $fail = 0;
        foreach ($rows as $row) {
            if (count($row) < 2) {
                continue;
            }

            $studentId = trim($row[$idx['student_id'] ?? 0] ?? '');
            $name = trim($row[$idx['name'] ?? 1] ?? '');
            if ($studentId === '' || $name === '') {
                $fail++;
                continue;
            }

            $paths = [];
            foreach (['photo1', 'photo2', 'photo3'] as $col) {
                if (! isset($idx[$col])) {
                    continue;
                }
                $p = trim($row[$idx[$col]] ?? '');
                if ($p === '') {
                    continue;
                }
                $full = $p[0] === '/' ? $p : rtrim($photosDir, '/').'/'.$p;
                if (is_file($full)) {
                    $paths[] = $full;
                }
            }

            if (! $paths) {
                $this->warn("  [skip] {$studentId}: tidak ada foto valid");

                $fail++;
                continue;
            }

            $twin = null;
            if (isset($idx['twin_group'])) {
                $twin = trim($row[$idx['twin_group']] ?? '') ?: null;
            }

            $res = $faceid->enroll($studentId, $name, $paths, $twin);
            if ($res['ok']) {
                $this->info("  [ok] {$studentId} ({$name}): ".$res['data']['photos_accepted'].' foto diterima');
                $ok++;
            } else {
                $this->error("  [gagal] {$studentId} ({$name}): ".($res['error'] ?? 'unknown'));
                $fail++;
            }
        }

        $this->newLine();
        $this->info("Selesai: {$ok} berhasil, {$fail} gagal/skip.");

        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }
}
