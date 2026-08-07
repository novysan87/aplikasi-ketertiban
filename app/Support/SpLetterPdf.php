<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\SpLetter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Generator PDF surat peringatan resmi (kop surat + tanda tangan).
 * Dipakai saat SP dibuat agar wali murid bisa melihat/unduh lewat SiMURID.
 */
class SpLetterPdf
{
    public static function generate(SpLetter $spLetter): ?string
    {
        try {
            $spLetter->load(['student', 'spThreshold', 'generator']);

            $school = [
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
                'place' => Setting::getValue('school_place', 'Wonorejo'),
            ];

            $pdf = Pdf::loadView('sp-letters.print', [
                'spLetter' => $spLetter,
                'school' => $school,
                'forPdf' => true,
            ])->setPaper('folio'); // F4 = 210 x 330 mm

            $path = 'sp-letters/sp-' . $spLetter->id . '.pdf';
            Storage::disk('public')->put($path, $pdf->output());

            return $path;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
