<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'source_id', 'name', 'level', 'department_code',
        'department_name', 'academic_year_name', 'is_active',
        'metadata', 'homeroom_teacher_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'json',
        ];
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(User::class, 'homeroom_teacher_id');
    }
}
