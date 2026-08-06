<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Setting;
use App\Models\ViolationType;
use App\Models\Violation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Laporan Rekap Pelanggaran (PDF) — untuk BK / Kepala Sekolah.
 */
class ViolationReportController extends Controller
{
    public function index(Request $request): View
    {
        $classes = Classes::orderBy('name')->get();
        $violationTypes = ViolationType::with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();
        $filters = $this->filters($request);

        $query = $this->baseQuery($filters);
        $stats = $this->stats($query);

        $recent = (clone $query)
            ->with(['student.class', 'violationType'])
            ->orderByDesc('violation_date')
            ->limit(20)
            ->get();

        return view('reports.violations', compact('classes', 'violationTypes', 'filters', 'stats', 'recent'));
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request, validate: true);
        \Illuminate\Support\Facades\Log::error('PDF-FILTER-DEBUG', ['query' => $request->query(), 'filters' => $filters]);

        $violations = $this->baseQuery($filters)
            ->with(['student.class', 'violationType.category', 'handlings'])
            ->orderBy('violation_date')
            ->get();

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
            'kop_logo' => Setting::getValue('kop_logo', ''),
            'government' => Setting::getValue('school_government', 'PEMERINTAH PROVINSI JAWA TIMUR'),
            'agency' => Setting::getValue('school_agency', 'DINAS PENDIDIKAN'),
            'full_name' => Setting::getValue('school_full_name', Setting::getValue('school_name', 'SMK')),
            'address_detail' => Setting::getValue('school_address_detail', ''),
            'website_email' => Setting::getValue('school_website_email', ''),
            'postal' => Setting::getValue('school_postal', ''),
            'kepala_sekolah' => Setting::getValue('kepala_sekolah_name', ''),
            'kepala_sekolah_nip' => Setting::getValue('kepala_sekolah_nip', ''),
        ];

        $periode = ($filters['date_from'] ?? 'Awal').' s/d '.($filters['date_to'] ?? 'Sekarang');
        $filterKelas = $filters['class_id'] ? (Classes::find($filters['class_id'])?->name ?? '-') : 'Semua Kelas';
        $filterJenis = $filters['violation_type_id']
            ? (ViolationType::find($filters['violation_type_id'])?->name ?? '-')
            : 'Semua Jenis Pelanggaran';

        $pdf = Pdf::loadView('reports.violation-report-pdf', compact(
            'violations', 'summary', 'perJenis', 'perKelas', 'school', 'periode', 'filterKelas', 'filterJenis'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('rekap-pelanggaran-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * Ambil & validasi filter dari request.
     *
     * @return array{date_from: ?string, date_to: ?string, class_id: ?int}
     */
    protected function filters(Request $request, bool $validate = false): array
    {
        $rules = [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'violation_type_id' => ['nullable', 'integer', 'exists:violation_types,id'],
        ];

        $data = $validate ? $request->validate($rules) : $request->validate($rules);

        return [
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'class_id' => isset($data['class_id']) ? (int) $data['class_id'] : null,
            'violation_type_id' => isset($data['violation_type_id']) ? (int) $data['violation_type_id'] : null,
        ];
    }

    /**
     * Query dasar pelanggaran: scoping wali kelas + filter periode & kelas.
     */
    protected function baseQuery(array $filters): Builder
    {
        return Violation::query()
            ->when($filters['date_from'], fn ($q) => $q->whereDate('violation_date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('violation_date', '<=', $filters['date_to']))
            ->when($filters['class_id'], fn ($q) => $q->whereHas('student', fn ($qq) => $qq->where('class_id', $filters['class_id'])))
            ->when($filters['violation_type_id'], fn ($q) => $q->where('violation_type_id', $filters['violation_type_id']))
            ->when(auth()->user()->isScopedWaliKelas(), fn ($q) => $q->whereHas('student', fn ($qq) => $qq->whereIn('class_id', auth()->user()->homeroomClassIds())));
    }

    /**
     * Statistik ringkas + rekap preview untuk halaman.
     */
    protected function stats(Builder $query): array
    {
        $totalKasus = (clone $query)->count();
        $totalPoin = (clone $query)->sum('points');
        $siswaTerlibat = (clone $query)->distinct('student_id')->count('student_id');

        $rows = (clone $query)
            ->with(['violationType.category', 'student.class'])
            ->get(['id', 'student_id', 'violation_type_id', 'points', 'violation_date']);

        $perJenis = $rows->groupBy(fn ($v) => $v->violationType?->name ?? 'Tanpa Jenis')
            ->map(fn ($group) => [
                'jenis' => $group->first()->violationType?->name ?? 'Tanpa Jenis',
                'kategori' => $group->first()->violationType?->category?->name ?? '-',
                'jumlah' => $group->count(),
                'poin' => $group->sum('points'),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $perKelas = $rows->groupBy(fn ($v) => $v->student?->class?->name ?? 'Tanpa Kelas')
            ->map(fn ($group) => [
                'kelas' => $group->first()->student?->class?->name ?? 'Tanpa Kelas',
                'jumlah' => $group->count(),
                'poin' => $group->sum('points'),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $jenisTerbanyak = $perJenis->first();

        return compact('totalKasus', 'totalPoin', 'siswaTerlibat', 'perJenis', 'perKelas', 'jenisTerbanyak');
    }
}
