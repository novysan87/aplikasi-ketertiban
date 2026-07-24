<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\StudentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SyncController extends Controller
{
    protected StudentSyncService $syncService;

    public function __construct(StudentSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function saveEjurnalToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ejurnal_token' => ['required', 'string', 'min:16'],
        ]);

        Setting::setValue('ejurnal_sync_token', $validated['ejurnal_token'], 'integration', 'Token untuk push presensi dari E-Jurnal Guru');

        return back()->with('success', 'Token E-Jurnal berhasil ' . ($request->input('_regenerate') ? 'diperbarui' : 'disimpan') . '.');
    }

    public function generateEjurnalToken(): RedirectResponse
    {
        $token = \Illuminate\Support\Str::random(48);
        Setting::setValue('ejurnal_sync_token', $token, 'integration', 'Token untuk push presensi dari E-Jurnal Guru');

        return back()->with('success', 'Token baru berhasil digenerate. Salin dan paste di E-Jurnal Guru.');
    }

    public function index(): View
    {
        $baseUrl = Setting::getValue('kesiswaan_base_url', '');
        $savedToken = Setting::getValue('kesiswaan_token', '');
        $hasToken = !empty($savedToken);
        $studentCount = \App\Models\Student::count();

        $ejurnalToken = Setting::getValue('ejurnal_sync_token', '');
        $hasEjurnalToken = !empty($ejurnalToken);

        return view('settings.sync', compact('baseUrl', 'savedToken', 'hasToken', 'studentCount', 'ejurnalToken', 'hasEjurnalToken'));
    }

    public function testKesiswaanConnection(Request $request): \Illuminate\Http\JsonResponse
    {
        $url = $request->input('base_url', Setting::getValue('kesiswaan_base_url', ''));
        $token = $request->input('token', Setting::getValue('kesiswaan_token', ''));

        if (empty($url) || empty($token)) {
            return response()->json(['success' => false, 'message' => 'URL atau token belum diisi.'], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->get(rtrim($url, '/') . '/api/integration/students?per_page=1');

            if ($response->successful()) {
                $data = $response->json();
                $count = $data['meta']['total'] ?? count($data['data'] ?? []);
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi berhasil.',
                    'data' => [
                        'students' => $count,
                    ],
                ]);
            } elseif ($response->status() === 401) {
                return response()->json(['success' => false, 'message' => 'Token tidak valid (401).'], 422);
            } else {
                return response()->json(['success' => false, 'message' => 'Respon server: ' . $response->status()], 422);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat terhubung: ' . $e->getMessage()], 422);
        }
    }

    public function syncNow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'base_url' => ['required', 'url'],
            'token' => ['nullable', 'string'],
        ]);

        // Use saved token if not provided in form
        if (empty($validated['token'])) {
            $validated['token'] = Setting::getValue('kesiswaan_token', '');
        }

        if (empty($validated['token'])) {
            return back()->with('error', 'Token sinkronisasi harus diisi.');
        }

        $result = $this->syncService->sync($validated['base_url'], $validated['token']);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        // Save to settings
        Setting::setValue('kesiswaan_base_url', $validated['base_url'], 'integration', 'URL Database Kesiswaan');
        Setting::setValue('kesiswaan_token', $validated['token'], 'integration', 'Token sinkronisasi');

        $deactivated = $result['students_deactivated'] ?? 0;
        $msg = 'Sinkron berhasil! ' . $result['students_created'] . ' siswa baru, ' . $result['students_updated'] . ' diperbarui, ' . $result['classes_created'] . ' kelas baru.';
        if ($deactivated > 0) {
            $msg .= ' ' . $deactivated . ' siswa dinonaktifkan (tidak aktif tahun ajaran baru).';
        }

        if (!empty($result['errors'])) {
            $skipped = count($result['errors']);
            $msg .= ' (' . $skipped . ' dilewati — siswa tidak aktif/tanpa NISN).';
        }

        return back()->with('success', $msg);
    }
}
