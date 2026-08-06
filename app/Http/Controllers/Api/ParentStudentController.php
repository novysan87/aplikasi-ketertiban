<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
use App\Models\SpLetter;
use App\Models\Student;
use App\Models\Violation;
use App\Support\PhoneHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ParentStudentController extends Controller
{
    /**
     * Daftar anak milik wali (termasuk yang statusnya pending verifikasi).
     */
    public function index(Request $request): JsonResponse
    {
        $payload = app(ParentAuthController::class)->studentPayloads($request->user());

        return response()->json(['students' => $payload]);
    }

    /**
     * Jadwal pelajaran mingguan kelas siswa (diambil dari database e-jurnal).
     */
    public function schedule(Request $request, Student $student): JsonResponse
    {
        // Hanya wali dengan tautan aktif yang boleh melihat
        $hasLink = ParentStudent::where('user_id', $request->user()->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasLink, 403, 'Putra/putri belum tertaut aktif.');

        try {
            $db = DB::connection('ejurnal');
            $yearId = $db->table('academic_years')->where('is_active', 1)->value('id');
            $semesterId = $db->table('semesters')->where('is_active', 1)->value('id');

            $rows = $db->table('schedules as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->join('subjects as sub', 'sub.id', '=', 's.subject_id')
                ->leftJoin('teachers as t', 't.id', '=', 's.teacher_id')
                ->leftJoin('users as u', 'u.id', '=', 't.user_id')
                ->where('c.name', $student->class_name)
                ->where('s.academic_year_id', $yearId)
                ->where('s.semester_id', $semesterId)
                ->where('s.is_active', 1)
                ->orderBy('s.day_of_week')
                ->orderBy('s.start_time')
                ->get(['s.day_of_week', 's.start_time', 's.end_time', 'sub.name as subject', 'u.name as teacher']);

            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $grouped = [];
            foreach ($days as $day) {
                $items = $rows->where('day_of_week', $day)->values()->map(fn ($r) => [
                    'start' => substr($r->start_time, 0, 5),
                    'end' => substr($r->end_time, 0, 5),
                    'subject' => $r->subject,
                    'teacher' => $r->teacher,
                ]);
                if ($items->isNotEmpty()) {
                    $grouped[] = [
                        'day' => $day,
                        'items' => $items,
                    ];
                }
            }

            return response()->json([
                'class_name' => $student->class_name,
                'schedule' => $grouped,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jadwal gagal dimuat: '.$e->getMessage());

            return response()->json(['message' => 'Jadwal belum tersedia untuk kelas ini.'], 404);
        }
    }

    /**
     * Tautkan anak tambahan (kakak/adik) ke akun wali.
     */
    public function link(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:30'],
            'parent_phone' => ['required', 'string', 'min:9', 'max:20'],
            'relation' => ['nullable', Rule::in(['father', 'mother', 'guardian'])],
        ]);

        $user = $request->user();
        $nisn = trim($validated['nisn']);

        $student = Student::query()
            ->where('nisn', $nisn)
            ->where('is_active', true)
            ->first();

        if (! $student) {
            return response()->json([
                'message' => 'NISN tidak ditemukan. Periksa kembali NISN anak Anda.',
                'errors' => ['nisn' => ['NISN tidak ditemukan atau siswa tidak aktif.']],
            ], 422);
        }

        $exists = ParentStudent::where('user_id', $user->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Siswa ini sudah tertaut ke akun Anda.',
                'errors' => ['nisn' => ['Sudah tertaut.']],
            ], 422);
        }

        $autoVerified = PhoneHelper::matches(trim($validated['parent_phone']), $student->parent_phone);

        $link = ParentStudent::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'relation' => $validated['relation'] ?? null,
            'status' => $autoVerified ? 'active' : 'pending',
            'verified_at' => $autoVerified ? now() : null,
        ]);

        return response()->json([
            'message' => $autoVerified
                ? 'Siswa berhasil ditautkan dan langsung aktif.'
                : 'Siswa ditautkan, menunggu verifikasi pihak sekolah.',
            'link_id' => $link->id,
            'link_status' => $link->status,
            'needs_verification' => ! $autoVerified,
        ], 201);
    }

    /**
     * Riwayat pelanggaran anak (hanya untuk tautan aktif).
     */
    public function violations(Request $request, int $student): JsonResponse
    {
        $user = $request->user();
        $student = $this->assertActiveChild($user, $student);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $violations = Violation::query()
            ->where('student_id', $student->id)
            ->whereNull('deleted_at')
            ->with(['violationType.category', 'evidences'])
            ->orderByDesc('violation_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        $items = collect($violations->items())->map(function (Violation $v) {
            return [
                'id' => $v->id,
                'violation_date' => $v->violation_date?->toDateString(),
                'violation_time' => $v->violation_time?->format('H:i'),
                'description' => $v->description,
                'location' => $v->location,
                'points' => $v->points,
                'sanction' => $v->sanction,
                'handling_status' => $v->handling_status,
                'is_verified' => (bool) $v->is_verified,
                'type' => $v->violationType ? [
                    'name' => $v->violationType->name,
                    'category' => $v->violationType->category?->name,
                ] : null,
                'evidences' => $v->evidences->map(fn ($e) => [
                    'id' => $e->id,
                    'url' => url($e->file_path),
                    'mime_type' => $e->mime_type,
                ])->all(),
            ];
        });

        return response()->json([
            'student' => $this->studentBrief($student),
            'total_points' => $student->total_points,
            'violations' => $items,
            'pagination' => [
                'current_page' => $violations->currentPage(),
                'last_page' => $violations->lastPage(),
                'per_page' => $violations->perPage(),
                'total' => $violations->total(),
            ],
        ]);
    }

    /**
     * Daftar surat SP anak (selain draft).
     */
    public function spLetters(Request $request, int $student): JsonResponse
    {
        $user = $request->user();
        $student = $this->assertActiveChild($user, $student);
        if ($student instanceof JsonResponse) {
            return $student;
        }

        $letters = SpLetter::query()
            ->where('student_id', $student->id)
            ->where('status', '!=', 'draft')
            ->orderByDesc('created_at')
            ->get();

        $items = $letters->map(function (SpLetter $sp) {
            return [
                'id' => $sp->id,
                'letter_number' => $sp->letter_number,
                'title' => $sp->title,
                'body' => $sp->body,
                'status' => $sp->status,
                'total_points_at_time' => $sp->total_points_at_time,
                'violations_included' => $sp->violations_included,
                'file_url' => $sp->file_path ? url($sp->file_path) : null,
                'created_at' => $sp->created_at?->toISOString(),
                'delivered_at' => $sp->delivered_at?->toISOString(),
            ];
        });

        return response()->json([
            'student' => $this->studentBrief($student),
            'sp_letters' => $items,
        ]);
    }

    /**
     * Pastikan siswa adalah anak aktif dari user. Return model atau JsonResponse 403.
     */
    protected function assertActiveChild($user, int $studentId): Student|JsonResponse
    {
        $link = ParentStudent::where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->first();

        if (! $link) {
            return response()->json([
                'message' => 'Akses ditolak. Siswa tidak tertaut atau masih menunggu verifikasi.',
            ], 403);
        }

        return $link->student;
    }

    protected function studentBrief(Student $student): array
    {
        return [
            'id' => $student->id,
            'nisn' => $student->nisn,
            'full_name' => $student->full_name,
            'class_name' => $student->class_name,
            'department_name' => $student->department_name,
        ];
    }
}
