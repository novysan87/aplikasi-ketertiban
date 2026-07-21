<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'color', 'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function violationTypes()
    {
        return $this->hasMany(ViolationType::class, 'category_id');
    }
}
