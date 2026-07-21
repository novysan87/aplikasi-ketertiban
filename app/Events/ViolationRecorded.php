<?php

namespace App\Events;

use App\Models\Violation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ViolationRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public Violation $violation;
    public array $data;

    public function __construct(Violation $violation)
    {
        $this->violation = $violation;
        $this->data = [
            'id' => $violation->id,
            'student_name' => $violation->student->full_name,
            'student_nisn' => $violation->student->nisn,
            'class_name' => $violation->student->class_name,
            'violation_type' => $violation->violationType->name,
            'category_color' => $violation->violationType->category->color ?? '#ef4444',
            'points' => $violation->points,
            'total_points' => $violation->student->total_points,
            'time' => $violation->created_at->diffForHumans(),
            'url' => route('violations.show', $violation->id),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('violations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'violation.recorded';
    }
}
