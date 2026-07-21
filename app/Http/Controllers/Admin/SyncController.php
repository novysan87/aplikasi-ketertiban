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

    public function index(): View
    {
        $baseUrl = Setting::getValue('kesiswaan_base_url', '');
        $savedToken = Setting::getValue('kesiswaan_token', '');
        $hasToken = !empty($savedToken);
        $studentCount = \App\Models\Student::count();

        return view('settings.sync', compact('baseUrl', 'savedToken', 'hasToken', 'studentCount'));
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
