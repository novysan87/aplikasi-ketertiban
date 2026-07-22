<?php

namespace Database\Seeders;

use App\Models\ViolationCategory;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationTypeDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        // Cari kategori ringan (ID 1 biasanya)
        $ringan = ViolationCategory::where('slug', 'ringan')
            ->orWhere('name', 'like', '%ringan%')
            ->first();

        if (!$ringan) {
            $this->command->warn('Kategori "Ringan" tidak ditemukan. Buat kategori Ringan dulu.');
            return;
        }

        // Alpha
        ViolationType::updateOrCreate(
            ['slug' => 'alpha'],
            [
                'name' => 'Alpha - Tidak hadir tanpa keterangan',
                'category_id' => $ringan->id,
                'points' => 15,
                'default_sanction' => 'Peringatan lisan / teguran BK',
                'description' => 'Pelanggaran otomatis dari sistem presensi ketika siswa tidak hadir tanpa keterangan.',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        // Terlambat
        ViolationType::updateOrCreate(
            ['slug' => 'terlambat'],
            [
                'name' => 'Terlambat',
                'category_id' => $ringan->id,
                'points' => 2,
                'default_sanction' => 'Peringatan lisan',
                'description' => 'Pelanggaran otomatis dari sistem presensi ketika siswa terlambat.',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        $this->command->info('System violation types (alpha & terlambat) seeded/updated.');
    }
}
