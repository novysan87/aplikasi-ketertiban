<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsThresholdPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_ambang_sp_terbuka_untuk_admin(): void
    {
        $admin = User::factory()->create(['roles' => ['admin']]);

        $this->actingAs($admin)
            ->get(route('settings.thresholds'))
            ->assertOk()
            ->assertSee('Ambang Surat Peringatan');
    }

    public function test_halaman_ambang_sp_menampilkan_tangga_sp_dinamis(): void
    {
        $admin = User::factory()->create(['roles' => ['admin']]);

        \Illuminate\Support\Facades\DB::table('sp_thresholds')->insert([
            ['name' => 'SP 1', 'slug' => 'sp-1', 'min_points' => 50, 'color' => '#eab308', 'is_active' => true, 'default_description' => 'SP 1 — 50', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SP 2', 'slug' => 'sp-2', 'min_points' => 100, 'color' => '#f97316', 'is_active' => true, 'default_description' => 'SP 2 — 100', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($admin)
            ->get(route('settings.thresholds'))
            ->assertOk()
            ->assertSee('SP 1')
            ->assertSee('SP 2')
            ->assertSee('≥ 100 poin');
    }
}
