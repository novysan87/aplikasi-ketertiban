<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointAuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\PointAuditLogFactory> */
    use HasFactory;

    public const ACTION_CREATED = 'created';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_ADJUSTED = 'adjusted';

    protected $fillable = [
        'student_id', 'violation_id', 'action',
        'points_before', 'points_after', 'points_delta',
        'description', 'metadata', 'actor_id', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'points_before' => 'integer',
            'points_after' => 'integer',
            'points_delta' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Pencatatan',
            self::ACTION_DELETED => 'Penghapusan',
            self::ACTION_ADJUSTED => 'Penyesuaian',
            default => ucfirst($this->action),
        };
    }

    /**
     * Catat log audit perubahan poin.
     *
     * @param  array<string, mixed>  $metadata
     */
    public static function log(
        int $studentId,
        string $action,
        int $pointsBefore,
        int $pointsAfter,
        ?string $description = null,
        ?array $metadata = null,
        ?int $violationId = null,
        ?int $actorId = null,
        ?string $ipAddress = null,
    ): self {
        return static::create([
            'student_id' => $studentId,
            'violation_id' => $violationId,
            'action' => $action,
            'points_before' => $pointsBefore,
            'points_after' => $pointsAfter,
            'points_delta' => $pointsAfter - $pointsBefore,
            'description' => $description,
            'metadata' => $metadata,
            'actor_id' => $actorId,
            'ip_address' => $ipAddress,
        ]);
    }
}
