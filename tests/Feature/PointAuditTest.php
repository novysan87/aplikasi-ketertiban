<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\PointAuditLog;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationCategory;
use App\Models\ViolationType;
use App\Services\ViolationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bk = User::factory()->create(['roles' => ['bk'], 'name' => 'Guru BK Uji']);
    }

    protected function makeStudent(int $points = 0): Student
    {
        $class = Classes::create(['name' => 'X-TKJ 1', 'level' => 'X']);

        return Student::create([
            'full_name' => 'Siswa Audit',
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'class_name' => 'X-TKJ 1',
        ]);
    }

    protected function makeType(int $points = 5): ViolationType
    {
        $category = ViolationCategory::create(['name' => 'Kedisiplinan', 'slug' => 'kedisiplinan-'.uniqid()]);

        return ViolationType::create([
            'name' => 'Terlambat',
            'slug' => 'terlambat-'.uniqid(),
            'points' => $points,
            'category_id' => $category->id,
        ]);
    }

    public function test_pencatatan_pelanggaran_membuat_audit_log(): void
    {
        $student = $this->makeStudent();
        $type = $this->makeType(5);

        $violation = app(ViolationService::class)->recordViolation([
            'student_id' => $student->id,
            'violation_type_id' => $type->id,
            'points' => $type->points,
            'description' => 'Terlambat 15 menit',
            'violation_date' => '2026-08-01',
        ], $this->bk->id);

        $log = PointAuditLog::where('student_id', $student->id)->firstOrFail();

        $this->assertSame(PointAuditLog::ACTION_CREATED, $log->action);
        $this->assertSame(0, $log->points_before);
        $this->assertSame(5, $log->points_after);
        $this->assertSame(5, $log->points_delta);
        $this->assertSame($violation->id, $log->violation_id);
        $this->assertSame($this->bk->id, $log->actor_id);
        $this->assertSame('Terlambat', $log->description);
        $this->assertSame('Terlambat 15 menit', $log->metadata['description'] ?? null);
    }

    public function test_penghapusan_pelanggaran_membuat_audit_log_negatif(): void
    {
        $student = $this->makeStudent();
        $type = $this->makeType(5);

        $violation = app(ViolationService::class)->recordViolation([
            'student_id' => $student->id,
            'violation_type_id' => $type->id,
            'points' => $type->points,
            'description' => 'Kasus sengketa',
            'violation_date' => '2026-08-01',
        ], $this->bk->id);

        $this->actingAs(User::factory()->create(['roles' => ['admin']]))
            ->delete("/violations/{$violation->id}")
            ->assertRedirect();

        $deletedLog = PointAuditLog::where('student_id', $student->id)
            ->where('action', PointAuditLog::ACTION_DELETED)
            ->firstOrFail();

        $this->assertSame(5, $deletedLog->points_before);
        $this->assertSame(0, $deletedLog->points_after);
        $this->assertSame(-5, $deletedLog->points_delta);
        $this->assertSame('Kasus sengketa', $deletedLog->metadata['description'] ?? null);
        $this->assertSame(0, $student->refresh()->total_points);
    }

    public function test_halaman_riwayat_memerlukan_permission(): void
    {
        $user = User::factory()->create(['roles' => ['staff']]);

        $this->actingAs($user)->get('/point-audit')->assertForbidden();
    }

    public function test_halaman_riwayat_menampilkan_log(): void
    {
        $student = $this->makeStudent();
        $type = $this->makeType(5);

        app(ViolationService::class)->recordViolation([
            'student_id' => $student->id,
            'violation_type_id' => $type->id,
            'points' => $type->points,
            'description' => 'Terlambat 15 menit',
            'violation_date' => '2026-08-01',
        ], $this->bk->id);

        $this->actingAs($this->bk)
            ->get('/point-audit')
            ->assertOk()
            ->assertSee('Riwayat Perubahan Poin')
            ->assertSee('Siswa Audit')
            ->assertSee('Pencatatan')
            ->assertSee('Terlambat 15 menit');
    }
}
