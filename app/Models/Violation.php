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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'violation_date' => 'date',
            'violation_time' => 'datetime:H:i',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
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
}
