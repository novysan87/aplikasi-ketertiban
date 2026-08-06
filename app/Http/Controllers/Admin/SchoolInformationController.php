<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentDevice;
use App\Models\ParentStudent;
use App\Models\SchoolInformation;
use App\Models\ViolationNotification;
use App\Services\FcmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SchoolInformationController extends Controller
{
    public function index(): View
    {
        $items = SchoolInformation::query()
            ->with('creator:id,name')
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->get();

        return view('settings.school-info', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        SchoolInformation::create($validated + ['created_by' => $request->user()->id]);

        if ($validated['is_published'] ?? false) {
            $this->notifyParents($validated['title']);
        }

        return back()->with('success', 'Informasi "'.$validated['title'].'" berhasil dipublikasikan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $info = SchoolInformation::findOrFail($id);
        $validated = $this->validateData($request);

        $info->update($validated);

        if ($validated['is_published'] ?? false) {
            $this->notifyParents($validated['title']);
        }

        return back()->with('success', 'Informasi berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $info = SchoolInformation::findOrFail($id);
        $info->delete();

        return back()->with('success', 'Informasi dihapus.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:50'],
            'event_date' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]) + ['is_published' => $request->boolean('is_published')];
    }

    /**
     * Kirim notifikasi + push informasi sekolah ke semua wali (broadcast).
     * Dedupe: satu notifikasi per judul per hari.
     */
    protected function notifyParents(string $title): void
    {
        try {
            $message = 'Informasi Sekolah: '.$title;

            $already = ViolationNotification::where('channel', 'info')
                ->where('message', $message)
                ->whereDate('created_at', today())
                ->exists();
            if ($already) {
                return;
            }

            $parentIds = ParentStudent::where('status', 'active')
                ->distinct()
                ->pluck('user_id');

            foreach ($parentIds as $userId) {
                ViolationNotification::create([
                    'student_id' => null,
                    'channel' => 'info',
                    'recipient' => 'Semua Wali',
                    'message' => $message,
                    'status' => 'sent',
                    'user_id' => $userId,
                    'created_at' => now(),
                ]);

                $tokens = ParentDevice::where('user_id', $userId)->pluck('fcm_token');
                foreach ($tokens as $token) {
                    app(FcmService::class)->sendToToken($token, [
                        'title' => '📢 Informasi Sekolah',
                        'body' => $title,
                    ], ['type' => 'school_info', 'title' => $title]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Notif info sekolah gagal: '.$e->getMessage());
        }
    }
}
