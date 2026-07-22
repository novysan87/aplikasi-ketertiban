<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Student;
use App\Models\Violation;
use App\Models\SpLetter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $stats = [
            'today_violations' => Violation::whereDate('violation_date', $today)->count(),
            'total_violations' => Violation::count(),
            'total_students' => Student::where('is_active', true)->count(),
            'active_sp' => SpLetter::where('status', 'draft')->count(),
            'unhandled_violations' => Violation::where('handling_status', 'unhandled')->count(),
            'in_progress_violations' => Violation::where('handling_status', 'in_progress')->count(),
            'resolved_violations' => Violation::where('handling_status', 'resolved')->count(),
        ];

        $recentViolations = Violation::with(['student', 'violationType.category', 'recorder'])
            ->latest()
            ->take(10)
            ->get();

        $topStudents = Student::where('is_active', true)
            ->withSum('violations', 'points')
            ->orderByDesc('violations_sum_points')
            ->take(5)
            ->get()
            ->map(function ($s) {
                $s->total_points = $s->violations_sum_points ?? 0;
                return $s;
            });

        $unreadNotifications = AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        $notificationCount = AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        $spThresholds = \App\Models\SpThreshold::where('is_active', true)->get();

        // Data untuk calendar: pelanggaran per tanggal di bulan ini
        $calendarData = Violation::selectRaw('DATE(violation_date) as date, COUNT(*) as count')
            ->whereMonth('violation_date', now()->month)
            ->whereYear('violation_date', now()->year)
            ->groupBy('date')
            ->pluck('count', 'date');

        return view('dashboard.index', compact(
            'stats', 'recentViolations', 'topStudents',
            'unreadNotifications', 'notificationCount', 'spThresholds', 'calendarData'
        ));
    }

    public function getCalendarData(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $data = Violation::selectRaw('DATE(violation_date) as date, COUNT(*) as count')
            ->whereYear('violation_date', $year)
            ->whereMonth('violation_date', $month)
            ->groupBy('date')
            ->pluck('count', 'date');

        return response()->json($data);
    }

    public function markNotificationRead(Request $request, $id)
    {
        $notif = AppNotification::where('user_id', $request->user()->id)->findOrFail($id);
        $notif->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function notificationsIndex(Request $request): View
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function getUnreadCount(Request $request)
    {
        $count = AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecentNotifications(Request $request)
    {
        $notifications = AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }
}
