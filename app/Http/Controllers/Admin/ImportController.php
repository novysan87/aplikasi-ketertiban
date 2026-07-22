<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViolationCategory;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'Kategori');
        $sheet->setCellValue('B1', 'Nama Pelanggaran');
        $sheet->setCellValue('C1', 'Poin');
        $sheet->setCellValue('D1', 'Sanksi Default');
        $sheet->setCellValue('E1', 'Deskripsi');

        // Bold header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        // Contoh baris
        $sheet->setCellValue('A2', 'Ringan');
        $sheet->setCellValue('B2', 'Terlambat masuk kelas');
        $sheet->setCellValue('C2', 5);
        $sheet->setCellValue('D2', 'Teguran lisan');
        $sheet->setCellValue('E2', 'Terlambat masuk kelas setelah bel berbunyi');

        $sheet->setCellValue('A3', 'Sedang');
        $sheet->setCellValue('B3', 'Membuang sampah sembarangan');
        $sheet->setCellValue('C3', 20);
        $sheet->setCellValue('D3', 'Membersihkan lingkungan selama 1 jam');

        // Autofit column width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Data validation for kategori column
        $categories = ViolationCategory::where('is_active', true)->pluck('name')->toArray();
        if (!empty($categories)) {
            $validation = $sheet->getCell('A2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setFormula1('"' . implode(',', $categories) . '"');
            $validation->setAllowBlank(false);
            $validation->setShowDropDown(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template-jenis-pelanggaran.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importViolationTypes(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File kosong. Isi minimal 1 baris data.');
            }

            // Validate header
            $header = array_map('trim', $rows[0]);
            $expectedHeader = ['Kategori', 'Nama Pelanggaran', 'Poin', 'Sanksi Default', 'Deskripsi'];
            if (array_slice($header, 0, 4) !== array_slice($expectedHeader, 0, 4)) {
                return back()->with('error', 'Format kolom tidak sesuai. Gunakan template yang disediakan.');
            }

            $categories = ViolationCategory::where('is_active', true)->get()->keyBy(fn($c) => strtolower(trim($c->name)));
            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach (array_slice($rows, 1) as $index => $row) {
                $rowNum = $index + 2;

                $categoryName = trim($row[0] ?? '');
                $typeName = trim($row[1] ?? '');
                $points = trim($row[2] ?? '');
                $sanction = trim($row[3] ?? '');
                $description = trim($row[4] ?? '');

                // Skip baris kosong
                if (empty($categoryName) && empty($typeName)) {
                    continue;
                }

                // Validasi: nama pelanggaran wajib
                if (empty($typeName)) {
                    $errors[] = "Baris {$rowNum}: Nama Pelanggaran tidak boleh kosong.";
                    $skipped++;
                    continue;
                }

                // Validasi: poin harus angka
                if (!is_numeric($points) || $points < 0) {
                    $errors[] = "Baris {$rowNum} ('{$typeName}'): Poin harus angka positif.";
                    $skipped++;
                    continue;
                }

                // Cari kategori
                $catKey = strtolower($categoryName);
                if (empty($categoryName) || !isset($categories[$catKey])) {
                    $errors[] = "Baris {$rowNum} ('{$typeName}'): Kategori '{$categoryName}' tidak ditemukan. Buat kategori terlebih dahulu.";
                    $skipped++;
                    continue;
                }

                $category = $categories[$catKey];

                // Cek duplikat nama
                $exists = ViolationType::where('name', $typeName)
                    ->where('category_id', $category->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                ViolationType::create([
                    'category_id' => $category->id,
                    'name' => $typeName,
                    'slug' => Str::slug($typeName . '-' . $category->id),
                    'points' => (int) $points,
                    'default_sanction' => $sanction ?: null,
                    'description' => $description ?: null,
                    'is_active' => true,
                ]);

                $imported++;
            }

            $message = "Berhasil mengimpor {$imported} jenis pelanggaran.";
            if ($skipped > 0) {
                $message .= " {$skipped} dilewati (duplikat/error).";
            }

            if (!empty($errors)) {
                $message .= ' ' . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= ' (+' . (count($errors) - 5) . ' error lainnya)';
                }
            }

            if ($imported > 0) {
                return back()->with('success', $message);
            } else {
                return back()->with('error', 'Tidak ada data baru yang diimpor. ' . implode(', ', array_slice($errors, 0, 3)));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }
}
