<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Violation;
use App\Models\ViolationHandling;
use App\Models\ViolationType;
use App\Models\AppNotification;
use App\Models\HandlingParticipant;
use App\Services\ViolationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ViolationController extends Controller
{
    protected ViolationService $violationService;

    public function __construct(ViolationService $violationService)
    {
        $this->violationService = $violationService;
    }

    public function index(Request $request): View
    {
        $query = Violation::with(['student', 'violationType.category', 'recorder']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('violationType', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('class_level')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_level', $request->class_level);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('violation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('violation_date', '<=', $request->date_to);
        }

        if ($request->filled('handling_status')) {
            $query->where('handling_status', $request->handling_status);
        }

        $violations = $query->latest()->paginate(20);
        $categories = \App\Models\ViolationCategory::where('is_active', true)->get();

        return view('violations.index', compact('violations', 'categories'));
    }

    public function create(): View
    {
        $students = Student::where('is_active', true)->orderBy('full_name')->get();
        $violationTypes = ViolationType::with('category')->where('is_active', true)->get();

        // Group by category for searchable dropdown
        $typeGroups = $violationTypes->groupBy(fn($t) => $t->category?->name ?? 'Lainnya')
            ->map(fn($group, $cat) => [
                'label' => $cat,
                'color' => $group->first()->category?->color ?? '#6b7280',
                'types' => $group->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'points' => $t->points,
                    'sanction' => $t->default_sanction,
                ])->values(),
            ])->values();

        return view('violations.create', compact('students', 'violationTypes', 'typeGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'violation_type_ids' => ['required', 'array', 'min:1', 'max:10'],
            'violation_type_ids.*' => ['required', 'exists:violation_types,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'violation_date' => ['required', 'date'],
            'violation_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'evidences' => ['nullable', 'array', 'max:5'],
            'evidences.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $evidences = $request->file('evidences') ?? [];
        $created = 0;

        foreach ($validated['violation_type_ids'] as $typeId) {
            $type = ViolationType::findOrFail($typeId);

            $data = array_merge($validated, [
                'violation_type_id' => $typeId,
                'points' => $type->points,
                'sanction' => $type->default_sanction,
                'evidences' => $evidences,
            ]);

            $this->violationService->recordViolation($data, $request->user()->id);
            $created++;
        }

        return redirect()->route('violations.index')
            ->with('success', "{$created} pelanggaran berhasil dicatat.");
    }

    public function show(Violation $violation): View
    {
        $violation->load([
            'student', 'violationType.category', 'recorder', 'evidences',
            'handlings.participants.user', 'handlings.creator',
        ]);

        $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();

        return view('violations.show', compact('violation', 'users'));
    }

    public function storeHandling(Request $request, Violation $violation): RedirectResponse
    {
        $validated = $request->validate([
            'handling_type' => ['required', 'string', 'max:50'],
            'handling_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'evidence' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx', 'max:10240'],
            'participants' => ['nullable', 'array', 'max:20'],
            'participants.*.user_id' => ['required_with:participants', 'exists:users,id'],
            'participants.*.role' => ['nullable', 'string', 'max:50'],
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('handling-evidence', 'public');
        }

        $handling = ViolationHandling::create([
            'violation_id' => $violation->id,
            'handling_type' => $validated['handling_type'],
            'handling_date' => $validated['handling_date'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'evidence' => $evidencePath,
            'created_by' => $request->user()->id,
        ]);

        // Add participants
        if (!empty($validated['participants'])) {
            foreach ($validated['participants'] as $p) {
                HandlingParticipant::create([
                    'handling_id' => $handling->id,
                    'user_id' => $p['user_id'],
                    'role' => $p['role'] ?? null,
                ]);
            }
        }

        // Update violation status
        if ($violation->isUnhandled()) {
            $violation->update(['handling_status' => 'in_progress']);
        }

        return redirect()->route('violations.show', $violation->id)
            ->with('success', 'Penanganan berhasil ditambahkan.');
    }

    public function resolveHandling(Request $request, Violation $violation): RedirectResponse
    {
        $violation->update([
            'handling_status' => 'resolved',
            'handled_at' => now(),
            'handled_by' => $request->user()->id,
        ]);

        return redirect()->route('violations.show', $violation->id)
            ->with('success', 'Pelanggaran ditandai selesai ditangani.');
    }

    public function destroyHandling(Violation $violation, ViolationHandling $handling): RedirectResponse
    {
        $handling->delete();

        // If no more handlings, revert to unhandled
        if ($violation->handlings()->count() === 0) {
            $violation->update(['handling_status' => 'unhandled']);
        }

        return redirect()->route('violations.show', $violation->id)
            ->with('success', 'Catatan penanganan berhasil dihapus.');
    }

    public function verify(Request $request, Violation $violation): RedirectResponse
    {
        $violation->update([
            'is_verified' => true,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Pelanggaran berhasil diverifikasi.');
    }

    public function destroy(Violation $violation): RedirectResponse
    {
        $violation->delete();

        return redirect()->route('violations.index')
            ->with('success', 'Pelanggaran berhasil dihapus.');
    }

    public function searchStudents(Request $request)
    {
        $search = $request->get('q', '');
        $students = Student::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('full_name')
            ->take(20)
            ->get(['id', 'nisn', 'full_name', 'class_name', 'class_level']);

        return response()->json($students);
    }
}
