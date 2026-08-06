<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ParentDevice;
use App\Models\ParentStudent;
use App\Models\Setting;
use App\Models\Student;
use App\Models\ViolationNotification;
use App\Models\ViolationType;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSyncController extends Controller
{
    /**
     * Receive attendance push from E-Jurnal.
     * Auto-generate violations for alpha students (with notifications).
     */
    /**
     * Test connection — validate token and return simple status.
     * GET /api/v1/attendance/ping?token=***
     */
    public function ping(Request $request)
    {
        $expectedToken = Setting::getValue('ejurnal_sync_token', '');
        $providedToken = $request->input('token');

        if (empty($expectedToken) || $providedToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Koneksi OK.',
            'data' => [
                'app' => config('app.name', 'Aplikasi Ketertiban'),
                'students' => Student::where('is_active', true)->count(),
                'token_valid' => true,
            ],
        ]);
    }

    public function sync(Request $request)
    {
        $expectedToken = Setting::getValue('ejurnal_sync_token', '');
        $providedToken = $request->input('token');

        if (empty($expectedToken) || $providedToken !== $expectedToken) {
            Log::warning('Attendance sync: invalid token', [
                'ip' => $request->ip(),
                'provided' => substr((string)$providedToken, 0, 10),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing sync token.',
            ], 401);
        }

        $request->validate([
            'attendances' => 'required|array|min:1|max:500',
            'attendances.*.source_student_id' => 'required|integer',
            'attendances.*.date' => 'required|date',
            'attendances.*.lesson_hour' => 'required|integer|min:1|max:10',
            'attendances.*.status' => 'required|in:hadir,sakit,izin,alpha',
            'attendances.*.notes' => 'nullable|string|max:255',
        ]);

        $attendances = $request->input('attendances');
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'violations' => 0,
        ];

        $sourceIds = array_unique(array_column($attendances, 'source_student_id'));
        $students = Student::whereIn('source_id', $sourceIds)
            ->where('is_active', true)
            ->get(['id', 'source_id', 'class_name']);
        $studentMap = $students->pluck('id', 'source_id');
        $classNameMap = $students->pluck('class_name', 'id');

        $alphaCounts = [];
        $alphaDate = null;
        $allStudentIdsInPush = []; // track all students for cleanup

        foreach ($attendances as $item) {
            $sourceId = $item['source_student_id'];

            if (!isset($studentMap[$sourceId])) {
                $results['skipped']++;
                $results['errors'][] = "Student source_id={$sourceId} not found";
                continue;
            }

            $localStudentId = $studentMap[$sourceId];
            $allStudentIdsInPush[$localStudentId] = true;

            $statusMap = [
                'present' => 'hadir',
                'sick' => 'sakit',
                'permission' => 'izin',
                'absent' => 'alpha',
            ];
            $localStatus = $statusMap[$item['status']] ?? $item['status'];

            try {
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $localStudentId,
                        'date' => $item['date'],
                        'lesson_hour' => $item['lesson_hour'],
                    ],
                    [
                        'status' => $localStatus,
                        'class_name' => $classNameMap[$localStudentId] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'recorded_by' => null,
                    ]
                );

                if ($attendance->wasRecentlyCreated) {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }

                if ($localStatus === 'alpha') {
                    $alphaCounts[$localStudentId] = ($alphaCounts[$localStudentId] ?? 0) + 1;
                    $alphaDate = $item['date'];
                }
            } catch (\Exception $e) {
                $results['errors'][] = "student_id={$sourceId} jam={$item['lesson_hour']}: " . $e->getMessage();
                $results['skipped']++;
            }
        }

        // Auto-generate / update / hapus violations untuk alpha siswa di push ini
        $alphaType = ViolationType::where('slug', 'alpha')->first();

        if ($alphaType) {
            $violationService = app(\App\Services\ViolationService::class);

            foreach ($allStudentIdsInPush as $studentId => $_) {
                try {
                    // Hitung total alpha di DB (akumulasi dari semua push, bukan cuma push ini)
                    $totalAlpha = \App\Models\Attendance::where('student_id', $studentId)
                        ->where('date', $alphaDate)
                        ->where('status', 'alpha')
                        ->count();

                    // Cari existing system-generated violation
                    $existing = \App\Models\Violation::where('student_id', $studentId)
                        ->where('violation_type_id', $alphaType->id)
                        ->where('violation_date', $alphaDate)
                        ->whereNull('recorded_by')
                        ->first();

                    if ($totalAlpha > 0) {
                        // Ada alpha → buat/update violation
                        $points = max(1, (int) round(($alphaType->points / 10) * $totalAlpha));
                        $desc = "Alpha - Tidak hadir tanpa keterangan ({$totalAlpha} jam pelajaran)";

                        if ($existing) {
                            $existing->update([
                                'points' => $points,
                                'description' => $desc,
                                'notes' => 'Diperbarui otomatis dari sinkron E-Jurnal.',
                            ]);
                        } else {
                            $violationService->recordViolation([
                                'student_id' => $studentId,
                                'violation_type_id' => $alphaType->id,
                                'violation_date' => $alphaDate,
                                'points' => $points,
                                'description' => $desc,
                                'notes' => 'Dibuat otomatis dari sinkron E-Jurnal.',
                                'evidences' => [],
                            ], null);
                        }
                        $results['violations']++;
                    } elseif ($existing) {
                        // Tidak ada alpha tapi ada violation lama → hapus (ganti status ke hadir)
                        $existing->delete();
                        $results['violations']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Violation sync failed for student={$studentId}: " . $e->getMessage();
                }
            }
        } else {
            $results['errors'][] = 'Violation type "Alpha" not found. Create a violation type with slug "alpha".';
        }

        // ===== Notifikasi kehadiran ke wali (hadir/izin/sakit — alpha sudah via violation) =====
        try {
            $this->notifyAttendanceToParents($attendances, $studentMap, $classNameMap);
        } catch (\Throwable $e) {
            Log::error('Notifikasi kehadiran wali gagal: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Sync completed: {$results['created']} created, {$results['updated']} updated, {$results['skipped']} skipped, {$results['violations']} violations.",
            'results' => $results,
        ]);
    }

    /**
     * Kirim notifikasi kehadiran per sesi ke wali yang tertaut aktif.
     * Dedup: satu notifikasi per (siswa, tanggal, status) per hari.
     */
    protected function notifyAttendanceToParents(array $attendances, $studentMap, $classNameMap): void
    {
        // Kelompokkan item dalam batch ini: (student_id, date, status) → jam pertama/terakhir
        $groups = [];
        foreach ($attendances as $item) {
            $sourceId = $item['source_student_id'];
            if (! isset($studentMap[$sourceId])) {
                continue;
            }
            $localId = $studentMap[$sourceId];
            $status = $item['status'];
            if ($status === 'absent') {
                continue; // alpha sudah dinotifikasi lewat alur violation
            }
            $key = $localId.'|'.$item['date'].'|'.$status;
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'student_id' => $localId,
                    'date' => $item['date'],
                    'status' => $status,
                    'hours' => [],
                ];
            }
            $groups[$key]['hours'][] = (int) $item['lesson_hour'];
        }

        $statusLabel = ['present' => 'hadir', 'sick' => 'sakit', 'permission' => 'izin'];
        $statusLocal = ['present' => 'hadir', 'sick' => 'sakit', 'permission' => 'izin'];

        foreach ($groups as $g) {
            $student = Student::find($g['student_id']);
            if (! $student) {
                continue;
            }

            $links = ParentStudent::where('student_id', $student->id)
                ->where('status', 'active')
                ->get();
            if ($links->isEmpty()) {
                continue;
            }

            // Konteks: mapel + rentang waktu dari e-jurnal
            $ctx = $this->attendanceContext($student->class_name, $g['date']);
            $label = $statusLabel[$g['status']] ?? $g['status'];
            $subject = $ctx['subject'] ?? 'pembelajaran';
            $time = $ctx['start'] && $ctx['end'] ? " ({$ctx['start']}-{$ctx['end']})" : '';
            $message = 'Kehadiran: '.$student->full_name.' — '.$label.' di '.$subject.$time;

            // Dedup: cek notifikasi serupa hari ini
            $exists = ViolationNotification::where('student_id', $student->id)
                ->where('channel', 'attendance')
                ->where('message', $message)
                ->whereDate('created_at', today())
                ->exists();
            if ($exists) {
                continue;
            }

            $data = [
                'student_name' => $student->full_name,
                'status' => $label,
                'type' => 'attendance_'.$label,
            ];

            foreach ($links as $link) {
                ViolationNotification::create([
                    'student_id' => $student->id,
                    'channel' => 'attendance',
                    'recipient' => $link->user?->name ?? 'Wali',
                    'message' => $message,
                    'status' => 'sent',
                    'user_id' => $link->user_id,
                    'created_at' => now(),
                ]);

                $tokens = ParentDevice::where('user_id', $link->user_id)->pluck('fcm_token');
                foreach ($tokens as $token) {
                    app(FcmService::class)->sendToToken($token, [
                        'title' => 'Kehadiran: '.$student->full_name,
                        'body' => $label.' di '.$subject.$time,
                    ], $data);
                }
            }
        }
    }

    /**
     * Mapel + rentang waktu untuk kelas & tanggal tertentu (dari e-jurnal).
     */
    protected function attendanceContext(?string $className, string $date): array
    {
        try {
            $db = DB::connection('ejurnal');
            $yearId = $db->table('academic_years')->where('is_active', 1)->value('id');
            $semesterId = $db->table('semesters')->where('is_active', 1)->value('id');
            $dayKey = strtolower(date('l', strtotime($date)));

            $subject = $db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->join('subjects as sub', 'sub.id', '=', 's.subject_id')
                ->where('c.name', $className)
                ->where('s.day_of_week', $dayKey)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->value('sub.name');

            $start = $db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->where('c.name', $className)
                ->where('s.day_of_week', $dayKey)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->orderBy('s.start_time')
                ->value('s.start_time');
            $end = $db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->where('c.name', $className)
                ->where('s.day_of_week', $dayKey)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->orderByDesc('s.end_time')
                ->value('s.end_time');

            return [
                'subject' => $subject,
                'start' => $start ? substr($start, 0, 5) : null,
                'end' => $end ? substr($end, 0, 5) : null,
            ];
        } catch (\Throwable $e) {
            Log::error('attendanceContext gagal: '.$e->getMessage());

            return ['subject' => null, 'start' => null, 'end' => null];
        }
    }
}
