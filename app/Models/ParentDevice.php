<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentDevice extends Model
{
    protected $fillable = [
        'user_id', 'platform', 'fcm_token', 'device_name', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
