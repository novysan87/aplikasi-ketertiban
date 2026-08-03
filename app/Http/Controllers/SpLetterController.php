<?php

namespace App\Http\Controllers;

use App\Models\SpLetter;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpLetterController extends Controller
{
    public function index(Request $request): View
    {
        $letters = SpLetter::with(['student', 'spThreshold', 'generator'])
            ->when(auth()->user()->isScopedWaliKelas(), fn ($q) => $q->whereHas('student', fn ($qq) => $qq->whereIn('class_id', auth()->user()->homeroomClassIds())))
            ->latest()
            ->paginate(20);

        return view('sp-letters.index', compact('letters'));
    }

    public function show(SpLetter $spLetter): View
    {
        abort_unless(auth()->user()->canViewStudent($spLetter->student_id), 403);

        $spLetter->load(['student', 'spThreshold', 'generator']);
        $school = $this->schoolSettings();

        return view('sp-letters.show', compact('spLetter', 'school'));
    }

    public function print(SpLetter $spLetter)
    {
        abort_unless(auth()->user()->canViewStudent($spLetter->student_id), 403);

        $spLetter->load(['student', 'spThreshold', 'generator']);
        $school = $this->schoolSettings();

        $spLetter->update(['printed_at' => now(), 'status' => 'printed']);

        // For now, return an HTML print view
        return view('sp-letters.print', compact('spLetter', 'school'));
    }

    /**
     * Data sekolah untuk kop surat (format resmi).
     */
    protected function schoolSettings(): array
    {
        return [
            'name' => Setting::getValue('school_name', 'SMK'),
            'address' => Setting::getValue('school_address', ''),
            'phone' => Setting::getValue('school_phone', ''),
            'logo' => Setting::getValue('school_logo', ''),
            'kop_logo' => Setting::getValue('kop_logo', ''),
            'government' => Setting::getValue('school_government', 'PEMERINTAH PROVINSI JAWA TIMUR'),
            'agency' => Setting::getValue('school_agency', 'DINAS PENDIDIKAN'),
            'full_name' => Setting::getValue('school_full_name', Setting::getValue('school_name', 'SMK')),
            'address_detail' => Setting::getValue('school_address_detail', ''),
            'website_email' => Setting::getValue('school_website_email', ''),
            'postal' => Setting::getValue('school_postal', ''),
            'kepala_sekolah' => Setting::getValue('kepala_sekolah_name', ''),
            'kepala_sekolah_nip' => Setting::getValue('kepala_sekolah_nip', ''),
        ];
    }
}
