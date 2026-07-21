<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data',
        'icon', 'color', 'action_url', 'is_read',
        'read_at', 'violation_id', 'sp_letter_id',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function spLetter()
    {
        return $this->belongsTo(SpLetter::class, 'sp_letter_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
