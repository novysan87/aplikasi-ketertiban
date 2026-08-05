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
