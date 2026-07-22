<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Student;
use App\Models\Violation;
use App\Models\SpLetter;
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

        return view('dashboard.index', compact(
            'stats', 'recentViolations', 'topStudents',
            'unreadNotifications', 'notificationCount', 'spThresholds'
        ));
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
