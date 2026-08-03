<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['roles' => ['admin']]);
    }

    public function test_admin_menambah_user_dengan_nomor_hp(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'Guru Baru',
                'username' => 'gurubaru',
                'email' => 'gurubaru@sekolah.sch.id',
                'phone' => '081234567890',
                'password' => 'rahasia123',
                'roles' => ['bk'],
                'is_active' => 1,
            ])
            ->assertRedirect();

        $user = User::where('email', 'gurubaru@sekolah.sch.id')->firstOrFail();
        $this->assertSame('081234567890', $user->phone);
    }

    public function test_admin_mengupdate_nomor_hp_user(): void
    {
        $user = User::factory()->create(['roles' => ['bk'], 'phone' => '081111111111']);

        $this->actingAs($this->admin)
            ->put("/users/{$user->id}", [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => '082222222222',
                'roles' => ['bk'],
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertSame('082222222222', $user->refresh()->phone);
    }

    public function test_nomor_hp_tidak_boleh_karakter_aneh(): void
    {
        $this->actingAs($this->admin)
            ->post('/users', [
                'name' => 'Guru Aneh',
                'username' => 'guruaneh',
                'email' => 'guruaneh@sekolah.sch.id',
                'phone' => 'abc; drop table',
                'password' => 'rahasia123',
                'roles' => ['bk'],
            ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseMissing('users', ['username' => 'guruaneh']);
    }

    public function test_kirim_akun_via_whatsapp(): void
    {
        $user = User::factory()->create([
            'roles' => ['bk'],
            'phone' => '081234567890',
            'username' => 'guruwa',
            'password' => 'passwordlama',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/users/{$user->id}/send-wa");

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/081234567890?text=', $response->headers->get('Location'));
        $this->assertStringContainsString('guruwa', $response->headers->get('Location'));
        $this->assertStringContainsString('password', urldecode($response->headers->get('Location')));

        // Password lama tidak berlaku lagi (digenerate baru)
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('passwordlama', $user->refresh()->password));
    }

    public function test_kirim_akun_tanpa_nomor_hp_ditolak(): void
    {
        $user = User::factory()->create(['roles' => ['bk'], 'phone' => null]);

        $this->actingAs($this->admin)
            ->post("/users/{$user->id}/send-wa")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse(str_contains(session('error'), 'wa.me'));
    }
}
