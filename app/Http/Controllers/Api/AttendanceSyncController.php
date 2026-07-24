<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceSyncController extends Controller
{
    /**
     * Test connection — validate token and return simple status.
     * GET /api/v1/attendance/ping?token=xxx
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
    /**
     * Receive attendance push from E-Jurnal.
     * 
     * Request body:
     * {
     *   "token": "shared-secret",
     *   "attendances": [
     *     {
     *       "source_student_id": 123,
     *       "date": "2026-07-24",
     *       "lesson_hour": 1,
     *       "status": "hadir",
     *       "notes": null
     *     }
     *   ]
     * }
     */
    public function sync(Request $request)
    {
        // Validate token
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

        // Validate payload structure
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
        ];

        // Lookup map: source_student_id -> local student id
        $sourceIds = array_unique(array_column($attendances, 'source_student_id'));
        $studentMap = Student::whereIn('source_id', $sourceIds)
            ->where('is_active', true)
            ->pluck('id', 'source_id');

        foreach ($attendances as $item) {
            $sourceId = $item['source_student_id'];

            // Skip if student not found in local DB
            if (!isset($studentMap[$sourceId])) {
                $results['skipped']++;
                $results['errors'][] = "Student source_id={$sourceId} not found";
                continue;
            }

            $localStudentId = $studentMap[$sourceId];

            // Map status from e-jurnal to aplikasi-ketertiban format
            $statusMap = [
                'present' => 'hadir',
                'sick' => 'sakit',
                'permission' => 'izin',
                'absent' => 'alpha',
            ];
            $localStatus = $statusMap[$item['status']] ?? $item['status'];

            try {
                // Upsert: unique constraint on (student_id, date, lesson_hour)
                $attendance = Attendance::updateOrCreate(
                    [
                        'student_id' => $localStudentId,
                        'date' => $item['date'],
                        'lesson_hour' => $item['lesson_hour'],
                    ],
                    [
                        'status' => $localStatus,
                        'notes' => $item['notes'] ?? null,
                        'recorded_by' => null, // system-generated
                    ]
                );

                if ($attendance->wasRecentlyCreated) {
                    $results['created']++;
                } else {
                    $results['updated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "student_id={$sourceId} jam={$item['lesson_hour']}: " . $e->getMessage();
                $results['skipped']++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sync completed: {$results['created']} created, {$results['updated']} updated, {$results['skipped']} skipped.",
            'results' => $results,
        ]);
    }
}
