<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use App\Models\SpLetter;
use App\Models\SpThreshold;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_level')) {
            $query->where('class_level', $request->class_level);
        }

        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        if ($request->filled('department')) {
            $query->where('department_code', $request->department);
        }

        $students = $query->withCount(['violations' => fn($q) => $q->whereNull('deleted_at')])
            ->orderBy('class_name')
            ->orderBy('full_name')
            ->paginate(20);

        // Data untuk dropdown filter
        $classLevels = Student::where('is_active', true)->distinct()->pluck('class_level')->sort()->values();
        $classNames = Student::where('is_active', true)->distinct()->pluck('class_name')->sort()->values();
        $departments = Student::where('is_active', true)
            ->selectRaw('DISTINCT department_code, department_name')
            ->whereNotNull('department_code')
            ->where('department_code', '!=', '')
            ->get()
            ->sortBy('department_name')
            ->pluck('department_name', 'department_code');

        // Data kelas dengan level & jurusan untuk filter dependen
        $classOptions = Student::where('is_active', true)
            ->selectRaw('DISTINCT class_name, class_level, department_code')
            ->whereNotNull('class_name')
            ->where('class_name', '!=', '')
            ->orderBy('class_name')
            ->get()
            ->map(fn($c) => [
                'name' => $c->class_name,
                'level' => $c->class_level,
                'dept' => $c->department_code,
            ]);

        return view('students.index', compact('students', 'classLevels', 'classNames', 'departments', 'classOptions'));
    }

    public function show(Student $student): View
    {
        $student->load(['violations' => function ($q) {
            $q->with(['violationType.category', 'recorder', 'evidences', 'handlings.participants.user'])
              ->latest()
              ->take(50);
        }, 'spLetters' => function ($q) {
            $q->with('spThreshold')->latest();
        }]);

        $totalPoints = $student->total_points;
        $violationCount = $student->violations()->whereNull('deleted_at')->count();
        $lastViolation = $student->violations()->whereNull('deleted_at')->latest()->first();
        $spThresholds = SpThreshold::where('is_active', true)->orderBy('min_points')->get();

        // Cari SP threshold yang sudah tercapai
        $currentSpLevel = null;
        $nextSpThreshold = null;
        foreach ($spThresholds as $threshold) {
            if ($totalPoints >= $threshold->min_points) {
                $currentSpLevel = $threshold;
            } elseif (!$nextSpThreshold) {
                $nextSpThreshold = $threshold;
            }
        }

        // SP yang sudah terbit
        $activeSpLetters = $student->spLetters()->whereIn('status', ['draft', 'issued'])->count();

        return view('students.show', compact(
            'student', 'totalPoints', 'violationCount', 'lastViolation',
            'spThresholds', 'currentSpLevel', 'nextSpThreshold', 'activeSpLetters'
        ));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nisn' => ['nullable', 'string', 'max:50'],
            'student_number' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:L,P'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'class_level' => ['nullable', 'string', 'max:20'],
            'department_code' => ['nullable', 'string', 'max:20'],
            'department_name' => ['nullable', 'string', 'max:100'],
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }
}
