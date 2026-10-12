<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        return view('admin.classrooms.index', [
            'classrooms' => Classroom::withCount('studentProfiles')->orderByDesc('academic_year')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.classrooms.form', ['classroom' => new Classroom(['is_active' => true])]);
    }

    public function store(ClassroomRequest $request): RedirectResponse
    {
        Classroom::create($request->validated());

        return redirect()->route('admin.classrooms.index')->with('status', 'Kelas berhasil dibuat.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('admin.classrooms.form', compact('classroom'));
    }

    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return redirect()->route('admin.classrooms.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        if ($classroom->studentProfiles()->exists()) {
            return back()->withErrors(['delete' => 'Kelas masih memiliki siswa. Pindahkan atau nonaktifkan kelas ini.']);
        }

        $classroom->delete();

        return redirect()->route('admin.classrooms.index')->with('status', 'Kelas berhasil dihapus.');
    }
}
