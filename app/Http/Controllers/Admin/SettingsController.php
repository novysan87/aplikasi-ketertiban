<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::where('group', 'school')->get()->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'kepala_sekolah_name' => ['nullable', 'string', 'max:255'],
            'kepala_sekolah_nip' => ['nullable', 'string', 'max:50'],
            'bk_koordinator_name' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value, 'school');
        }

        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('school', 'public');
            Setting::setValue('school_logo', $path, 'school', 'Logo sekolah');
        }

        if ($request->hasFile('login_background')) {
            $path = $request->file('login_background')->store('school', 'public');
            Setting::setValue('login_background', $path, 'school', 'Background halaman login');
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
