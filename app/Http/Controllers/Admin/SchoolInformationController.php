<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return back()->with('success', 'Informasi "' . $validated['title'] . '" berhasil dipublikasikan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $info = SchoolInformation::findOrFail($id);
        $validated = $this->validateData($request);

        $info->update($validated);

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
}
