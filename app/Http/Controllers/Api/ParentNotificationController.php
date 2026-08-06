<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentDevice;
use App\Models\ParentStudent;
use App\Models\ViolationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentNotificationController extends Controller
{
    /**
     * Notifikasi untuk anak-anak wali (SP baru, dll).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $studentIds = ParentStudent::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->all();

        if (empty($studentIds)) {
            return response()->json(['notifications' => []]);
        }

        $notifications = ViolationNotification::query()
            ->where(function ($q) use ($studentIds) {
                $q->whereIn('student_id', $studentIds)
                    ->orWhere(function ($q2) use ($studentIds) {
                        // Info sekolah (broadcast): student_id null, per user
                        $q2->whereNull('student_id')->where('user_id', auth()->id());
                    });
            })
            ->with('student:id,full_name,class_name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $items = $notifications->map(function (ViolationNotification $n) {
            return [
                'id' => $n->id,
                'student_name' => $n->student?->full_name,
                'class_name' => $n->student?->class_name,
                'channel' => $n->channel,
                'recipient' => $n->recipient,
                'message' => $n->message,
                'status' => $n->status,
                'is_read' => (bool) $n->is_read,
                'created_at' => $n->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'notifications' => $items,
            'unread_count' => $notifications->where('status', '!=', 'sent')->count(),
        ]);
    }

    /**
     * Jumlah notifikasi penting (pelanggaran/SP) yang BELUM dibaca — untuk badge.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $studentIds = ParentStudent::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->all();

        if (empty($studentIds)) {
            return response()->json(['count' => 0]);
        }

        $count = ViolationNotification::where(function ($q) use ($studentIds) {
                $q->whereIn('student_id', $studentIds)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('student_id')->where('user_id', auth()->id());
                    });
            })
            ->whereIn('channel', ['push', 'info']) // pelanggaran, SP, info sekolah
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Tandai semua notifikasi putra/putri sebagai sudah dibaca.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $studentIds = ParentStudent::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->all();

        $updated = 0;
        if (! empty($studentIds)) {
            $updated = ViolationNotification::where(function ($q) use ($studentIds) {
                    $q->whereIn('student_id', $studentIds)
                        ->orWhere(function ($q2) {
                            $q2->whereNull('student_id')->where('user_id', auth()->id());
                        });
                })
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['updated' => $updated, 'count' => 0]);
    }

    /**
     * Daftarkan perangkat (token FCM) untuk push notification.
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', Rule::in(['android', 'ios', 'web'])],
            'fcm_token' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        $device = ParentDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'fcm_token' => $validated['fcm_token'],
            ],
            [
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Perangkat terdaftar.',
            'device_id' => $device->id,
        ], 201);
    }

    /**
     * Endpoint debug: catat error sisi klien (FCM web) ke log server.
     */
    public function pushDebug(Request $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::error('PUSH_DEBUG: '.json_encode($request->only(['step', 'error', 'detail', 'ua'])));

        return response()->json(['ok' => true]);
    }
}
