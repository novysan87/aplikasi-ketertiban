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
            ->whereIn('student_id', $studentIds)
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
                'created_at' => $n->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'notifications' => $items,
            'unread_count' => $notifications->where('status', '!=', 'sent')->count(),
        ]);
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
