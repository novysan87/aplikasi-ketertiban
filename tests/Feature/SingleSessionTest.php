<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingleSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['roles' => ['admin']]);
    }

    protected function makeUser(): User
    {
        return User::factory()->create([
            'roles' => ['bk'],
            'username' => 'bksession',
            'password' => 'rahasia123',
        ]);
    }

    public function test_login_menyimpan_token_sesi_aktif(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'username' => 'bksession',
            'password' => 'rahasia123',
        ])->assertRedirect();

        $this->assertNotNull($user->refresh()->active_session_token);
    }

    public function test_login_kedua_ditolak_saat_akun_masih_aktif(): void
    {
        $user = $this->makeUser();
        // Simulasikan sudah login di perangkat lain (token sesi aktif)
        $user->forceFill(['active_session_token' => 'sesi-perangkat-lain'])->save();

        $this->post('/login', [
            'username' => 'bksession',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertSame('sesi-perangkat-lain', $user->refresh()->active_session_token);
    }

    public function test_middleware_mengeluarkan_sesi_yang_bukan_pemilik_token(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['active_session_token' => 'sesi-perangkat-lain'])->save();

        // User ini "login" dengan sesi berbeda → harus ditendang ke halaman login
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_menghapus_token_sesi(): void
    {
        $user = $this->makeUser();
        $sessionId = str_repeat('a', 40); // id sesi valid (format 40 char)
        $user->forceFill(['active_session_token' => $sessionId])->save();

        // Uji logika controller logout langsung (session id = token)
        $request = \Illuminate\Http\Request::create('/logout', 'POST');
        $request->setLaravelSession($this->app['session.store']);
        $this->app['session.store']->setId($sessionId);
        $this->actingAs($user);

        (new \App\Http\Controllers\Auth\LoginController())->logout($request);

        $this->assertNull($user->refresh()->active_session_token);
    }

    public function test_force_logout_membersihkan_token(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['active_session_token' => 'sesi-lama'])->save();

        $this->actingAs($this->admin)
            ->post("/users/{$user->id}/force-logout")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($user->refresh()->active_session_token);
    }

    public function test_sesi_yang_sudah_kadaluarsa_dianggap_mati(): void
    {
        // Produksi pakai driver file — uji perilaku sesungguhnya
        config()->set('session.driver', 'file');

        $user = $this->makeUser();
        $sessionId = str_repeat('a', 40); // id sesi valid (format 40 char)
        $user->forceFill(['active_session_token' => $sessionId])->save();

        // Simulasikan file sesi lama: sudah lewat masa hidup (10 menit lalu)
        $file = storage_path('framework/sessions/'.$sessionId);
        file_put_contents($file, 'expired');
        touch($file, time() - 600);

        $this->assertFalse($user->refresh()->sessionTokenIsAlive());

        // Login kedua harusnya DITERIMA sekarang
        $this->post('/login', [
            'username' => 'bksession',
            'password' => 'rahasia123',
        ])->assertRedirect();

        $this->assertAuthenticated();
        @unlink($file);
    }

    public function test_sesi_yang_masih_hidup_dianggap_hidup(): void
    {
        // Produksi pakai driver file — uji perilaku sesungguhnya
        config()->set('session.driver', 'file');

        $user = $this->makeUser();
        $sessionId = str_repeat('b', 40);
        $user->forceFill(['active_session_token' => $sessionId])->save();

        // File sesi baru (aktivitas sekarang)
        $file = storage_path('framework/sessions/'.$sessionId);
        file_put_contents($file, 'fresh');
        touch($file);

        $this->assertTrue($user->refresh()->sessionTokenIsAlive());
        @unlink($file);
    }
}
