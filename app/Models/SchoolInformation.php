<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInformation extends Model
{
    protected $table = 'school_informations';

    protected $fillable = [
        'title', 'content', 'category', 'event_date', 'is_published', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
