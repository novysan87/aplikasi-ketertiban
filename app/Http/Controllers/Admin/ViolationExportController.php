<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Violation;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ViolationExportController extends Controller
{
    public function export(Request $request)
    {
        $query = Violation::with([
            'student', 'violationType.category', 'recorder',
            'handlings.participants.user', 'handler',
        ]);

        if ($request->filled('date_from')) {
            $query->whereDate('violation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('violation_date', '<=', $request->date_to);
        }

        $violations = $query->orderBy('violation_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pelanggaran');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Tanggal',
            'C1' => 'Waktu',
            'D1' => 'NISN',
            'E1' => 'Nama Siswa',
            'F1' => 'Kelas',
            'G1' => 'Jurusan',
            'H1' => 'Jenis Pelanggaran',
            'I1' => 'Kategori',
            'J1' => 'Poin',
            'K1' => 'Sanksi',
            'L1' => 'Lokasi',
            'M1' => 'Deskripsi',
            'N1' => 'Dicatat Oleh',
            'O1' => 'Status Verifikasi',
            'P1' => 'Status Penanganan',
            'Q1' => 'Penanganan',
            'R1' => 'Yang Menangani',
            'S1' => 'Tanggal Dibuat',
        ];

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(7);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(40);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(18);
        $sheet->getColumnDimension('P')->setWidth(18);
        $sheet->getColumnDimension('Q')->setWidth(30);
        $sheet->getColumnDimension('R')->setWidth(30);
        $sheet->getColumnDimension('S')->setWidth(16);

        // Data
        $row = 2;
        foreach ($violations as $i => $v) {
            $handlingTypes = $v->handlings->pluck('handling_type')->implode(', ');
            $participants = $v->handlings->flatMap(function ($h) {
                return $h->participants->map(fn($p) => ($p->user->name ?? '') . ($p->role ? " ({$p->role})" : ''));
            })->implode(', ');

            $statusVerifikasi = $v->is_verified ? 'Terverifikasi' : 'Belum';
            $statusLabels = ['unhandled' => 'Belum Ditangani', 'in_progress' => 'Dalam Proses', 'resolved' => 'Selesai'];
            $statusPenanganan = $statusLabels[$v->handling_status] ?? $v->handling_status;

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $v->violation_date->format('d/m/Y'));
            $sheet->setCellValue("C{$row}", $v->violation_time ? \Carbon\Carbon::parse($v->violation_time)->format('H:i') : '');
            $sheet->setCellValue("D{$row}", $v->student->nisn ?? '');
            $sheet->setCellValue("E{$row}", $v->student->full_name ?? '');
            $sheet->setCellValue("F{$row}", $v->student->class_name ?? '');
            $sheet->setCellValue("G{$row}", $v->student->department_name ?? '');
            $sheet->setCellValue("H{$row}", $v->violationType->name ?? '');
            $sheet->setCellValue("I{$row}", $v->violationType?->category?->name ?? '');
            $sheet->setCellValue("J{$row}", $v->points);
            $sheet->setCellValue("K{$row}", $v->sanction ?? '');
            $sheet->setCellValue("L{$row}", $v->location ?? '');
            $sheet->setCellValue("M{$row}", $v->description ?? '');
            $sheet->setCellValue("N{$row}", $v->recorder->name ?? '');
            $sheet->setCellValue("O{$row}", $statusVerifikasi);
            $sheet->setCellValue("P{$row}", $statusPenanganan);
            $sheet->setCellValue("Q{$row}", $handlingTypes);
            $sheet->setCellValue("R{$row}", $participants);
            $sheet->setCellValue("S{$row}", $v->created_at->format('d/m/Y H:i'));

            // Alternate row color
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$row}:S{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}:S{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $row++;
        }

        // Auto filter
        $sheet->setAutoFilter("A1:S" . ($row - 1));

        // Borders
        $styleArray = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
            ],
        ];
        $sheet->getStyle("A1:S" . ($row - 1))->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        $filename = 'data-pelanggaran-' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
