<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViolationReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Role 'kepala_sekolah' punya permission violations-export (di-seed lewat migrasi)
        $this->admin = User::factory()->create(['roles' => ['kepala_sekolah']]);
    }

    protected function seedData(): void
    {
        Setting::setValue('school_name', 'SMKN 1 WONOREJO');
        Setting::setValue('school_address', 'Jl. Pesantren');
        Setting::setValue('school_phone', '(0343) 410064');
        Setting::setValue('kepala_sekolah_name', 'Dr. H. Ahmad Sobirin');
        Setting::setValue('kepala_sekolah_nip', '197606042008011014');

        $class = Classes::create(['name' => 'X-TKJ 1', 'level' => 'X']);
        $student = Student::create([
            'full_name' => 'Budi Santoso',
            'nisn' => '1234567890',
            'class_id' => $class->id,
            'class_name' => 'X-TKJ 1',
        ]);
        $category = \App\Models\ViolationCategory::create(['name' => 'Kedisiplinan', 'slug' => 'kedisiplinan']);
        $type = ViolationType::create(['name' => 'Terlambat', 'slug' => 'terlambat', 'points' => 5, 'category_id' => $category->id]);

        Violation::create([
            'student_id' => $student->id,
            'student_class' => 'X-TKJ 1',
            'violation_type_id' => $type->id,
            'violation_date' => '2026-07-20',
            'points' => 5,
            'recorded_by' => $this->admin->id,
        ]);
        Violation::create([
            'student_id' => $student->id,
            'student_class' => 'X-TKJ 1',
            'violation_type_id' => $type->id,
            'violation_date' => '2026-07-21',
            'points' => 5,
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_halaman_laporan_membutuhkan_permission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/reports/violations')->assertForbidden();
    }

    public function test_halaman_laporan_terbuka_untuk_yang_punya_permission(): void
    {
        $this->seedData();

        $this->actingAs($this->admin)
            ->get('/reports/violations')
            ->assertOk()
            ->assertSee('Laporan Rekap Pelanggaran')
            ->assertSee('Cetak Laporan PDF');
    }

    public function test_pdf_laporan_valid(): void
    {
        $this->seedData();

        $response = $this->actingAs($this->admin)
            ->get('/reports/violations/pdf?date_from=2026-07-01&date_to=2026-07-31');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        $content = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertGreaterThan(1000, strlen($content)); // PDF terisi, bukan kosong
    }

    public function test_pdf_laporan_dengan_filter_kelas(): void
    {
        $this->seedData();
        $class = Classes::first();

        $this->actingAs($this->admin)
            ->get("/reports/violations/pdf?class_id={$class->id}")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
