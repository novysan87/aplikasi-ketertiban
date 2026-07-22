<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandlingParticipant extends Model
{
    protected $fillable = ['handling_id', 'user_id', 'role'];

    public function handling(): BelongsTo
    {
        return $this->belongsTo(ViolationHandling::class, 'handling_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
