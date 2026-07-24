<?php

namespace App\Services;

use App\Models\Classes;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StudentSyncService
{
    public function sync(string $baseUrl, string $token): array
    {
        $results = [
            'students_created' => 0,
            'students_updated' => 0,
            'classes_created' => 0,
            'classes_updated' => 0,
            'errors' => [],
        ];

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(60)
                ->get(rtrim($baseUrl, '/') . '/api/integration/students');

            if (!$response->successful()) {
                return ['error' => 'Gagal terhubung: ' . $response->status()];
            }

            $payload = $response->json();
            $items = Arr::get($payload, 'data', []);

            \$syncedIds = [];

            DB::transaction(function () use ($items, &$results, &$syncedIds) {
                foreach ($items as $item) {
                    // Only sync active students
                    $status = $item['status'] ?? 'active';
                    if ($status !== 'active') {
                        $results['errors'][] = 'Skipped (status: ' . $status . '): ' . ($item['full_name'] ?? 'unknown');
                        continue;
                    }

                    // Use NISN as primary key, fallback to student_number if NISN is null
                    $nisn = $item['nisn'] ?? null;
                    if (!$nisn) {
                        $studentNumber = $item['student_number'] ?? null;
                        if ($studentNumber) {
                            $nisn = 'NIS_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $studentNumber);
                            $results['errors'][] = 'Using student_number as ID: ' . ($item['full_name'] ?? 'unknown') . ' (' . $studentNumber . ')';
                        } else {
                            $results['errors'][] = 'Skipped (no NISN/NIS): ' . ($item['full_name'] ?? 'unknown');
                            continue;
                        }
                    }

                    // Sync class
                    $classData = $item['classroom'] ?? [];
                    $className = $item['class_name'] ?? '-';

                    $class = Classes::updateOrCreate(
                        ['source_id' => (string) ($classData['id'] ?? $className)],
                        [
                            'name' => $className,
                            'level' => $classData['level'] ?? $item['class_level'] ?? 'X',
                            'department_code' => $item['department_code'] ?? '',
                            'department_name' => $item['department_name'] ?? '',
                            'academic_year_name' => $item['academic_year_name'] ?? '',
                            'is_active' => true,
                        ]
                    );

                    if ($class->wasRecentlyCreated) $results['classes_created']++;
                    elseif ($class->wasChanged()) $results['classes_updated']++;

                    // Sync student
                    $student = Student::updateOrCreate(
                        ['nisn' => $nisn],
                        [
                            'source_id' => (string) ($item['id'] ?? ''),
                            'student_number' => $item['student_number'],
                            'full_name' => $item['full_name'],
                            'gender' => $item['gender'],
                            'place_of_birth' => $item['place_of_birth'],
                            'date_of_birth' => $item['date_of_birth'] ?: null,
                            'address' => $item['address'],
                            'phone_number' => $item['phone_number'],
                            'email' => $item['email'],
                            'class_name' => $className,
                            'class_level' => $classData['level'] ?? $item['class_level'] ?? 'X',
                            'department_code' => $item['department_code'] ?? '',
                            'department_name' => $item['department_name'] ?? '',
                            'academic_year_name' => $item['academic_year_name'] ?? '',
                            'status' => 'active',
                            'photo_path' => $item['photo_path'],
                            'class_id' => $class->id,
                            'is_active' => true,
                            'synced_at' => now(),
                            'metadata' => [
                                'source_updated_at' => $item['updated_at'],
                                'synced_from' => 'database-kesiswaan',
                            ],
                        ]
                    );

                    if ($student->wasRecentlyCreated) $results['students_created']++;
                    else $results['students_updated']++;

                    $syncedIds[] = $student->id;
                }
            });

            // Deactivate students not in this sync (graduated/dropped out)
            \$deactivated = Student::where('is_active', true)
                ->whereNotIn('id', $syncedIds)
                ->update(['is_active' => false]);
            $results['students_deactivated'] = $deactivated;

            return $results;
        } catch (\Exception $e) {
            Log::error('Student sync failed: ' . $e->getMessage());
            return ['error' => 'Sinkron gagal: ' . $e->getMessage()];
        }
    }
}
