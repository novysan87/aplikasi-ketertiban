<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'violation_type_id', 'recorded_by',
        'description', 'points', 'sanction', 'location',
        'violation_date', 'violation_time',
        'is_verified', 'verified_by', 'verified_at',
        'notes', 'handling_status', 'handled_at', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'violation_date' => 'date',
            'violation_time' => 'datetime:H:i',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'handled_at' => 'datetime',
            'points' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ViolationEvidence::class);
    }

    public function handlings(): HasMany
    {
        return $this->hasMany(ViolationHandling::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isUnhandled(): bool
    {
        return $this->handling_status === 'unhandled';
    }

    public function isInProgress(): bool
    {
        return $this->handling_status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->handling_status === 'resolved';
    }
}
