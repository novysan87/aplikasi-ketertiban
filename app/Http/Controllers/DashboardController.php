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

        // Wali kelas murni → data dibatasi ke kelas yang diwalikan
        $scoped = auth()->user()->isScopedWaliKelas();
        $scopedStudentIds = $scoped ? Student::whereIn('class_id', auth()->user()->homeroomClassIds())->pluck('id')->all() : [];
        $scopedClassIds = $scoped ? auth()->user()->homeroomClassIds() : [];
        $vScope = fn ($q) => $q->when($scoped, fn ($qq) => $qq->whereIn('student_id', $scopedStudentIds));

        $stats = [
            'today_violations' => Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))->whereDate('violation_date', $today)->count(),
            'total_violations' => Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))->count(),
            'total_students' => Student::where('is_active', true)->when($scoped, fn ($q) => $q->whereIn('class_id', $scopedClassIds))->count(),
            'active_sp' => SpLetter::where('status', 'draft')->when($scoped, fn ($q) => $q->whereHas('student', fn ($qq) => $qq->whereIn('class_id', $scopedClassIds)))->count(),
            'unhandled_violations' => Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))->where('handling_status', 'unhandled')->count(),
            'in_progress_violations' => Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))->where('handling_status', 'in_progress')->count(),
            'resolved_violations' => Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))->where('handling_status', 'resolved')->count(),
        ];

        $recentViolations = Violation::with(['student', 'violationType.category', 'recorder'])
            ->when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))
            ->latest()
            ->take(10)
            ->get();

        $topStudents = Student::where('is_active', true)
            ->when($scoped, fn ($q) => $q->whereIn('class_id', $scopedClassIds))
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

        $spThresholds = \App\Models\SpThreshold::where('is_active', true)->orderBy('min_points')->get();

        // Data untuk calendar: pelanggaran per tanggal di bulan ini
        $calendarData = Violation::selectRaw('DATE(violation_date) as date, COUNT(*) as count')
            ->when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))
            ->whereMonth('violation_date', now()->month)
            ->whereYear('violation_date', now()->year)
            ->groupBy('date')
            ->pluck('count', 'date');

        // Tren pelanggaran HARIAN — 7 / 14 / 30 hari terakhir
        $dailySet = function (int $days) use ($scoped, $scopedStudentIds): array {
            $labels = [];
            $data = [];
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $labels[] = $d->format('d/m');
                $data[] = Violation::when($scoped, fn ($q) => $q->whereIn('student_id', $scopedStudentIds))
                    ->whereDate('violation_date', $d->toDateString())->count();
            }

            return [$labels, $data];
        };
        [$dailyLabels7, $dailyData7] = $dailySet(7);
        [$dailyLabels14, $dailyData14] = $dailySet(14);
        [$dailyLabels30, $dailyData30] = $dailySet(30);

        $dailyCurrent = $dailyData7[6] ?? 0;            // hari ini
        $dailyPrev = $dailyData7[5] ?? 0;               // kemarin
        $trendPercent = $dailyPrev > 0 ? round((($dailyCurrent - $dailyPrev) / $dailyPrev) * 100) : ($dailyCurrent > 0 ? 100 : 0);
        $trendTotal7 = array_sum($dailyData7);
        $trendTotal14 = array_sum($dailyData14);
        $trendTotal30 = array_sum($dailyData30);

        // Pelanggaran per jenis (grafik batang) — 3 periode
        $typeCounts = function ($from = null, $month = null) use ($scoped, $scopedStudentIds) {
            $q = Violation::query();
            if ($scoped) $q->whereIn('student_id', $scopedStudentIds);
            if ($from) $q->whereDate('violation_date', '>=', $from);
            if ($month) $q->whereMonth('violation_date', $month)->whereYear('violation_date', now()->year);
            return $q->selectRaw('violation_type_id, COUNT(*) as jml')
                ->groupBy('violation_type_id')
                ->pluck('jml', 'violation_type_id');
        };
        $typeMapToday = $typeCounts(now()->toDateString());
        $typeMapWeek = $typeCounts(now()->subDays(7)->toDateString());
        $typeMapMonth = $typeCounts(null, now()->month);
        $typeNames = [];
        $typeToday = [];
        $typeWeek = [];
        $typeMonth = [];
        foreach (\App\Models\ViolationType::where('is_active', true)->orderBy('name')->get() as $t) {
            $typeNames[] = $t->name;
            $typeToday[] = $typeMapToday[$t->id] ?? 0;
            $typeWeek[] = $typeMapWeek[$t->id] ?? 0;
            $typeMonth[] = $typeMapMonth[$t->id] ?? 0;
        }

        return view('dashboard.index', compact(
            'stats', 'recentViolations', 'topStudents',
            'unreadNotifications', 'notificationCount', 'spThresholds', 'calendarData',
            'dailyLabels7', 'dailyData7', 'dailyLabels14', 'dailyData14', 'dailyLabels30', 'dailyData30',
            'dailyCurrent', 'trendPercent', 'trendTotal7', 'trendTotal14', 'trendTotal30',
            'typeNames', 'typeToday', 'typeWeek', 'typeMonth'
        ));
    }

    public function getCalendarData(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $data = Violation::selectRaw('DATE(violation_date) as date, COUNT(*) as count')
            ->when(auth()->user()->isScopedWaliKelas(), fn ($q) => $q->whereIn('student_id',
                Student::whereIn('class_id', auth()->user()->homeroomClassIds())->pluck('id')->all()))
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
