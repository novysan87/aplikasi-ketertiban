<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
use App\Models\PointAuditLog;
use App\Models\SpLetter;
use App\Models\Student;
use App\Models\Violation;
use App\Support\PhoneHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ParentStudentController extends Controller
{
    /**
     * Daftar anak milik wali (termasuk yang statusnya pending verifikasi).
     */
    public function index(Request $request): JsonResponse
    {
        $payload = app(ParentAuthController::class)->studentPayloads($request->user());

        return response()->json(['students' => $payload]);
    }

    /**
     * Jadwal pelajaran mingguan kelas siswa (diambil dari database e-jurnal).
     */
    public function schedule(Request $request, Student $student): JsonResponse
    {
        // Hanya wali dengan tautan aktif yang boleh melihat
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        try {
            $db = DB::connection('ejurnal');
            $yearId = $db->table('academic_years')->where('is_active', 1)->value('id');
            $semesterId = $db->table('semesters')->where('is_active', 1)->value('id');

            $rows = $db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->join('subjects as sub', 'sub.id', '=', 's.subject_id')
                ->leftJoin('teachers as t', 't.id', '=', 's.teacher_id')
                ->leftJoin('users as u', 'u.id', '=', 't.user_id')
                ->where('c.name', $student->class_name)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->orderBy('s.day_of_week')
                ->orderBy('s.start_time')
                ->get(['s.day_of_week', 's.start_time', 's.end_time', 'sub.name as subject', 'u.name as teacher']);

            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $grouped = [];
            foreach ($days as $day) {
                $items = $rows->where('day_of_week', $day)->values()->map(fn ($r) => [
                    'start' => substr($r->start_time, 0, 5),
                    'end' => substr($r->end_time, 0, 5),
                    'subject' => $r->subject,
                    'teacher' => $r->teacher,
                ]);
                if ($items->isNotEmpty()) {
                    $grouped[] = [
                        'day' => $day,
                        'items' => $items,
                    ];
                }
            }

            return response()->json([
                'class_name' => $student->class_name,
                'schedule' => $grouped,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jadwal gagal dimuat: '.$e->getMessage());

            return response()->json(['message' => 'Jadwal belum tersedia untuk kelas ini.'], 404);
        }
    }

    /**
     * Rekap kehadiran putra/putri (bulan berjalan + ringkasan).
     */
    public function attendance(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $first = $student->attendances()->orderBy('date')->value('date');
        $from = $first && $first > $monthStart ? $first : $monthStart;

        $rows = $student->attendances()
            ->whereBetween('date', [$from, $today])
            ->orderBy('date')
            ->orderBy('lesson_hour')
            ->get(['date', 'status', 'lesson_hour']);

        $statuses = ['hadir', 'izin', 'sakit', 'alpha'];

        // ---- Detail per jam: waktu (dari time_slots e-jurnal) + mapel (dari schedule) ----
        $jamTimes = [];
        try {
            $db = DB::connection('ejurnal');
            $slots = $db->table('time_slots')
                ->where('is_break', 0)
                ->orderBy('period')
                ->get(['period', 'start_time', 'end_time']);
            $map = [];
            $idx = 1;
            foreach ($slots as $s) {
                $map[$s->period] = $idx++;
            }
            foreach ($slots as $s) {
                $jamTimes[$map[$s->period]] = [
                    substr($s->start_time, 0, 5),
                    substr($s->end_time, 0, 5),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Detail jam tidak tersedia: '.$e->getMessage());
        }

        $subjectByDay = [];
        try {
            $db = DB::connection('ejurnal');
            $yearId = $db->table('academic_years')->where('is_active', 1)->value('id');
            $semesterId = $db->table('semesters')->where('is_active', 1)->value('id');
            foreach ($db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->join('subjects as sub', 'sub.id', '=', 's.subject_id')
                ->where('c.name', $student->class_name)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->get(['s.day_of_week', 'sub.name as subject']) as $row) {
                $subjectByDay[$row->day_of_week] = $row->subject;
            }
        } catch (\Throwable $e) {
            Log::error('Mapel detail tidak tersedia: '.$e->getMessage());
        }

        $dayEnglish = [
            'Sunday' => 'sunday', 'Monday' => 'monday', 'Tuesday' => 'tuesday',
            'Wednesday' => 'wednesday', 'Thursday' => 'thursday', 'Friday' => 'friday',
            'Saturday' => 'saturday',
        ];

        $byDate = $rows->groupBy('date');
        $records = $byDate
            ->map(function ($items, $date) use ($statuses, $jamTimes, $subjectByDay, $dayEnglish) {
                $counts = [];
                foreach ($statuses as $s) {
                    $counts[$s] = $items->where('status', $s)->count();
                }
                $primary = null;
                foreach ($statuses as $s) {
                    if ($counts[$s] > 0) {
                        $primary = $s;
                        break;
                    }
                }

                $dayKey = $dayEnglish[date('l', strtotime((string) $date))] ?? null;
                $subject = $dayKey ? ($subjectByDay[$dayKey] ?? null) : null;

                $details = $items->sortBy('lesson_hour')->map(function ($item) use ($jamTimes, $subject) {
                    $t = $jamTimes[$item->lesson_hour] ?? [null, null];

                    return [
                        'hour' => (int) $item->lesson_hour,
                        'start' => $t[0],
                        'end' => $t[1],
                        'subject' => $subject,
                        'status' => $item->status,
                    ];
                })->values();

                return [
                    'date' => \Illuminate\Support\Str::substr((string) $date, 0, 10),
                    'primary' => $primary,
                    'statuses' => $counts,
                    'details' => $details,
                ];
            })
            ->values()
            ->sortByDesc('date')
            ->values();

        // Ringkasan berbasis HARI (bukan jam pelajaran):
        // 1 hari = 1 status utama (urutan prioritas hadir > izin > sakit > alpha).
        $summary = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        foreach ($byDate as $items) {
            foreach ($statuses as $s) {
                if ($items->where('status', $s)->count() > 0) {
                    $summary[$s]++;
                    break;
                }
            }
        }
        $summary['days'] = $byDate->count();
        $summary['based_on'] = 'days';

        return response()->json([
            'from' => $from,
            'month' => now()->format('Y-m'),
            'summary' => $summary,
            'records' => $records,
        ]);
    }

    /**
     * Riwayat poin putra/putri (peristiwa + grafik bulanan).
     */
    public function pointsHistory(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        $logs = PointAuditLog::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $events = $logs->map(function (PointAuditLog $l) {
            $meta = $l->metadata ?? [];

            return [
                'id' => $l->id,
                'date' => $l->created_at?->toDateString(),
                'description' => $l->description,
                'detail' => $meta['description'] ?? null,
                'delta' => (int) $l->points_delta,
                'total_after' => (int) $l->points_after,
                'action' => $l->action,
            ];
        })->values();

        // Grafik 6 bulan terakhir (net poin per bulan)
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $key = $m->format('Y-m');
            $monthNumber = (int) $m->format('n');
            $sum = $logs->filter(fn (PointAuditLog $l) => $l->created_at?->format('Y-m') === $key)
                ->sum('points_delta');
            $monthly[] = [
                'month' => $key,
                'month_number' => $monthNumber,
                'points' => (int) $sum,
            ];
        }

        return response()->json([
            'total_points' => (int) $student->total_points,
            'events' => $events,
            'monthly' => $monthly,
        ]);
    }

    /**
     * Statistik pelanggaran putra/putri (per kategori, jenis, tren bulanan).
     */
    public function violationsStats(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        $violations = $student->violations()
            ->with('violationType.category')
            ->get();

        $totalCount = $violations->count();
        $totalPoints = (int) $student->total_points;

        // Per kategori (Ringan/Sedang/Berat)
        $categories = $violations
            ->groupBy(fn ($v) => $v->violationType?->category?->id ?? 0)
            ->map(function ($items, $catId) {
                $cat = $items->first()->violationType?->category;

                return [
                    'name' => $cat?->name ?? 'Lainnya',
                    'color' => $cat?->color ?? '#94a3b8',
                    'count' => $items->count(),
                    'points' => (int) $items->sum('points'),
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->values();

        // Jenis pelanggaran teratas
        $topTypes = $violations
            ->groupBy(fn ($v) => $v->violation_type_id)
            ->map(function ($items) {
                return [
                    'name' => $items->first()->violationType?->name ?? 'Lainnya',
                    'count' => $items->count(),
                    'points' => (int) $items->sum('points'),
                ];
            })
            ->values()
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Tren 6 bulan terakhir
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $key = $m->format('Y-m');
            $monthly[] = [
                'month' => $key,
                'month_number' => (int) $m->format('n'),
                'count' => $violations
                    ->filter(fn ($v) => $v->violation_date?->format('Y-m') === $key)
                    ->count(),
            ];
        }

        return response()->json([
            'total_count' => $totalCount,
            'total_points' => $totalPoints,
            'categories' => $categories,
            'top_types' => $topTypes,
            'monthly' => $monthly,
        ]);
    }

    /**
     * Nilai putra/putri (dari teacher_grades e-jurnal, dikelompokkan per mapel).
     */
    public function grades(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        try {
            $db = DB::connection('ejurnal');
            $ejStudent = $db->table('students')->where('nisn', $student->nisn)->first();
            if (! $ejStudent) {
                return response()->json(['subjects' => [], 'overall_average' => null]);
            }

            $rows = $db->table('teacher_grades as tg')
                ->join('schedules as s', 's.id', '=', 'tg.schedule_id')
                ->join('subjects as sub', 'sub.id', '=', 's.subject_id')
                ->where('tg.student_id', $ejStudent->id)
                ->orderBy('sub.name')
                ->orderByDesc('tg.date')
                ->get(['tg.label', 'tg.score', 'tg.category', 'tg.date', 'sub.name as subject']);

            $bySubject = $rows->groupBy('subject');
            $subjects = $bySubject->map(function ($items, $subject) {
                $scores = $items->pluck('score')->map(fn ($s) => (int) $s);

                return [
                    'subject' => $subject,
                    'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
                    'entries' => $items->map(fn ($r) => [
                        'label' => $r->label,
                        'score' => (int) $r->score,
                        'category' => $r->category,
                        'date' => $r->date,
                    ])->values(),
                ];
            })->values();

            $allScores = $rows->pluck('score')->map(fn ($s) => (int) $s);

            return response()->json([
                'subjects' => $subjects,
                'overall_average' => $allScores->isNotEmpty() ? round($allScores->avg(), 1) : null,
                'total_entries' => $rows->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Nilai gagal dimuat: '.$e->getMessage());

            return response()->json(['message' => 'Nilai belum tersedia.'], 404);
        }
    }

    /**
     * Daftar teman sekelas + wali kelas putra/putri.
     */
    public function classmates(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        // Wali kelas dari database kesiswaan
        $homeroom = null;
        try {
            $homeroom = DB::connection('kesiswaan')
                ->table('classes')
                ->where('name', $student->class_name)
                ->where('is_active', 1)
                ->orderByDesc('id')
                ->value('homeroom_teacher');
        } catch (\Throwable $e) {
            Log::error('Wali kelas tidak tersedia: '.$e->getMessage());
        }

        // Teman sekelas dari tabel students (aktif)
        $students = Student::where('class_name', $student->class_name)
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender'])
            ->map(fn (Student $s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'gender' => $s->gender, // L | P
            ])
            ->values();

        return response()->json([
            'class_name' => $student->class_name,
            'homeroom_teacher' => $homeroom,
            'students' => $students,
            'total' => $students->count(),
        ]);
    }

    /**
     * Prestasi putra/putri (dari student_achievements database kesiswaan).
     */
    public function achievements(Request $request, Student $student): JsonResponse
    {
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        try {
            $rows = DB::connection('kesiswaan')
                ->table('student_achievements')
                ->where('student_id', $student->source_id)
                ->orderByDesc('achievement_date')
                ->orderByDesc('achievement_year')
                ->get();

            $items = $rows->map(fn ($r) => [
                'name' => $r->achievement_name,
                'category' => $r->category,
                'level' => $r->level,
                'organizer' => $r->organizer,
                'rank' => $r->rank,
                'date' => $r->achievement_date,
                'year' => $r->achievement_year,
                'description' => $r->description,
            ])->values();

            return response()->json([
                'achievements' => $items,
                'total' => $items->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Prestasi gagal dimuat: '.$e->getMessage());

            return response()->json(['achievements' => [], 'total' => 0]);
        }
    }

    /**
     * Tautkan anak tambahan (kakak/adik) ke akun wali.
     */
    public function link(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:30'],
            'parent_phone' => ['required', 'string', 'min:9', 'max:20'],
            'relation' => ['nullable', Rule::in(['father', 'mother', 'guardian'])],
        ]);

        $user = $request->user();
        $nisn = trim($validated['nisn']);

        $student = Student::query()
            ->where('nisn', $nisn)
            ->where('is_active', true)
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'NISN tidak ditemukan. Periksa kembali NISN anak Anda.',
                'errors' => ['nisn' => ['NISN tidak ditemukan atau siswa tidak aktif.']],
            ], 422);
        }

        $exists = ParentStudent::where('user_id', $user->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Siswa ini sudah tertaut ke akun Anda.',
                'errors' => ['nisn' => ['Sudah tertaut.']],
            ], 422);
        }

        $autoVerified = PhoneHelper::matches(trim($validated['parent_phone']), $student->parent_phone);

        // Simpan nomor HP wali bila akun belum punya.
        if (empty($user->phone)) {
            $user->update(['phone' => trim($validated['parent_phone'])]);
        }

        $link = ParentStudent::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'relation' => $validated['relation'] ?? null,
            'status' => $autoVerified ? 'active' : 'pending',
            'verified_at' => $autoVerified ? now() : null,
        ]);

        return response()->json([
            'message' => $autoVerified
                ? 'Siswa berhasil ditautkan dan langsung aktif.'
                : 'Siswa ditautkan, menunggu verifikasi pihak sekolah.',
            'link_id' => $link->id,
            'link_status' => $link->status,
            'needs_verification' => ! $autoVerified,
        ], 201);
    }

    /**
     * Riwayat pelanggaran anak (hanya untuk tautan aktif).
     */
    public function violations(Request $request, int $student): JsonResponse
    {
        $user = $request->user();
        $student = $this->assertActiveChild($user, $student);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $violations = Violation::query()
            ->where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->with(['violationType.category', 'evidences'])
            ->orderByDesc('violation_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        $items = collect($violations->items())->map(function (Violation $v) {
            return [
                'id' => $v->id,
                'violation_date' => $v->violation_date?->toDateString(),
                'violation_time' => $v->violation_time?->format('H:i'),
                'description' => $v->description,
                'location' => $v->location,
                'points' => $v->points,
                'sanction' => $v->sanction,
                'handling_status' => $v->handling_status,
                'is_verified' => (bool) $v->is_verified,
                'type' => $v->violationType ? [
                    'name' => $v->violationType->name,
                    'category' => $v->violationType->category?->name,
                ] : null,
                'evidences' => $v->evidences->map(fn ($e) => [
                    'id' => $e->id,
                    'url' => url($e->file_path),
                    'mime_type' => $e->mime_type,
                ])->all(),
            ];
        });

        return response()->json([
            'student' => $this->studentBrief($student),
            'total_points' => $student->total_points,
            'violations' => $items,
            'pagination' => [
                'current_page' => $violations->currentPage(),
                'last_page' => $violations->lastPage(),
                'per_page' => $violations->perPage(),
                'total' => $violations->total(),
            ],
        ]);
    }

    /**
     * Daftar surat SP anak (selain draft).
     */
    public function spLetters(Request $request, int $student): JsonResponse
    {
        $user = $request->user();
        $student = $this->assertActiveChild($user, $student);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $letters = SpLetter::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'draft')
            ->orderByDesc('created_at')
            ->get();

        $items = $letters->map(function (SpLetter $sp) {
            return [
                'id' => $sp->id,
                'letter_number' => $sp->letter_number,
                'title' => $sp->title,
                'body' => $sp->body,
                'status' => $sp->status,
                'total_points_at_time' => $sp->total_points_at_time,
                'violations_included' => $sp->violations_included,
                'file_url' => $sp->file_path ? url($sp->file_path) : null,
                'created_at' => $sp->created_at?->toISOString(),
                'delivered_at' => $sp->delivered_at?->toISOString(),
            ];
        });

        return response()->json([
            'student' => $this->studentBrief($student),
            'sp_letters' => $items,
        ]);
    }

    /**
     * Pastikan siswa adalah anak aktif dari user. Return model atau JsonResponse 403.
     */
    protected function assertActiveChild($user, int $studentId): Student|JsonResponse
    {
        $link = ParentStudent::where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->first();

        if (! $link) {
            return response()->json([
                'message' => 'Akses ditolak. Siswa tidak tertaut atau masih menunggu verifikasi.',
            ], 403);
        }

        return $link->student;
    }

    protected function studentBrief(Student $student): array
    {
        return [
            'id' => $student->id,
            'nisn' => $student->nisn,
            'full_name' => $student->full_name,
            'class_name' => $student->class_name,
            'department_name' => $student->department_name,
        ];
    }
}
