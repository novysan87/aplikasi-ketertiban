<?php

namespace Tests\Feature;

use App\Models\ParentStudent;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'nisn' => '0099990001',
            'student_number' => 'TEST/001',
            'full_name' => 'SISWA TEST',
            'class_name' => 'X TKJ 1',
            'class_level' => 'X',
            'department_name' => 'TKJ',
            'status' => 'active',
            'is_active' => true,
        ], $overrides));
    }

    public function test_register_auto_verifies_when_phone_matches(): void
    {
        $this->makeStudent([
            'nisn' => '0099990001',
            'parent_phone' => '081234567890',
            'parent_name' => 'Orang Tua Test',
        ]);

        $response = $this->postJson('/api/v1/parent/register', [
            'nisn' => '0099990001',
            'parent_phone' => '081234567890',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('needs_verification', false)
            ->assertJsonPath('students.0.link_status', 'active')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'username']]);

        $this->assertDatabaseHas('parent_students', [
            'status' => 'active',
        ]);
    }

    public function test_register_creates_pending_when_phone_not_match(): void
    {
        $this->makeStudent(['nisn' => '0099990001']); // parent_phone null

        $response = $this->postJson('/api/v1/parent/register', [
            'nisn' => '0099990001',
            'parent_phone' => '081298765432',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('needs_verification', true)
            ->assertJsonPath('students.0.link_status', 'pending');

        $this->assertDatabaseHas('parent_students', [
            'status' => 'pending',
        ]);
    }

    public function test_register_rejects_unknown_nisn(): void
    {
        $response = $this->postJson('/api/v1/parent/register', [
            'nisn' => '0000000000',
            'parent_phone' => '081298765432',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('nisn');
    }

    public function test_login_and_me(): void
    {
        $this->makeStudent(['nisn' => '0099990001']);
        $this->postJson('/api/v1/parent/register', [
            'nisn' => '0099990001',
            'parent_phone' => '081298765432',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ]);

        $response = $this->postJson('/api/v1/parent/login', [
            'username' => '0099990001',
            'password' => 'rahasia123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['username'], 'students']);

        $token = $response->json('token');

        $this->withToken($token)
            ->getJson('/api/v1/parent/me')
            ->assertOk()
            ->assertJsonPath('user.username', '0099990001');
    }

    public function test_pending_link_cannot_access_violations(): void
    {
        $student = $this->makeStudent(['nisn' => '0099990001']);
        $user = User::factory()->create(['username' => '0099990001', 'role' => 'parent', 'roles' => ['parent']]);
        ParentStudent::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/parent/students/{$student->id}/violations")
            ->assertForbidden();
    }

    public function test_active_link_can_access_violations(): void
    {
        $student = $this->makeStudent(['nisn' => '0099990001']);
        $user = User::factory()->create(['username' => '0099990001', 'role' => 'parent', 'roles' => ['parent']]);
        ParentStudent::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'status' => 'active',
            'verified_at' => now(),
        ]);

        $category = \App\Models\ViolationCategory::create([
            'name' => 'Kedisiplinan',
            'slug' => 'kedisiplinan',
        ]);

        $type = ViolationType::create([
            'category_id' => $category->id,
            'name' => 'Terlambat',
            'slug' => 'terlambat',
            'points' => 5,
            'is_active' => true,
        ]);

        Violation::create([
            'student_id' => $student->id,
            'student_class' => 'X TKJ 1',
            'violation_type_id' => $type->id,
            'points' => 5,
            'description' => 'Terlambat datang',
            'violation_date' => now()->toDateString(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/parent/students/{$student->id}/violations")
            ->assertOk()
            ->assertJsonPath('total_points', 5)
            ->assertJsonCount(1, 'violations');
    }

    public function test_link_second_child(): void
    {
        $this->makeStudent(['nisn' => '0099990001']);
        $this->makeStudent([
            'nisn' => '0099990002',
            'student_number' => 'TEST/002',
            'full_name' => 'SISWA KEDUA',
            'parent_phone' => '081234567890',
        ]);

        $token = $this->postJson('/api/v1/parent/register', [
            'nisn' => '0099990001',
            'parent_phone' => '081298765432',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ])->json('token');

        $this->withToken($token)
            ->postJson('/api/v1/parent/students/link', [
                'nisn' => '0099990002',
                'parent_phone' => '081234567890',
            ])
            ->assertStatus(201)
            ->assertJsonPath('link_status', 'active');
    }

    public function test_register_device(): void
    {
        $this->makeStudent(['nisn' => '0099990001']);
        $token = $this->postJson('/api/v1/parent/register', [
            'nisn' => '0099990001',
            'parent_phone' => '081298765432',
            'name' => 'Wali Test',
            'password' => 'rahasia123',
        ])->json('token');

        $this->withToken($token)
            ->postJson('/api/v1/parent/devices', [
                'platform' => 'android',
                'fcm_token' => 'fcm-abc-123',
                'device_name' => 'Pixel 8',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('parent_devices', [
            'fcm_token' => 'fcm-abc-123',
        ]);

        // Platform web (preview browser) juga harus diterima
        $this->withToken($token)
            ->postJson('/api/v1/parent/devices', [
                'platform' => 'web',
                'fcm_token' => 'fcm-web-456',
                'device_name' => 'Chrome preview',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('parent_devices', [
            'fcm_token' => 'fcm-web-456',
            'platform' => 'web',
        ]);
    }
}
