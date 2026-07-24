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

        $msg = 'Sinkron berhasil! ' . $result['students_created'] . ' siswa baru, ' . $result['students_updated'] . ' diperbarui, ' . $result['classes_created'] . ' kelas baru.';

        if (!empty($result['errors'])) {
            $skipped = count($result['errors']);
            $msg .= ' (' . $skipped . ' dilewati — siswa tidak aktif/tanpa NISN).';
        }

        return back()->with('success', $msg);
    }
}
