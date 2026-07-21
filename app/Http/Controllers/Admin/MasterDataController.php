<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpThreshold;
use App\Models\ViolationCategory;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    // === Violation Categories ===
    public function categories(): View
    {
        $categories = ViolationCategory::orderBy('sort_order')->get();
        return view('settings.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        ViolationCategory::create($validated);

        return back()->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, ViolationCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(ViolationCategory $category): RedirectResponse
    {
        if ($category->violationTypes()->count() > 0) {
            return back()->with('error', 'Kategori masih memiliki jenis pelanggaran. Hapus jenis pelanggaran terlebih dahulu.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    // === Violation Types ===
    public function types(Request $request): View
    {
        $query = ViolationType::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('default_sanction', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $types = $query->orderBy('category_id')->orderBy('name')->paginate(20);
        $categories = ViolationCategory::orderBy('sort_order')->get();

        return view('settings.violation-types', compact('types', 'categories'));
    }

    public function storeType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:violation_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0', 'max:500'],
            'default_sanction' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        ViolationType::create($validated);

        return back()->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function updateType(Request $request, ViolationType $type): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:violation_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:0', 'max:500'],
            'default_sanction' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $type->update($validated);

        return back()->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroyType(ViolationType $type): RedirectResponse
    {
        if ($type->violations()->count() > 0) {
            return back()->with('error', 'Jenis pelanggaran ini sudah tercatat pada pelanggaran siswa. Nonaktifkan saja.');
        }

        $type->delete();

        return back()->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }

    // === SP Thresholds ===
    public function thresholds(): View
    {
        $thresholds = SpThreshold::orderBy('min_points')->get();
        return view('settings.thresholds', compact('thresholds'));
    }

    public function updateThresholds(Request $request): RedirectResponse
    {
        $thresholds = $request->validate([
            'thresholds' => ['required', 'array'],
            'thresholds.*.id' => ['required', 'exists:sp_thresholds,id'],
            'thresholds.*.min_points' => ['required', 'integer', 'min:0'],
            'thresholds.*.max_points' => ['nullable', 'integer', 'min:0'],
            'thresholds.*.name' => ['required', 'string', 'max:255'],
            'thresholds.*.default_description' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($thresholds['thresholds'] as $data) {
            SpThreshold::where('id', $data['id'])->update([
                'name' => $data['name'],
                'min_points' => $data['min_points'],
                'max_points' => $data['max_points'],
                'default_description' => $data['default_description'],
            ]);
        }

        return back()->with('success', 'Ambang batas SP berhasil diperbarui.');
    }
}
