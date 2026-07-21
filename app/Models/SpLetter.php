<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpLetter extends Model
{
    protected $fillable = [
        'student_id', 'sp_threshold_id', 'generated_by',
        'letter_number', 'title', 'body', 'total_points_at_time',
        'violations_included', 'status', 'file_path',
        'printed_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'total_points_at_time' => 'integer',
            'violations_included' => 'json',
            'printed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function spThreshold()
    {
        return $this->belongsTo(SpThreshold::class, 'sp_threshold_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
