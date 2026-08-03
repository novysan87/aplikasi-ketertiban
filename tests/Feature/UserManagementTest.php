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
}
