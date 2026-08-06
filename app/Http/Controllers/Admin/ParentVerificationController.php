<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentVerificationController extends Controller
{
    public function index(): View
    {
        $pending = ParentStudent::query()
            ->where('status', 'pending')
            ->with(['user:id,name,username,phone,created_at', 'student:id,nisn,full_name,class_name,department_name'])
            ->orderByDesc('created_at')
            ->get();

        $recent = ParentStudent::query()
            ->where('status', '!=', 'pending')
            ->with(['user:id,name,username', 'student:id,nisn,full_name,class_name'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        return view('parents.verification', compact('pending', 'recent'));
    }

    public function approve(Request $request, int $linkId): RedirectResponse
    {
        $link = ParentStudent::findOrFail($linkId);

        if ($link->status !== 'pending') {
            return back()->with('error', 'Tautan sudah diproses sebelumnya.');
        }

        $link->update([
            'status' => 'active',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        // Isi data kontak wali ke siswa bila masih kosong
        // (user->phone kini terisi sejak registrasi — register & link-child menyimpannya)
        if ($link->student && empty($link->student->parent_phone) && ! empty($link->user->phone)) {
            $link->student->update([
                'parent_name' => $link->user->name,
                'parent_phone' => $link->user->phone,
            ]);
        }

        return back()->with('success', 'Akun wali "' . $link->user->name . '" untuk ' . $link->student->full_name . ' disetujui.');
    }

    public function reject(Request $request, int $linkId): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $link = ParentStudent::findOrFail($linkId);

        if ($link->status !== 'pending') {
            return back()->with('error', 'Tautan sudah diproses sebelumnya.');
        }

        $link->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Tautan ditolak.');
    }
}
