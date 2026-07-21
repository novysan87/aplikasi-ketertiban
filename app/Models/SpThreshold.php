<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpThreshold extends Model
{
    protected $fillable = [
        'name', 'slug', 'min_points', 'max_points',
        'color', 'default_description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'max_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function spLetters()
    {
        return $this->hasMany(SpLetter::class, 'sp_threshold_id');
    }
}
