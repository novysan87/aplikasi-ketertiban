<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Setting;
use App\Models\Violation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Laporan Rekap Pelanggaran (PDF) — untuk BK / Kepala Sekolah.
 */
class ViolationReportController extends Controller
{
    public function index(): View
    {
        $classes = Classes::orderBy('name')->get();

        $query = Violation::query()
            ->when(auth()->user()->isScopedWaliKelas(), fn ($q) => $q->whereHas('student', fn ($qq) => $qq->whereIn('class_id', auth()->user()->homeroomClassIds())));

        $totalKasus = (clone $query)->count();
        $totalPoin = (clone $query)->sum('points');
        $siswaTerlibat = (clone $query)->distinct('student_id')->count('student_id');

        return view('reports.violations', compact('classes', 'totalKasus', 'totalPoin', 'siswaTerlibat'));
    }

    public function pdf(Request $request)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $query = Violation::with(['student.class', 'violationType.category', 'handlings'])
            ->when($data['date_from'] ?? null, fn ($q) => $q->whereDate('violation_date', '>=', $data['date_from']))
            ->when($data['date_to'] ?? null, fn ($q) => $q->whereDate('violation_date', '<=', $data['date_to']))
            ->when($data['class_id'] ?? null, fn ($q) => $q->whereHas('student', fn ($qq) => $qq->where('class_id', $data['class_id'])))
            // Wali kelas murni: hanya kelas yang diwalikan
            ->when(auth()->user()->isScopedWaliKelas(), fn ($q) => $q->whereHas('student', fn ($qq) => $qq->whereIn('class_id', auth()->user()->homeroomClassIds())));

        $violations = $query->orderBy('violation_date')->get();

        $summary = [
            'total_kasus' => $violations->count(),
            'total_poin' => $violations->sum('points'),
            'siswa_terlibat' => $violations->pluck('student_id')->unique()->count(),
        ];

        $perJenis = $violations->groupBy(fn ($v) => $v->violationType?->name ?? 'Tanpa Jenis')
            ->map(fn ($group) => [
                'jenis' => $group->first()->violationType?->name ?? 'Tanpa Jenis',
                'kategori' => $group->first()->violationType?->category?->name ?? '-',
                'jumlah' => $group->count(),
                'poin' => $group->sum('points'),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $perKelas = $violations->groupBy(fn ($v) => $v->student?->class?->name ?? 'Tanpa Kelas')
            ->map(fn ($group) => [
                'kelas' => $group->first()->student?->class?->name ?? 'Tanpa Kelas',
                'jumlah' => $group->count(),
                'poin' => $group->sum('points'),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $school = [
            'name' => Setting::getValue('school_name', 'SMK'),
            'address' => Setting::getValue('school_address', ''),
            'phone' => Setting::getValue('school_phone', ''),
            'logo' => Setting::getValue('school_logo', ''),
            'kepala_sekolah' => Setting::getValue('kepala_sekolah_name', ''),
            'kepala_sekolah_nip' => Setting::getValue('kepala_sekolah_nip', ''),
        ];

        $periode = ($data['date_from'] ?? 'Awal').' s/d '.($data['date_to'] ?? 'Sekarang');
        $filterKelas = $data['class_id'] ?? null
            ? Classes::find($data['class_id'])?->name
            : 'Semua Kelas';

        $pdf = Pdf::loadView('reports.violation-report-pdf', compact(
            'violations', 'summary', 'perJenis', 'perKelas', 'school', 'periode', 'filterKelas'
        ))->setPaper('a4', 'landscape');

        $filename = 'rekap-pelanggaran-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }
}
