<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceSyncController extends Controller
{
    /**
     * Receive attendance push from E-Jurnal.
     * Auto-generate violations for alpha students (with notifications).
     */
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
        $studentMap = Student::whereIn('source_id', $sourceIds)
            ->where('is_active', true)
            ->pluck('id', 'source_id');

        $alphaCounts = [];
        $alphaDate = null;

        foreach ($attendances as $item) {
            $sourceId = $item['source_student_id'];

            if (!isset($studentMap[$sourceId])) {
                $results['skipped']++;
                $results['errors'][] = "Student source_id={$sourceId} not found";
                continue;
            }

            $localStudentId = $studentMap[$sourceId];

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

        // Auto-generate / update violations for alpha students
        if (!empty($alphaCounts)) {
            $alphaType = ViolationType::where('slug', 'alpha')->first();

            if ($alphaType) {
                $violationService = app(\App\Services\ViolationService::class);
                foreach ($alphaCounts as $studentId => $count) {
                    try {
                        $points = max(1, (int) round(($alphaType->points / 10) * $count));
                        $desc = "Alpha - Tidak hadir tanpa keterangan ({$count} jam pelajaran)";

                        // Cek apakah sudah ada pelanggaran alpha untuk siswa+ tanggal+type yang sama
                        // (system-generated: recorded_by IS NULL)
                        $existing = \App\Models\Violation::where('student_id', $studentId)
                            ->where('violation_type_id', $alphaType->id)
                            ->where('violation_date', $alphaDate)
                            ->whereNull('recorded_by')
                            ->first();

                        if ($existing) {
                            // Update existing violation (points & description mungkin berubah)
                            $existing->update([
                                'points' => $points,
                                'description' => $desc,
                                'notes' => 'Diperbarui otomatis dari sinkron E-Jurnal.',
                            ]);
                            $results['violations']++;
                        } else {
                            // Buat baru (via service biar notifikasi & SP threshold jalan)
                            $violationService->recordViolation([
                                'student_id' => $studentId,
                                'violation_type_id' => $alphaType->id,
                                'violation_date' => $alphaDate,
                                'points' => $points,
                                'description' => $desc,
                                'notes' => 'Dibuat otomatis dari sinkron E-Jurnal.',
                                'evidences' => [],
                            ], null);
                            $results['violations']++;
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = "Violation failed for student={$studentId}: " . $e->getMessage();
                    }
                }
            } else {
                $results['errors'][] = 'Violation type "Alpha" not found. Create a violation type with slug "alpha".';
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sync completed: {$results['created']} created, {$results['updated']} updated, {$results['skipped']} skipped, {$results['violations']} violations.",
            'results' => $results,
        ]);
    }
}
