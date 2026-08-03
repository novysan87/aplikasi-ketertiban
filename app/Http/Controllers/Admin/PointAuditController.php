<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointAuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = PointAuditLog::with(['student', 'actor']);

        // Wali kelas murni: hanya siswa di kelasnya
        if (auth()->user()->isScopedWaliKelas()) {
            $query->whereHas('student', fn ($q) => $q->whereIn('class_id', auth()->user()->homeroomClassIds()));
        }

        $query->when($request->filled('search'), fn ($q) => $q->whereHas('student', fn ($qq) => $qq
            ->where('full_name', 'like', "%{$request->search}%")
            ->orWhere('nisn', 'like', "%{$request->search}%")));

        $query->when($request->filled('action'), fn ($q) => $q->where('action', $request->action));
        $query->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from));
        $query->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $logs = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $summary = [
            'total' => PointAuditLog::count(),
            'penambahan' => PointAuditLog::where('points_delta', '>', 0)->count(),
            'pengurangan' => PointAuditLog::where('points_delta', '<', 0)->count(),
        ];

        return view('point-audit.index', compact('logs', 'summary'));
    }
}
