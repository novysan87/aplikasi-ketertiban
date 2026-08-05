<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
use App\Models\Student;
use App\Models\User;
use App\Support\PhoneHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ParentAuthController extends Controller
{
    /**
     * Registrasi wali murid.
     *
     * Wali memasukkan NISN anak + nomor HP sendiri. Jika nomor HP cocok dengan
     * data orang tua siswa → langsung aktif. Jika tidak/belum ada data →
     * status pending menunggu verifikasi admin.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:30'],
            'parent_phone' => ['required', 'string', 'min:9', 'max:20'],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'relation' => ['nullable', Rule::in(['father', 'mother', 'guardian'])],
        ]);

        $nisn = trim($validated['nisn']);
        $phone = trim($validated['parent_phone']);

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

        // Akun wali memakai NISN sebagai username (satu akun per siswa pertama,
        // anak berikutnya ditautkan lewat endpoint link).
        $user = User::where('username', $nisn)->first();

        if ($user && ! $user->isParent()) {
            return response()->json([
                'message' => 'NISN ini sudah terdaftar sebagai akun lain.',
                'errors' => ['nisn' => ['NISN sudah digunakan akun lain.']],
            ], 422);
        }

        if ($user) {
            $already = ParentStudent::where('user_id', $user->id)
                ->where('student_id', $student->id)
                ->exists();

            if ($already) {
                return response()->json([
                    'message' => 'Akun wali untuk siswa ini sudah terdaftar. Silakan login.',
                    'errors' => ['nisn' => ['Sudah terdaftar.']],
                ], 422);
            }
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $nisn,
                'email' => $nisn . '@parent.local',
                'password' => $validated['password'],
                'role' => 'parent',
                'roles' => ['parent'],
                'is_active' => true,
            ]);
        }

        // Verifikasi otomatis jika HP cocok dengan data orang tua di database
        $autoVerified = PhoneHelper::matches($phone, $student->parent_phone);

        $link = ParentStudent::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'relation' => $validated['relation'] ?? null,
            'status' => $autoVerified ? 'active' : 'pending',
            'verified_at' => $autoVerified ? now() : null,
        ]);

        $token = $user->createToken('parent-mobile')->plainTextToken;

        return response()->json([
            'message' => $autoVerified
                ? 'Registrasi berhasil. Data anak Anda sudah aktif.'
                : 'Registrasi berhasil. Akun menunggu verifikasi pihak sekolah (biasanya 1×24 jam).',
            'token' => $token,
            'user' => $this->userPayload($user),
            'students' => $this->studentPayloads($user),
            'needs_verification' => ! $autoVerified,
            'link_id' => $link->id,
        ], 201);
    }

    /**
     * Login wali murid (username = NISN anak, password = milik akun wali).
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', trim($validated['username']))->first();

        if (! $user || ! $user->isParent() || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'NISN atau password salah.',
                'errors' => ['username' => ['Kredensial tidak valid.']],
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda dinonaktifkan. Hubungi pihak sekolah.',
            ], 403);
        }

        $token = $user->createToken('parent-mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->userPayload($user),
            'students' => $this->studentPayloads($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $this->userPayload($user),
            'students' => $this->studentPayloads($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
            'role' => 'parent',
        ];
    }

    /**
     * Daftar anak + status tautan (active/pending) + ringkasan poin & SP.
     */
    protected function studentPayloads(User $user): array
    {
        return $user->parentStudents()
            ->with('student.class')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (ParentStudent $link) {
                $student = $link->student;
                $active = $link->status === 'active';

                $payload = [
                    'link_id' => $link->id,
                    'link_status' => $link->status,
                    'relation' => $link->relation,
                    'student' => [
                        'id' => $student->id,
                        'nisn' => $student->nisn,
                        'student_number' => $student->student_number,
                        'full_name' => $student->full_name,
                        'gender' => $student->gender,
                        'class_name' => $student->class_name,
                        'class_level' => $student->class_level,
                        'department_name' => $student->department_name,
                        'photo_url' => $student->photo_path ? url($student->photo_path) : null,
                    ],
                ];

                if ($active) {
                    $latestSp = $student->spLetters()
                        ->where('status', '!=', 'draft')
                        ->orderByDesc('created_at')
                        ->first();

                    // Kehadiran hari ini: status di jam pelajaran pertama yang tercatat
                    $todayAtt = $student->attendances()
                        ->whereDate('date', today())
                        ->orderBy('lesson_hour')
                        ->first();

                    $payload['summary'] = [
                        'total_points' => $student->total_points,
                        'attendance' => [
                            'today_status' => $todayAtt?->status,
                            'school_start' => '07:00', // jam masuk sekolah (e-jurnal time_slots)
                        ],
                        'latest_sp' => $latestSp ? [
                            'letter_number' => $latestSp->letter_number,
                            'title' => $latestSp->title,
                            'status' => $latestSp->status,
                            'total_points_at_time' => $latestSp->total_points_at_time,
                            'date' => $latestSp->created_at?->toDateString(),
                        ] : null,
                    ];
                }

                return $payload;
            })
            ->all();
    }
}
