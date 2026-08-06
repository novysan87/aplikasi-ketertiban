<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ParentSchoolInformationController extends Controller
{
    /**
     * Daftar informasi sekolah yang dipublikasikan (untuk app wali murid).
     */
    public function index(): JsonResponse
    {
        $items = SchoolInformation::query()
            ->where('is_published', true)
            ->whereDate('event_date', today()) // hanya yang tanggalnya hari ini
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (SchoolInformation $i) => [
                'id' => $i->id,
                'title' => $i->title,
                'content' => $i->content,
                'category' => $i->category,
                'event_date' => $i->event_date?->toDateString(),
                'created_at' => $i->created_at?->toISOString(),
            ]);

        return response()->json(['informations' => $items]);
    }

    /**
     * Kalender akademik (dari tabel academic_calendar_events).
     */
    public function calendar(): JsonResponse
    {
        $events = DB::table('academic_calendar_events')
            ->orderBy('date')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->date,
                'date_end' => $e->date_end,
                'title' => $e->title,
                'category' => $e->category,
                'semester' => $e->semester,
            ]);

        return response()->json([
            'academic_year' => '2026/2027',
            'events' => $events,
        ]);
    }
}
