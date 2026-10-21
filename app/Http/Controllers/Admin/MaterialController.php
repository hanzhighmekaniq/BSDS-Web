<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        return view('admin.materials.index', [
            'materials' => Material::withCount('quizzes')->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.materials.form', ['material' => new Material(['is_published' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Material::create($this->validated($request));

        return redirect()->route('admin.materials.index')->with('status', 'Materi berhasil dibuat.');
    }

    public function edit(Material $material): View
    {
        return view('admin.materials.form', compact('material'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $material->update($this->validated($request));

        return redirect()->route('admin.materials.index')->with('status', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $material->delete();

        return redirect()->route('admin.materials.index')->with('status', 'Materi berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:65000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        return [...$data, 'sort_order' => $data['sort_order'] ?? 0, 'is_published' => $request->boolean('is_published')];
    }
}
