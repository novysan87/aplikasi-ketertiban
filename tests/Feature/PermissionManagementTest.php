<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['roles' => ['admin']]);
    }

    public function test_permission_khusus_admin_tidak_bisa_diberikan_ke_role_lain(): void
    {
        $auditId = DB::table('permissions')->where('key', 'view-point-audit')->value('id');
        $normalId = DB::table('permissions')->where('key', 'input-violations')->value('id');

        $this->actingAs($this->admin)
            ->post('/settings/permissions', [
                'permissions' => [
                    'bk' => [$auditId, $normalId],
                    'ketua_tim' => [$auditId],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // view-point-audit TIDAK boleh jatuh ke role non-admin
        $this->assertDatabaseMissing('role_permissions', ['role' => 'bk', 'permission_id' => $auditId]);
        $this->assertDatabaseMissing('role_permissions', ['role' => 'ketua_tim', 'permission_id' => $auditId]);

        // permission normal tetap tersimpan
        $this->assertDatabaseHas('role_permissions', ['role' => 'bk', 'permission_id' => $normalId]);

        // admin selalu dapat semua
        $this->assertDatabaseHas('role_permissions', ['role' => 'admin', 'permission_id' => $auditId]);
    }

    public function test_halaman_hak_akses_menampilkan_permission_khusus_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/settings/permissions')
            ->assertOk()
            ->assertSee('Hak Akses Role')
            ->assertSee('view-point-audit')
            ->assertSee('Khusus Admin');
    }
}
