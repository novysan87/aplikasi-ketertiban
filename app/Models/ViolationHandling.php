<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationHandling extends Model
{
    protected $fillable = [
        'violation_id', 'handling_type', 'handling_date',
        'description', 'location', 'evidence', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'handling_date' => 'date',
        ];
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(HandlingParticipant::class, 'handling_id');
    }
}
