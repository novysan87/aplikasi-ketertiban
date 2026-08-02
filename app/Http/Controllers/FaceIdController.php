<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Face ID — alur face-first:
 *  - scan: foto wajah -> sistem kenali siswa -> tambah pelanggaran
 *  - register: daftarkan wajah ke siswa yang sudah ada di database
 *
 * Microservice hanya ASISTEN. Bila FaceID mati / siswa belum terdaftar,
 * alur manual tetap berfungsi penuh.
 */
class FaceIdController extends Controller
{
    public function __construct(private FaceRecognitionService $faceid) {}

    public function scan(): View
    {
        return view('face.scan', [
            'faceidConfigured' => $this->faceid->isConfigured(),
        ]);
    }

    public function scanVerify(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        if (! $this->faceid->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'faceid_not_configured']);
        }

        $result = $this->faceid->verify($request->file('photo')->getRealPath());
        if (! $result['ok']) {
            return response()->json(['ok' => false, 'error' => $result['error'] ?? 'faceid_error']);
        }

        $data = $result['data'];
        $candidates = [];
        foreach ($data['top_candidates'] ?? [] as $c) {
            $student = Student::find($c['student_id']);
            if (! $student) {
                continue;
            }

            $candidates[] = [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'class_name' => $student->class_name,
                'score' => $c['score'],
                'total_points' => (int) $student->violations()->sum('points'),
                'recent_violations' => $student->violations()
                    ->with('violationType:id,name')
                    ->latest('violation_date')
                    ->take(3)
                    ->get(['id', 'violation_date', 'violation_type_id', 'points'])
                    ->map(fn ($v) => [
                        'date' => $v->violation_date?->format('d/m/Y'),
                        'type' => $v->violationType?->name ?? '—',
                        'points' => $v->points,
                    ]),
            ];
        }

        return response()->json([
            'ok' => true,
            'matched' => $data['matched'] ?? false,
            'ambiguous' => $data['ambiguous'] ?? false,
            'reason' => $data['reason'] ?? 'tidak_cocok',
            'candidates' => $candidates,
        ]);
    }

    public function register(Request $request): View
    {
        $query = Student::where('is_active', true);

        $q = trim((string) $request->get('q', ''));
        if ($q !== '') {
            $query->where(fn ($qq) => $qq->where('full_name', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%")
                ->orWhere('student_number', 'like', "%{$q}%"));
        }

        $students = $query->orderBy('full_name')->paginate(20)->withQueryString();
        $enrolledMap = $this->faceid->enrolledMap();

        $selected = null;
        if ($request->has('student_id')) {
            $selected = Student::find($request->integer('student_id'));
        }

        return view('face.register', [
            'students' => $students,
            'enrolledMap' => $enrolledMap,
            'selected' => $selected,
            'q' => $q,
            'faceidConfigured' => $this->faceid->isConfigured(),
        ]);
    }

    public function registerStore(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'photos' => ['required', 'array', 'min:1', 'max:3'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $student = Student::findOrFail($request->integer('student_id'));
        $result = $this->enrollPhotos($student, $request->file('photos') ?? []);

        if (! $result['ok']) {
            return back()->with('error', 'Registrasi wajah gagal: '.($result['error'] ?? 'unknown'))
                ->withInput();
        }

        $message = "Wajah {$student->full_name} berhasil terdaftar ({$result['accepted']} foto diterima).";
        if ($result['rejected']) {
            $reasons = collect($result['rejected'])->pluck('reason')->unique()->implode(', ');
            $message .= " Foto ditolak: {$reasons} — ulangi pengambilan foto.";
        }

        return redirect()->route('face.register', ['student_id' => $student->id, 'q' => $request->input('q')])
            ->with('success', $message);
    }

    /**
     * Registrasi cepat dari hasil scan (foto yang sama dipakai untuk enroll).
     * Dipanggil dari kartu "Siswa Belum Terdaftar" di halaman scan.
     */
    public function registerQuick(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $student = Student::findOrFail($request->integer('student_id'));
        $result = $this->enrollPhotos($student, [$request->file('photo')]);

        if (! $result['ok']) {
            return response()->json([
                'ok' => false,
                'error' => $result['error'] ?? 'enroll_failed',
                'detail' => $result['rejected'] ?? null,
            ]);
        }

        return response()->json([
            'ok' => true,
            'name' => $student->full_name,
            'photos_accepted' => $result['accepted'],
            'rejected' => $result['rejected'],
        ]);
    }

    /** Kirim foto ke microservice (logika bersama registerStore & registerQuick). */
    private function enrollPhotos(Student $student, array $photos): array
    {
        if (! $this->faceid->isConfigured()) {
            return ['ok' => false, 'error' => 'faceid_not_configured', 'rejected' => []];
        }

        $tmpPaths = [];
        foreach ($photos as $photo) {
            if (! $photo || ! $photo->isValid()) {
                continue;
            }
            $tmp = tempnam(sys_get_temp_dir(), 'faceid_');
            file_put_contents($tmp, file_get_contents($photo->getRealPath()));
            $tmpPaths[] = $tmp;
        }

        if (! $tmpPaths) {
            return ['ok' => false, 'error' => 'no_valid_photo', 'rejected' => []];
        }

        $result = $this->faceid->enroll($student->id, $student->full_name, $tmpPaths, null);

        foreach ($tmpPaths as $tmp) {
            @unlink($tmp);
        }

        if (! $result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'unknown', 'rejected' => []];
        }

        return [
            'ok' => true,
            'accepted' => $result['data']['photos_accepted'] ?? 0,
            'rejected' => $result['data']['photos_rejected'] ?? [],
        ];
    }
}
