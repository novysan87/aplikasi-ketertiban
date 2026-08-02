<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HandlingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'icon', 'color', 'sort_order', 'is_active', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Daftar peran penanganan (dari settings, dengan default).
     */
    public static function roles(): array
    {
        $defaults = ['Wakil Ketua Tim', 'Anggota Tim', 'Wali Kelas', 'Guru BK', 'Saksi'];
        $raw = \App\Models\Setting::getValue('handling_roles');
        if (!$raw) {
            return $defaults;
        }
        $roles = json_decode($raw, true);
        return is_array($roles) && count($roles) ? $roles : $defaults;
    }

    public static function saveRoles(array $roles): void
    {
        $roles = array_values(array_filter(array_map('trim', $roles)));
        \App\Models\Setting::setValue('handling_roles', json_encode($roles), 'general', 'Daftar peran penanganan');
    }
}
