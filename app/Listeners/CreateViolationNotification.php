<?php

namespace App\Listeners;

use App\Events\ViolationRecorded;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateViolationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ViolationRecorded $event): void
    {
        $violation = $event->violation;
        $data = $event->data;

        // Kirim notifikasi ke semua user kecuali pencatat
        $users = User::where('is_active', true)
            ->where('id', '!=', $violation->recorded_by)
            ->get();

        foreach ($users as $user) {
            AppNotification::create([
                'user_id' => $user->id,
                'type' => 'violation',
                'title' => 'Pelanggaran Baru: ' . ($data['student_name'] ?? ''),
                'body' => ($data['violation_type'] ?? '') . ' (+' . ($data['points'] ?? 0) . ' poin)',
                'icon' => 'exclamation-triangle',
                'color' => $data['category_color'] ?? '#ef4444',
                'action_url' => $data['url'] ?? null,
                'is_read' => false,
                'violation_id' => $violation->id,
                'data' => $data,
            ]);
        }
    }
}
