<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\ViolationType;
use App\Services\ViolationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    protected ViolationService $violationService;

    public function __construct(ViolationService $violationService)
    {
        $this->violationService = $violationService;
    }

    public function index(Request $request): View
    {
        $today = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear = now()->year;

        // Hitung berdasarkan jumlah siswa unik, bukan total record per jam
        $todayStudents = \App\Models\Attendance::where('date', $today)
            ->distinct('student_id')
            ->count('student_id');

        $todayAlphaStudents = \App\Models\Attendance::where('date', $today)
            ->where('status', 'alpha')
            ->distinct('student_id')
            ->count('student_id');

        $monthStudents = \App\Models\Attendance::whereMonth('date', $thisMonth)
            ->whereYear('date', $thisYear)
            ->distinct('student_id')
            ->count('student_id');

        $monthAlphaStudents = \App\Models\Attendance::whereMonth('date', $thisMonth)
            ->whereYear('date', $thisYear)
            ->where('status', 'alpha')
            ->distinct('student_id')
            ->count('student_id');

        // Calendar data for initial month
        $calendarStart = now()->startOfMonth()->toDateString();
        $calendarEnd = now()->endOfMonth()->toDateString();

        $totalRows = \App\Models\Attendance::selectRaw('date, COUNT(DISTINCT student_id) as total')
            ->whereBetween('date', [$calendarStart, $calendarEnd])
            ->groupBy('date')
            ->pluck('total', 'date');

        $alphaRows = \App\Models\Attendance::selectRaw('date, COUNT(DISTINCT student_id) as alpha')
            ->whereBetween('date', [$calendarStart, $calendarEnd])
            ->where('status', 'alpha')
            ->groupBy('date')
            ->pluck('alpha', 'date');

        $calendarData = [];
        foreach ($totalRows as $date => $total) {
            $calendarData[$date] = [
                'total' => $total,
                'alpha' => $alphaRows[$date] ?? 0,
            ];
        }

        return view('attendances.index', compact(
            'todayStudents', 'todayAlphaStudents',
            'monthStudents', 'monthAlphaStudents',
            'calendarData'
        ));
    }

    public function create(Request $request): View
    {
        $date = $request->input('date', now()->toDateString());
        $className = $request->input('class_name');

        $students = collect();
        if ($className) {
            $students = Student::where('is_active', true)
                ->where('class_name', $className)
                ->orderBy('full_name')
                ->get();
        }

        // Load existing attendances for this class + date
        $existing = collect();
        if ($students->isNotEmpty()) {
            $existing = Attendance::where('date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy(fn($a) => $a->student_id . '-' . $a->lesson_hour);
        }

        $classNames = Student::where('is_active', true)
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $lessonHours = range(1, 10);

        return view('attendances.create', compact(
            'students', 'date', 'className',
            'classNames', 'lessonHours', 'existing'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'class_name' => ['nullable', 'string'],
            'attendances' => ['required', 'array'],
            'auto_violation' => ['nullable', 'boolean'],
        ]);

        $date = $request->input('date');
        $class_name = $request->input('class_name');
        $saved = 0;
        $autoViolations = 0;
        $alphaIds = [];
        $terlambatIds = [];

        foreach ($request->input('attendances') as $studentId => $studentData) {
            foreach ($studentData as $hour => $data) {
                if (!isset($data['student_id'], $data['lesson_hour'], $data['status'])) {
                    continue;
                }
                $data['lesson_hour'] = (int) $data['lesson_hour'];
                Attendance::updateOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'date' => $date,
                        'lesson_hour' => $data['lesson_hour'],
                    ],
                    [
                        'status' => $data['status'],
                        'recorded_by' => $request->user()->id,
                    ]
                );
                $saved++;

                if ($data['status'] === 'alpha') {
                    $alphaIds[$data['student_id']] = ($alphaIds[$data['student_id']] ?? 0) + 1;
                } elseif ($data['status'] === 'terlambat') {
                    $terlambatIds[$data['student_id']] = ($terlambatIds[$data['student_id']] ?? 0) + 1;
                }
            }
        }

        // Auto-generate violation untuk alpha & terlambat
        if ($request->boolean('auto_violation') && (!empty($alphaIds) || !empty($terlambatIds))) {
            $alphaType = ViolationType::where('slug', 'alpha')->first();
            $terlambatType = ViolationType::where('slug', 'terlambat')->first();

            foreach ($alphaIds as $studentId => $count) {
                if ($alphaType) {
                    $this->violationService->recordViolation([
                        'student_id' => $studentId,
                        'violation_type_id' => $alphaType->id,
                        'violation_date' => $date,
                        'points' => max(1, (int) round(($alphaType->points / 10) * $count)),
                        'description' => "Alpha - Tidak hadir tanpa keterangan ({$count} jam pelajaran)",
                        'notes' => 'Dibuat otomatis dari presensi.',
                        'evidences' => [],
                    ], $request->user()->id);
                    $autoViolations++;
                }
            }

            foreach ($terlambatIds as $studentId => $count) {
                if ($terlambatType) {
                    $this->violationService->recordViolation([
                        'student_id' => $studentId,
                        'violation_type_id' => $terlambatType->id,
                        'violation_date' => $date,
                        'points' => $terlambatType->points,
                        'description' => "Terlambat ({$count} jam pelajaran)",
                        'notes' => 'Dibuat otomatis dari presensi.',
                        'evidences' => [],
                    ], $request->user()->id);
                    $autoViolations++;
                }
            }

            if (!$alphaType) {
                $msgExtra = 'Jenis pelanggaran "Alpha" tidak ditemukan. Buat jenis pelanggaran dengan slug "alpha".';
            }
            if (!$terlambatType) {
                $msgExtra = (empty($msgExtra) ? '' : $msgExtra . ' ')
                    . 'Jenis pelanggaran "Terlambat" tidak ditemukan. Buat dengan slug "terlambat".';
            }
        }

        $studentCount = count($request->input('attendances'));
        $lessonHourCount = $saved / max($studentCount, 1);
        $msg = "Presensi berhasil disimpan — {$studentCount} siswa × " . round($lessonHourCount) . " jam pelajaran.";
        if ($autoViolations > 0) {
            $msg .= " {$autoViolations} pelanggaran dibuat otomatis (alpha/terlambat).";
        } elseif (!empty($msgExtra)) {
            $msg .= " Peringatan: {$msgExtra}";
        }

        return redirect()->route('attendances.create', [
            'date' => $date,
            'class_name' => $class_name,
        ])->with('success', $msg);
    }

    public function recap(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $className = $request->input('class_name');

        $query = Student::where('is_active', true);
        if ($className) {
            $query->where('class_name', $className);
        }
        $students = $query->orderBy('class_name')->orderBy('full_name')->get();

        $recap = [];
        foreach ($students as $student) {
            $attendances = Attendance::where('student_id', $student->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy('status')
                ->map(fn($g) => $g->count());

            $recap[] = (object) [
                'student' => $student,
                'hadir' => $attendances->get('hadir', 0),
                'sakit' => $attendances->get('sakit', 0),
                'izin' => $attendances->get('izin', 0),
                'alpha' => $attendances->get('alpha', 0),
                'terlambat' => $attendances->get('terlambat', 0),
                'total' => $attendances->sum(),
            ];
        }

        $classNames = Student::where('is_active', true)
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        return view('attendances.recap', compact('recap', 'month', 'year', 'className', 'classNames'));
    }

    public function calendarData(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $daysInMonth = now()->setDate($year, $month, 1)->daysInMonth;

        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        // Total unique students per date
        $totalRows = Attendance::selectRaw('date, COUNT(DISTINCT student_id) as total')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Alpha unique students per date
        $alphaRows = Attendance::selectRaw('date, COUNT(DISTINCT student_id) as alpha')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'alpha')
            ->groupBy('date')
            ->pluck('alpha', 'date');

        $result = [];
        foreach ($totalRows as $date => $total) {
            $result[$date] = [
                'total' => $total,
                'alpha' => $alphaRows[$date] ?? 0,
            ];
        }

        return response()->json($result);
    }

}