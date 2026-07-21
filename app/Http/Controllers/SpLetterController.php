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
            ->latest()
            ->paginate(20);

        return view('sp-letters.index', compact('letters'));
    }

    public function show(SpLetter $spLetter): View
    {
        $spLetter->load(['student', 'spThreshold', 'generator']);
        $school = [
            'name' => Setting::getValue('school_name', 'SMK'),
            'address' => Setting::getValue('school_address', ''),
            'phone' => Setting::getValue('school_phone', ''),
            'kepala_sekolah' => Setting::getValue('kepala_sekolah_name', ''),
            'kepala_sekolah_nip' => Setting::getValue('kepala_sekolah_nip', ''),
        ];

        return view('sp-letters.show', compact('spLetter', 'school'));
    }

    public function print(SpLetter $spLetter)
    {
        $spLetter->load(['student', 'spThreshold', 'generator']);
        $school = [
            'name' => Setting::getValue('school_name', 'SMK'),
            'address' => Setting::getValue('school_address', ''),
            'phone' => Setting::getValue('school_phone', ''),
            'kepala_sekolah' => Setting::getValue('kepala_sekolah_name', ''),
            'kepala_sekolah_nip' => Setting::getValue('kepala_sekolah_nip', ''),
        ];

        $spLetter->update(['printed_at' => now(), 'status' => 'printed']);

        // For now, return an HTML print view
        return view('sp-letters.print', compact('spLetter', 'school'));
    }
}
