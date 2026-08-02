<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceVerificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'student_id',
        'violation_id',
        'faceid_matched',
        'faceid_ambiguous',
        'faceid_reason',
        'faceid_score',
        'photo_hash',
        'top_candidates',
        'created_at',
    ];

    protected $casts = [
        'faceid_matched' => 'boolean',
        'faceid_ambiguous' => 'boolean',
        'faceid_score' => 'float',
        'top_candidates' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }
}
