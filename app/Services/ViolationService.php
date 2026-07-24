<?php

namespace App\Services;

use App\Events\ViolationRecorded;
use App\Models\SpLetter;
use App\Models\SpThreshold;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use App\Models\AppNotification;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ViolationService
{
    public function recordViolation(array $data, ?int $userId = null): Violation
    {
        $violation = DB::transaction(function () use ($data, $userId) {
            $violation = Violation::create([
                'student_id' => $data['student_id'],
                'student_class' => \App\Models\Student::where('id', $data['student_id'])->value('class_name'),
                'violation_type_id' => $data['violation_type_id'],
                'recorded_by' => $userId,
                'description' => $data['description'] ?? null,
                'points' => $data['points'],
                'sanction' => $data['sanction'] ?? null,
                'location' => $data['location'] ?? null,
                'violation_date' => $data['violation_date'],
                'violation_time' => $data['violation_time'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Save evidence photos
 if (isset($data['evidences']) && is_array($data['evidences'])) {
      foreach ($data['evidences'] as $file) {
          if (!$file || !$file->isValid()) continue;
          $path = $file->store('violations/' . $violation->id, 'public');
          $violation->evidences()->create([
              'file_path' => $path,
              'original_name' => $file->getClientOriginalName(),
              'file_size' => $file->getSize(),
              'mime_type' => $file->getMimeType(),
          ]);
      }
  }

            return $violation;
        });

        // Check SP thresholds
        $student = $violation->student;
        $totalPoints = $student->total_points;

        $thresholdReached = SpThreshold::where('min_points', '<=', $totalPoints)
            ->where('is_active', true)
            ->orderByDesc('min_points')
            ->first();

        if ($thresholdReached) {
            $existingSp = SpLetter::where('student_id', $student->id)
                ->where('sp_threshold_id', $thresholdReached->id)
                ->exists();

            if (!$existingSp) {
                $this->generateSpLetter($student, $thresholdReached, $userId);
            }
        }

        // Broadcast realtime event
        $this->notifyRealtime($violation);

        return $violation;
    }

    protected function generateSpLetter(Student $student, SpThreshold $threshold, ?int $userId = null): SpLetter
    {
        $schoolName = Setting::getValue('school_name', 'SMK');
        $letterCount = SpLetter::where('sp_threshold_id', $threshold->id)->count() + 1;

        $letter = SpLetter::create([
            'student_id' => $student->id,
            'sp_threshold_id' => $threshold->id,
            'generated_by' => $userId,
            'letter_number' => sprintf('%s/%s/%s/%s',
                $threshold->slug,
                str_pad((string) $letterCount, 3, '0', STR_PAD_LEFT),
                date('m'),
                date('Y')
            ),
            'title' => $threshold->name . ' - ' . $student->full_name,
            'total_points_at_time' => $student->total_points,
            'violations_included' => $student->violations()
                ->whereNull('deleted_at')
                ->get(['id', 'violation_date', 'points', 'description'])
                ->toArray(),
            'status' => 'draft',
        ]);

        return $letter;
    }

    protected function notifyRealtime(Violation $violation): void
    {
        try {
            $student = $violation->student;

            // Create notifications for BK users
            $bkUsers = User::whereIn('role', ['admin', 'bk'])->get();

            foreach ($bkUsers as $user) {
                AppNotification::create([
                    'user_id' => $user->id,
                    'type' => 'violation_recorded',
                    'title' => 'Pelanggaran Baru: ' . $student->full_name,
                    'body' => $violation->violationType->name . ' (+' . $violation->points . ' poin)',
                    'data' => [
                        'violation_id' => $violation->id,
                        'student_name' => $student->full_name,
                        'points' => $violation->points,
                        'total_points' => $student->total_points,
                    ],
                    'icon' => 'exclamation-triangle',
                    'color' => $violation->violationType->category->color ?? '#ef4444',
                    'action_url' => route('violations.show', $violation->id),
                    'violation_id' => $violation->id,
                ]);
            }

            // Broadcast via Reverb
            broadcast(new ViolationRecorded($violation));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notify realtime failed: ' . $e->getMessage());
        }
    }
}
