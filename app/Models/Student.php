<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'source_id', 'nisn', 'student_number', 'full_name',
        'gender', 'place_of_birth', 'date_of_birth', 'address',
        'phone_number', 'email', 'class_name', 'class_level',
        'department_code', 'department_name', 'academic_year_name',
        'status', 'photo_path', 'class_id', 'is_active',
        'metadata', 'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'metadata' => 'json',
            'synced_at' => 'datetime',
        ];
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function spLetters(): HasMany
    {
        return $this->hasMany(SpLetter::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->violations()
            ->whereNull('deleted_at')
            ->sum('points');
    }
}
