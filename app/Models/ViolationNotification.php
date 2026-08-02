<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationNotification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'violation_id',
        'student_id',
        'channel',
        'recipient',
        'message',
        'status',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
