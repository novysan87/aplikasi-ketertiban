<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ViolationEvidence extends Model
{
    protected $table = 'violation_evidences';

    protected $fillable = [
        'violation_id', 'file_path', 'original_name', 'file_size', 'mime_type',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
