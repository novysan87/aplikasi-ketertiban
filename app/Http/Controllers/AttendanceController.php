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

        // Cek status presensi per kelas untuk tanggal ini
        $classAttendanceStatus = [];
        foreach ($classNames as $cn) {
            $studentIdsInClass = Student::where('is_active', true)
                ->where('class_name', $cn)
                ->pluck('id');
            $count = Attendance::where('date', $date)
                ->whereIn('student_id', $studentIdsInClass)
                ->distinct('student_id')
                ->count('student_id');
            $totalStudents = $studentIdsInClass->count();
            $classAttendanceStatus[$cn] = [
                'has_data' => $count > 0,
                'recorded' => $count,
                'total' => $totalStudents,
            ];
        }

        $lessonHours = range(1, 10);

        return view('attendances.create', compact(
            'students', 'date', 'className',
            'classNames', 'lessonHours', 'existing',
            'classAttendanceStatus'
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

        // Auto-generate / update / hapus violation (anti-duplikat)
        if ($request->boolean('auto_violation')) {
            $alphaType = ViolationType::where('slug', 'alpha')->first();
            $terlambatType = ViolationType::where('slug', 'terlambat')->first();
            $userId = $request->user()->id;

            // Kumpulkan semua student yang ada di form
            $allStudentsInForm = array_keys($request->input('attendances', []));

            // Proses alpha
            if ($alphaType) {
                foreach ($allStudentsInForm as $studentId) {
                    $count = $alphaIds[$studentId] ?? 0;
                    $existing = \App\Models\Violation::where('student_id', $studentId)
                        ->where('violation_type_id', $alphaType->id)
                        ->where('violation_date', $date)
                        ->where('recorded_by', $userId)
                        ->first();

                    if ($count > 0) {
                        $points = max(1, (int) round(($alphaType->points / 10) * $count));
                        $desc = "Alpha - Tidak hadir tanpa keterangan ({$count} jam pelajaran)";

                        if ($existing) {
                            $existing->update(['points' => $points, 'description' => $desc, 'notes' => 'Diperbarui otomatis dari presensi.']);
                        } else {
                            $this->violationService->recordViolation([
                                'student_id' => $studentId,
                                'violation_type_id' => $alphaType->id,
                                'violation_date' => $date,
                                'points' => $points,
                                'description' => $desc,
                                'notes' => 'Dibuat otomatis dari presensi.',
                                'evidences' => [],
                            ], $userId);
                        }
                        $autoViolations++;
                    } elseif ($existing) {
                        // Tidak alpha lagi tapi ada violation lama → hapus
                        $existing->delete();
                        $autoViolations++;
                    }
                }
            }

            // Proses terlambat (similar logic, tanpa delete karena terlambat per event)
            if ($terlambatType) {
                foreach ($terlambatIds as $studentId => $count) {
                    $existing = \App\Models\Violation::where('student_id', $studentId)
                        ->where('violation_type_id', $terlambatType->id)
                        ->where('violation_date', $date)
                        ->where('recorded_by', $userId)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'points' => $terlambatType->points,
                            'description' => "Terlambat ({$count} jam pelajaran)",
                            'notes' => 'Diperbarui otomatis dari presensi.',
                        ]);
                    } else {
                        $this->violationService->recordViolation([
                            'student_id' => $studentId,
                            'violation_type_id' => $terlambatType->id,
                            'violation_date' => $date,
                            'points' => $terlambatType->points,
                            'description' => "Terlambat ({$count} jam pelajaran)",
                            'notes' => 'Dibuat otomatis dari presensi.',
                            'evidences' => [],
                        ], $userId);
                    }
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
        $type = $request->input('type', 'daily');
        $className = $request->input('class_name');

        $classNames = Student::where('is_active', true)
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        if ($type === 'daily') {
            return $this->recapDaily($request, $classNames, $type);
        } elseif ($type === 'weekly') {
            return $this->recapWeekly($request, $classNames, $type);
        } else {
            return $this->recapMonthly($request, $classNames, $type);
        }
    }

    protected function recapDaily(Request $request, $classNames, $type = 'daily'): View
    {
        $date = $request->input('date', now()->toDateString());
        $className = $request->input('class_name');

        $students = collect();
        $attendances = collect();
        if ($className) {
            $students = Student::where('is_active', true)
                ->where('class_name', $className)
                ->orderBy('full_name')
                ->get();

            $attendances = Attendance::where('date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy(fn($a) => $a->student_id . '-' . $a->lesson_hour);
        }

        $lessonHours = range(1, 10);

        return view('attendances.recap', compact(
            'type', 'date', 'className', 'classNames',
            'students', 'attendances', 'lessonHours'
        ));
    }

    protected function recapWeekly(Request $request, $classNames, $type = 'weekly'): View
    {
        $weekStart = $request->input('week_start', now()->startOfWeek()->toDateString());
        $start = \Carbon\Carbon::parse($weekStart)->startOfWeek();
        $end = (clone $start)->addDays(5); // Senin - Sabtu
        $className = $request->input('class_name');

        $students = collect();
        $recap = collect();
        $totals = [];
        if ($className) {
            $students = Student::where('is_active', true)
                ->where('class_name', $className)
                ->orderBy('full_name')
                ->get();

            $allAttendances = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy(fn($a) => \Illuminate\Support\Carbon::parse($a->date)->toDateString() . '-' . $a->student_id);

            $dayLabels = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
            for ($i = 0; $i <= 5; $i++) {
                $day = (clone $start)->addDays($i);
                $dateStr = $day->toDateString();

                $row = ['label' => $dayLabels[$i], 'date' => $dateStr, 'students' => []];
                foreach ($students as $student) {
                    $key = $dateStr . '-' . $student->id;
                    $studentAtt = $allAttendances->get($key, collect());

                    // Per hari: binary untuk hadir/sakit/izin/terlambat
                    $hadir = $studentAtt->where('status', 'hadir')->count() > 0 ? 1 : 0;
                    // Alpha proporsional: jumlah jam alpha / 10 (max jam sekolah per hari)
                    $alphaCount = $studentAtt->where('status', 'alpha')->count();
                    $alpha = round($alphaCount / 10, 1);
                    $sakit = $studentAtt->where('status', 'sakit')->count() > 0 ? 1 : 0;
                    $izin = $studentAtt->where('status', 'izin')->count() > 0 ? 1 : 0;
                    $terlambat = $studentAtt->where('status', 'terlambat')->count() > 0 ? 1 : 0;
                    $total = $studentAtt->count();

                    $row['students'][$student->id] = [
                        'name' => $student->full_name,
                        'hadir' => $hadir,
                        'alpha' => $alpha,
                        'sakit' => $sakit,
                        'izin' => $izin,
                        'terlambat' => $terlambat,
                        'total' => $studentAtt->count(),
                    ];
                }
                $recap->push($row);
            }

            // Hitung total per siswa selama seminggu
            $totals = [];
            foreach ($students as $student) {
                $totals[$student->id] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
            }
            foreach ($recap as $row) {
                foreach ($row['students'] as $sid => $s) {
                    $totals[$sid]['hadir'] += $s['hadir'];
                    $totals[$sid]['sakit'] += $s['sakit'];
                    $totals[$sid]['izin'] += $s['izin'];
                    $totals[$sid]['alpha'] += $s['alpha'];
                    $totals[$sid]['terlambat'] += $s['terlambat'];
                }
            }
        }

        return view('attendances.recap', compact(
            'type', 'start', 'end', 'className', 'classNames',
            'students', 'recap', 'totals'
        ));
    }

    protected function recapMonthly(Request $request, $classNames, $type = 'monthly'): View
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $className = $request->input('class_name');

        $firstDay = \Carbon\Carbon::create($year, $month, 1);
        $lastDay = \Carbon\Carbon::create($year, $month, $firstDay->daysInMonth);

        // Generate minggu dalam bulan
        $weeks = [];
        $weekStart = (clone $firstDay)->startOfWeek();
        $weekNum = 1;
        while ($weekStart <= $lastDay) {
            $weekEnd = (clone $weekStart)->addDays(6);
            $weeks[$weekNum] = [
                'label' => 'Minggu ' . $weekNum,
                'start' => $weekStart->copy()->max($firstDay)->toDateString(),
                'end' => $weekEnd->copy()->min($lastDay)->toDateString(),
            ];
            $weekStart->addWeek();
            $weekNum++;
        }

        $students = collect();
        $recap = collect();
        $totals = [];
        if ($className) {
            $students = Student::where('is_active', true)
                ->where('class_name', $className)
                ->orderBy('full_name')
                ->get();

            $allAttendances = Attendance::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();

            foreach ($weeks as $weekNum => $week) {
                $weekAtt = $allAttendances->filter(fn($a) =>
                    $a->date >= $week['start'] && $a->date <= $week['end']
                );

                // Ambil tanggal unik dalam minggu ini
                $datesInWeek = $weekAtt->map(fn($a) => \Illuminate\Support\Carbon::parse($a->date)->toDateString())->unique();
                $totalDays = $datesInWeek->count();

                $weekByDay = $weekAtt->groupBy(fn($a) => \Illuminate\Support\Carbon::parse($a->date)->toDateString());

                $row = ['label' => $week['label'], 'start' => $week['start'], 'end' => $week['end'], 'students' => []];
                foreach ($students as $student) {
                    $hCount = 0; $sCount = 0; $iCount = 0; $aCount = 0; $tCount = 0; $totalDaysForStudent = 0;

                    foreach ($datesInWeek as $dateStr) {
                        $dayAtt = $weekByDay->get($dateStr, collect())->where('student_id', $student->id);
                        if ($dayAtt->isEmpty()) continue;
                        $totalDaysForStudent++;

                        $hasHadir = $dayAtt->where('status', 'hadir')->isNotEmpty();
                        $hasSakit = $dayAtt->where('status', 'sakit')->isNotEmpty();
                        $hasIzin = $dayAtt->where('status', 'izin')->isNotEmpty();
                        $hasAlpha = $dayAtt->where('status', 'alpha')->isNotEmpty();
                        $hasTerlambat = $dayAtt->where('status', 'terlambat')->isNotEmpty();

                        if ($hasHadir) $hCount++;
                        if ($hasSakit) $sCount++;
                        if ($hasIzin) $iCount++;
                        if ($hasTerlambat) $tCount++;

                        // Alpha proporsional: jumlah jam alpha / 10 (max jam sekolah per hari)
                        $alphaJam = $dayAtt->where('status', 'alpha')->count();
                        $aCount += round($alphaJam / 10, 1);
                    }

                    $row['students'][$student->id] = [
                        'name' => $student->full_name,
                        'hadir' => $hCount,
                        'alpha' => $aCount,
                        'sakit' => $sCount,
                        'izin' => $iCount,
                        'terlambat' => $tCount,
                        'total' => $totalDaysForStudent,
                    ];
                }
                $recap->push($row);
            }

            // Hitung total per siswa selama sebulan
            $totals = [];
            foreach ($students as $student) {
                $totals[$student->id] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0];
            }
            foreach ($recap as $row) {
                foreach ($row['students'] as $sid => $s) {
                    $totals[$sid]['hadir'] += $s['hadir'];
                    $totals[$sid]['sakit'] += $s['sakit'];
                    $totals[$sid]['izin'] += $s['izin'];
                    $totals[$sid]['alpha'] += $s['alpha'];
                    $totals[$sid]['terlambat'] += $s['terlambat'];
                }
            }
        }

        return view('attendances.recap', compact(
            'type', 'month', 'year', 'className', 'classNames',
            'students', 'recap', 'weeks', 'totals'
        ));
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

    public function exportWeekly(Request $request)
    {
        $classNames = Student::where('is_active', true)
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $view = $this->recapWeekly($request, $classNames);
        $data = $view->getData();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $title = 'Rekap Presensi Mingguan';
        if ($data['className']) {
            $title .= ' - ' . $data['className'];
        }
        $title .= ' (' . \Carbon\Carbon::parse($data['start'])->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($data['end'])->format('d/m/Y') . ')';

        $sheet->setCellValue('A1', $title);
            $totalCols = 1 + $data['recap']->count() * 4 + 4; // +4 untuk kolom TOTAL
        $sheet->mergeCells('A1:' . $this->getColLetter($totalCols - 1) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Header row 2: day names + TOTAL
        $sheet->setCellValue('A2', 'Siswa');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $col = 2;
        foreach ($data['recap'] as $row) {
            $cell = $this->getColLetter($col) . '2';
            $sheet->setCellValue($cell, $row['label'] . ' ' . \Carbon\Carbon::parse($row['date'])->format('d/m'));
            $endCell = $this->getColLetter($col + 3) . '2';
            $sheet->mergeCells($cell . ':' . $endCell);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $col += 4;
        }
        // Kolom TOTAL
        $totalCell = $this->getColLetter($col) . '2';
        $sheet->setCellValue($totalCell, 'TOTAL');
        $sheet->mergeCells($totalCell . ':' . $this->getColLetter($col + 3) . '2');
        $sheet->getStyle($totalCell)->getFont()->setBold(true);

        // Header row 3: H, S, I, A per day + TOTAL
        $sheet->setCellValue('A3', '');
        $col = 2;
        foreach ($data['recap'] as $row) {
            $sheet->setCellValue($this->getColLetter($col) . '3', 'H');
            $sheet->setCellValue($this->getColLetter($col + 1) . '3', 'S');
            $sheet->setCellValue($this->getColLetter($col + 2) . '3', 'I');
            $sheet->setCellValue($this->getColLetter($col + 3) . '3', 'A');
            foreach (range(0, 3) as $i) {
                $sheet->getStyle($this->getColLetter($col + $i) . '3')->getFont()->setBold(true);
            }
            $col += 4;
        }
        // Sub-header TOTAL
        $sheet->setCellValue($this->getColLetter($col) . '3', 'H');
        $sheet->setCellValue($this->getColLetter($col + 1) . '3', 'S');
        $sheet->setCellValue($this->getColLetter($col + 2) . '3', 'I');
        $sheet->setCellValue($this->getColLetter($col + 3) . '3', 'A');
        foreach (range(0, 3) as $i) {
            $sheet->getStyle($this->getColLetter($col + $i) . '3')->getFont()->setBold(true);
        }

        // Data rows
        $rowIdx = 4;
        foreach ($data['students'] as $student) {
            $sheet->setCellValue('A' . $rowIdx, $student->full_name);
            $col = 2;
            foreach ($data['recap'] as $row) {
                $s = $row['students'][$student->id] ?? null;
                if ($s) {
                    $sheet->setCellValue($this->getColLetter($col) . $rowIdx, $s['hadir']);
                    $sheet->setCellValue($this->getColLetter($col + 1) . $rowIdx, $s['sakit']);
                    $sheet->setCellValue($this->getColLetter($col + 2) . $rowIdx, $s['izin']);
                    $sheet->setCellValue($this->getColLetter($col + 3) . $rowIdx, $s['alpha']);
                } else {
                    foreach (range(0, 3) as $i) {
                        $sheet->setCellValue($this->getColLetter($col + $i) . $rowIdx, '-');
                    }
                }
                $col += 4;
            }
            // TOTAL per siswa
            $t = $data['totals'][$student->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
            $sheet->setCellValue($this->getColLetter($col) . $rowIdx, $t['hadir']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $rowIdx, $t['sakit']);
            $sheet->setCellValue($this->getColLetter($col + 2) . $rowIdx, $t['izin']);
            $sheet->setCellValue($this->getColLetter($col + 3) . $rowIdx, $t['alpha']);

            $rowIdx++;
        }

        // Auto width
        for ($c = 1; $c < $totalCols; $c++) {
            $sheet->getColumnDimension($this->getColLetter($c))->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'rekap-mingguan' . ($data['className'] ? '-' . $data['className'] : '') . '.xlsx';
        $filename = str_replace(['/', '\\', ' '], '-', $filename);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportMonthly(Request $request)
    {
        $classNames = Student::where('is_active', true)
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $view = $this->recapMonthly($request, $classNames);
        $data = $view->getData();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $title = 'Rekap Presensi Bulanan';
        if ($data['className']) {
            $title .= ' - ' . $data['className'];
        }
        $title .= ' (' . \Carbon\Carbon::create()->month($data['month'])->format('F') . ' ' . $data['year'] . ')';

        $sheet->setCellValue('A1', $title);
        $totalCols = 1 + $data['recap']->count() * 4 + 4; // +4 untuk kolom TOTAL
        $sheet->mergeCells('A1:' . $this->getColLetter($totalCols - 1) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Header row 2: week names + TOTAL
        $sheet->setCellValue('A2', 'Siswa');
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $col = 2;
        foreach ($data['recap'] as $row) {
            $cell = $this->getColLetter($col) . '2';
            $sheet->setCellValue($cell, $row['label'] . ' ' . \Carbon\Carbon::parse($row['start'])->format('d/m') . '-' . \Carbon\Carbon::parse($row['end'])->format('d/m'));
            $endCell = $this->getColLetter($col + 3) . '2';
            $sheet->mergeCells($cell . ':' . $endCell);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $col += 4;
        }
        // Kolom TOTAL
        $totalCell = $this->getColLetter($col) . '2';
        $sheet->setCellValue($totalCell, 'TOTAL');
        $sheet->mergeCells($totalCell . ':' . $this->getColLetter($col + 3) . '2');
        $sheet->getStyle($totalCell)->getFont()->setBold(true);

        // Header row 3: H, S, I, A per week + TOTAL
        $sheet->setCellValue('A3', '');
        $col = 2;
        foreach ($data['recap'] as $row) {
            $sheet->setCellValue($this->getColLetter($col) . '3', 'H');
            $sheet->setCellValue($this->getColLetter($col + 1) . '3', 'S');
            $sheet->setCellValue($this->getColLetter($col + 2) . '3', 'I');
            $sheet->setCellValue($this->getColLetter($col + 3) . '3', 'A');
            foreach (range(0, 3) as $i) {
                $sheet->getStyle($this->getColLetter($col + $i) . '3')->getFont()->setBold(true);
            }
            $col += 4;
        }
        // Sub-header TOTAL
        $sheet->setCellValue($this->getColLetter($col) . '3', 'H');
        $sheet->setCellValue($this->getColLetter($col + 1) . '3', 'S');
        $sheet->setCellValue($this->getColLetter($col + 2) . '3', 'I');
        $sheet->setCellValue($this->getColLetter($col + 3) . '3', 'A');
        foreach (range(0, 3) as $i) {
            $sheet->getStyle($this->getColLetter($col + $i) . '3')->getFont()->setBold(true);
        }

        // Data rows
        $rowIdx = 4;
        foreach ($data['students'] as $student) {
            $sheet->setCellValue('A' . $rowIdx, $student->full_name);
            $col = 2;
            foreach ($data['recap'] as $row) {
                $s = $row['students'][$student->id] ?? null;
                if ($s) {
                    $sheet->setCellValue($this->getColLetter($col) . $rowIdx, $s['hadir']);
                    $sheet->setCellValue($this->getColLetter($col + 1) . $rowIdx, $s['sakit']);
                    $sheet->setCellValue($this->getColLetter($col + 2) . $rowIdx, $s['izin']);
                    $sheet->setCellValue($this->getColLetter($col + 3) . $rowIdx, $s['alpha']);
                } else {
                    foreach (range(0, 3) as $i) {
                        $sheet->setCellValue($this->getColLetter($col + $i) . $rowIdx, '-');
                    }
                }
                $col += 4;
            }
            // TOTAL per siswa
            $t = $data['totals'][$student->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
            $sheet->setCellValue($this->getColLetter($col) . $rowIdx, $t['hadir']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $rowIdx, $t['sakit']);
            $sheet->setCellValue($this->getColLetter($col + 2) . $rowIdx, $t['izin']);
            $sheet->setCellValue($this->getColLetter($col + 3) . $rowIdx, $t['alpha']);

            $rowIdx++;
        }

        // Auto width
        for ($c = 1; $c < $totalCols; $c++) {
            $sheet->getColumnDimension($this->getColLetter($c))->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'rekap-bulanan' . ($data['className'] ? '-' . $data['className'] : '') . '.xlsx';
        $filename = str_replace(['/', '\\', ' '], '-', $filename);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function getColLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + $col % 26) . $letter;
            $col = intval($col / 26);
        }
        return $letter;
    }

}