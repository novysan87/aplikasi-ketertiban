<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\JsonResponse;

class ParentSchoolInformationController extends Controller
{
    /**
     * Daftar informasi sekolah yang dipublikasikan (untuk app wali murid).
     */
    public function index(): JsonResponse
    {
        $items = SchoolInformation::query()
            ->where('is_published', true)
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
}
