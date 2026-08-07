<?php

namespace App\Services;

use App\Events\ViolationRecorded;
use App\Models\AppNotification;
use App\Models\ParentDevice;
use App\Models\ParentStudent;
use App\Models\PointAuditLog;
use App\Models\SpLetter;
use App\Models\SpThreshold;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
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

            // Audit log perubahan poin (pencatatan pelanggaran)
            $after = $violation->student->total_points;
            PointAuditLog::log(
                studentId: $violation->student_id,
                action: PointAuditLog::ACTION_CREATED,
                pointsBefore: $after - $violation->points,
                pointsAfter: $after,
                description: $violation->violationType?->name ?? 'Pelanggaran',
                metadata: [
                    'violation_date' => $violation->violation_date?->format('Y-m-d'),
                    'description' => $violation->description,
                    'sanction' => $violation->sanction,
                    'location' => $violation->location,
                ],
                violationId: $violation->id,
                actorId: $userId,
                ipAddress: request()->ip(),
            );

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
                strtoupper($threshold->slug),
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

        // Generate PDF resmi (kop surat) agar wali bisa melihat/unduh di SiMURID
        $pdfPath = \App\Support\SpLetterPdf::generate($letter);
        if ($pdfPath) {
            $letter->update(['file_path' => $pdfPath]);
        }

        // Notifikasi + FCM push ke wali murid (SP baru)
        $this->notifyParentsOfSp($letter);

        return $letter;
    }

    protected function notifyParents(Violation $violation): void
    {
        try {
            $student = $violation->student;
            $links = ParentStudent::where('student_id', $student->id)
                ->where('status', 'active')
                ->get();

            if ($links->isEmpty()) {
                return;
            }

            $title = 'Pelanggaran baru: '.$student->full_name;
            $body = $violation->violationType?->name.' (+'.$violation->points.' poin)';
            $data = [
                'violation_id' => (string) $violation->id,
                'student_name' => $student->full_name,
                'points' => (string) $violation->points,
                'type' => 'violation_recorded',
            ];

            foreach ($links as $link) {
                // Catat di daftar notifikasi dalam aplikasi wali (channel push)
                $violation->notifications()->create([
                    'student_id' => $student->id,
                    'channel' => 'push',
                    'recipient' => $link->user?->name ?? 'Wali',
                    'message' => $title.' — '.$body,
                    'status' => 'sent',
                    'user_id' => $link->user_id,
                    'created_at' => now(),
                ]);

                // FCM push ke semua perangkat wali
                $tokens = ParentDevice::where('user_id', $link->user_id)->pluck('fcm_token');
                foreach ($tokens as $token) {
                    app(FcmService::class)->sendToToken($token, [
                        'title' => $title,
                        'body' => $body,
                    ], $data);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notify parents (violation) failed: '.$e->getMessage());
        }
    }

    protected function notifyParentsOfSp(SpLetter $letter): void
    {
        try {
            $student = $letter->student;
            $links = ParentStudent::where('student_id', $student->id)
                ->where('status', 'active')
                ->get();

            if ($links->isEmpty()) {
                return;
            }

            $title = 'Surat Peringatan untuk '.$student->full_name;
            $body = $letter->title.' ('.$letter->letter_number.')';
            $data = [
                'sp_letter_id' => (string) $letter->id,
                'student_name' => $student->full_name,
                'type' => 'sp_generated',
            ];

            foreach ($links as $link) {
                // Ambil id pelanggaran pertama dari data JSON violations_included (untuk tautan notifikasi)
                $included = $letter->violations_included ?? [];
                $firstViolationId = is_array($included) && ! empty($included)
                    ? ($included[0]['id'] ?? null)
                    : null;
                $notification = new \App\Models\ViolationNotification([
                    'student_id' => $student->id,
                    'channel' => 'push',
                    'recipient' => $link->user?->name ?? 'Wali',
                    'message' => $title.' — '.$body,
                    'status' => 'sent',
                    'user_id' => $link->user_id,
                    'created_at' => now(),
                ]);
                if ($firstViolationId) {
                    $notification->violation_id = $firstViolationId;
                }
                $notification->save();

                $tokens = ParentDevice::where('user_id', $link->user_id)->pluck('fcm_token');
                foreach ($tokens as $token) {
                    app(FcmService::class)->sendToToken($token, [
                        'title' => $title,
                        'body' => $body,
                    ], $data);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notify parents (SP) failed: '.$e->getMessage());
        }
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

            // Notifikasi + FCM push ke wali murid
            $this->notifyParents($violation);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notify realtime failed: ' . $e->getMessage());
        }
    }
}
