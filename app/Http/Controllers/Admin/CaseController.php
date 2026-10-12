<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CaseRequest;
use App\Models\CaseModel;
use App\Services\ScenarioGraphValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function __construct(protected ScenarioGraphValidator $validator) {}

    public function index(): View
    {
        $cases = CaseModel::withCount(['scenarios', 'simulationSessions'])->orderBy('sort_order')->get();

        $graphErrors = $cases->mapWithKeys(fn ($case) => [$case->id => $this->validator->validate($case)]);

        return view('admin.cases.index', compact('cases', 'graphErrors'));
    }

    /**
     * Metadata kasus + gambaran graf skenario (read-only) + hasil validasi.
     */
    public function edit(CaseModel $case): View
    {
        $scenarios = $case->scenarios()
            ->with(['options.nextScenario', 'investigationItems', 'hints'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.cases.form', [
            'case' => $case,
            'scenarios' => $scenarios,
            'graphErrors' => $this->validator->validate($case),
            'sessionCount' => $case->simulationSessions()->count(),
        ]);
    }

    public function update(CaseRequest $request, CaseModel $case): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? $case->sort_order;

        // Kasus dengan graf tidak valid tidak boleh diaktifkan.
        if ($data['is_active'] && $this->validator->validate($case) !== []) {
            return back()->withErrors(['is_active' => 'Kasus tidak dapat diaktifkan karena graf skenarionya belum valid.'])->withInput();
        }

        $case->update($data);

        return redirect()->route('admin.cases.index')->with('status', 'Kasus berhasil diperbarui.');
    }
}
