<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentStudent extends Model
{
    protected $table = 'parent_students';

    protected $fillable = [
        'user_id', 'student_id', 'relation', 'status', 'verified_at', 'verified_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
