<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliMuridController extends Controller
{
    /**
     * Data akun wali murid (terpisah dari user internal sekolah).
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'parent')
            ->with(['parentStudents' => fn ($q) => $q->with('student:id,nisn,full_name,class_name')]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        $total = User::where('role', 'parent')->count();
        $totalAktif = User::where('role', 'parent')
            ->whereHas('parentStudents', fn ($q) => $q->where('status', 'active'))
            ->count();

        return view('parents.index', compact('users', 'total', 'totalAktif'));
    }
}
