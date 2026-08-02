<?php

namespace Database\Seeders;

use App\Models\HandlingType;
use Illuminate\Database\Seeder;

class HandlingTypeDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Teguran Lisan',       'icon' => 'fa-comment',               'color' => '#3b82f6', 'sort_order' => 1],
            ['name' => 'Teguran Tertulis',    'icon' => 'fa-file-pen',              'color' => '#4f46e5', 'sort_order' => 2],
            ['name' => 'Pembinaan BK',        'icon' => 'fa-hand-holding-heart',    'color' => '#14b8a6', 'sort_order' => 3],
            ['name' => 'Panggilan Orang Tua', 'icon' => 'fa-phone-volume',          'color' => '#f97316', 'sort_order' => 4],
            ['name' => 'Home Visit',          'icon' => 'fa-house-chimney',         'color' => '#8b5cf6', 'sort_order' => 5],
            ['name' => 'Skorsing',            'icon' => 'fa-ban',                   'color' => '#ef4444', 'sort_order' => 6],
            ['name' => 'Tugas Sosial',        'icon' => 'fa-handshake-angle',       'color' => '#10b981', 'sort_order' => 7],
            ['name' => 'SP-1',                'icon' => 'fa-file-signature',        'color' => '#f59e0b', 'sort_order' => 8],
            ['name' => 'SP-2',                'icon' => 'fa-file-signature',        'color' => '#ea580c', 'sort_order' => 9],
            ['name' => 'SP-3',                'icon' => 'fa-file-circle-exclamation','color' => '#dc2626', 'sort_order' => 10],
            ['name' => 'Lainnya',             'icon' => 'fa-clipboard-list',        'color' => '#64748b', 'sort_order' => 99],
        ];

        foreach ($defaults as $data) {
            HandlingType::updateOrCreate(
                ['name' => $data['name']],
                $data + ['is_system' => true, 'is_active' => true]
            );
        }
    }
}
