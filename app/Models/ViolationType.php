<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'points',
        'default_sanction', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(ViolationCategory::class, 'category_id');
    }

    public function violations()
    {
        return $this->hasMany(Violation::class, 'violation_type_id');
    }
}
