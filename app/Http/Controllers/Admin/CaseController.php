<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CaseRequest;
use App\Models\CaseModel;
use App\Services\ScenarioGraphValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseController extends Controller
{
    public function __construct(protected ScenarioGraphValidator $validator) {}

    public function index(): View
    {
        $cases = CaseModel::withCount(['scenarios', 'simulationSessions'])->orderBy('sort_order')->get();

        $graphErrors = $cases->mapWithKeys(fn ($case) => [$case->id => $this->validator->validate($case)]);

        return view('admin.cases.index', compact('cases', 'graphErrors'));
    }

    public function create(): View
    {
        return view('admin.cases.form', [
            'case' => new CaseModel(['level' => 'foundation', 'estimated_time' => 15, 'sort_order' => 0, 'is_active' => false]),
            'scenarios' => collect(),
            'graphErrors' => [],
            'sessionCount' => 0,
        ]);
    }

    public function store(CaseRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'code', 'title', 'description', 'role_title', 'briefing_text', 'level', 'category', 'estimated_time', 'sort_order',
        ]);
        $data['is_active'] = false;
        $data['sort_order'] = $data['sort_order'] ?? ((int) CaseModel::max('sort_order') + 1);

        $case = CaseModel::create($data);

        return redirect()->route('admin.cases.edit', $case)
            ->with('status', 'Kasus berhasil dibuat. Tambahkan skenario sebelum mengaktifkannya.');
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

    public function destroy(CaseModel $case): RedirectResponse
    {
        if ($case->simulationSessions()->exists()) {
            return back()->withErrors([
                'delete' => 'Kasus ini sudah memiliki riwayat sesi simulasi siswa dan tidak dapat dihapus demi integritas data.',
            ]);
        }

        DB::transaction(function () use ($case) {
            foreach ($case->scenarios as $scenario) {
                foreach ($scenario->options as $option) {
                    $option->indicatorScores()->delete();
                }
                $scenario->options()->delete();
                $scenario->investigationItems()->delete();
                $scenario->hints()->delete();
                $scenario->delete();
            }
            $case->delete();
        });

        return redirect()->route('admin.cases.index')->with('status', "Kasus {$case->code} berhasil dihapus.");
    }

    public function duplicate(CaseModel $case): RedirectResponse
    {
        $newCase = DB::transaction(function () use ($case) {
            // Generate unique code
            $baseCode = $case->code . '-COPY';
            $code = $baseCode;
            $counter = 1;
            while (CaseModel::where('code', $code)->exists()) {
                $code = $baseCode . '-' . $counter;
                $counter++;
            }

            $clonedCase = CaseModel::create([
                'code' => $code,
                'title' => $case->title . ' (Duplikat)',
                'description' => $case->description,
                'level' => $case->level,
                'role_title' => $case->role_title,
                'briefing_text' => $case->briefing_text,
                'estimated_time' => $case->estimated_time,
                'is_active' => false,
                'sort_order' => ((int) CaseModel::max('sort_order')) + 1,
            ]);

            // Map old scenario id -> new scenario id
            $scenarioMap = [];

            // Pass 1: Clone all scenarios
            foreach ($case->scenarios()->orderBy('sort_order')->get() as $oldScenario) {
                $newScenario = $clonedCase->scenarios()->create([
                    'step_code' => $oldScenario->step_code,
                    'scenario_type' => $oldScenario->scenario_type,
                    'is_start' => $oldScenario->is_start,
                    'decision_no' => $oldScenario->decision_no,
                    'title' => $oldScenario->title,
                    'story_text' => $oldScenario->story_text,
                    'customer_dialogue' => $oldScenario->customer_dialogue,
                    'new_problem_text' => $oldScenario->new_problem_text,
                    'is_ending' => $oldScenario->is_ending,
                    'max_investigations' => $oldScenario->max_investigations,
                    'sort_order' => $oldScenario->sort_order,
                ]);
                $scenarioMap[$oldScenario->id] = $newScenario->id;

                // Clone investigation items
                foreach ($oldScenario->investigationItems as $item) {
                    $newScenario->investigationItems()->create([
                        'title' => $item->title,
                        'type' => $item->type,
                        'content' => $item->content,
                        'is_relevant' => $item->is_relevant,
                        'sort_order' => $item->sort_order,
                    ]);
                }

                // Clone hints
                foreach ($oldScenario->hints as $hint) {
                    $newScenario->hints()->create([
                        'level' => $hint->level,
                        'hint_text' => $hint->hint_text,
                    ]);
                }
            }

            // Pass 2: Clone options with remapped next_scenario_id
            foreach ($case->scenarios as $oldScenario) {
                $newScenarioId = $scenarioMap[$oldScenario->id];
                $newScenario = \App\Models\Scenario::find($newScenarioId);

                foreach ($oldScenario->options as $oldOption) {
                    $remappedNextId = $oldOption->next_scenario_id ? ($scenarioMap[$oldOption->next_scenario_id] ?? null) : null;

                    $newOption = $newScenario->options()->create([
                        'label' => $oldOption->label,
                        'option_text' => $oldOption->option_text,
                        'consequence_text' => $oldOption->consequence_text,
                        'consequence_type' => $oldOption->consequence_type,
                        'outcome_tag' => $oldOption->outcome_tag,
                        'next_scenario_id' => $remappedNextId,
                        'sort_order' => $oldOption->sort_order,
                    ]);

                    // Clone scores
                    foreach ($oldOption->indicatorScores as $score) {
                        $newOption->indicatorScores()->create([
                            'indicator_id' => $score->indicator_id,
                            'score' => $score->score,
                        ]);
                    }
                }
            }

            return $clonedCase;
        });

        return redirect()->route('admin.cases.edit', $newCase)
            ->with('status', "Kasus berhasil diduplikasi menjadi {$newCase->code}.");
    }

    public function export(CaseModel $case): StreamedResponse
    {
        $case->load([
            'scenarios.investigationItems',
            'scenarios.hints',
            'scenarios.options.indicatorScores.indicator',
        ]);

        $payload = [
            'version' => '1.0',
            'case' => [
                'code' => $case->code,
                'title' => $case->title,
                'description' => $case->description,
                'level' => $case->level,
                'role_title' => $case->role_title,
                'briefing_text' => $case->briefing_text,
                'estimated_time' => $case->estimated_time,
                'sort_order' => $case->sort_order,
            ],
            'scenarios' => $case->scenarios->map(function ($s) {
                return [
                    'step_code' => $s->step_code,
                    'scenario_type' => $s->scenario_type,
                    'is_start' => (bool) $s->is_start,
                    'is_ending' => (bool) $s->is_ending,
                    'decision_no' => $s->decision_no,
                    'title' => $s->title,
                    'story_text' => $s->story_text,
                    'customer_dialogue' => $s->customer_dialogue,
                    'new_problem_text' => $s->new_problem_text,
                    'max_investigations' => $s->max_investigations,
                    'sort_order' => $s->sort_order,
                    'investigation_items' => $s->investigationItems->map(fn ($it) => [
                        'title' => $it->title,
                        'type' => $it->type,
                        'content' => $it->content,
                        'is_relevant' => (bool) $it->is_relevant,
                        'sort_order' => $it->sort_order,
                    ]),
                    'hints' => $s->hints->map(fn ($h) => [
                        'level' => $h->level,
                        'hint_text' => $h->hint_text,
                    ]),
                    'options' => $s->options->map(fn ($o) => [
                        'label' => $o->label,
                        'option_text' => $o->option_text,
                        'consequence_text' => $o->consequence_text,
                        'consequence_type' => $o->consequence_type,
                        'outcome_tag' => $o->outcome_tag,
                        'next_step_code' => $o->nextScenario?->step_code,
                        'sort_order' => $o->sort_order,
                        'scores' => $o->indicatorScores->mapWithKeys(fn ($sc) => [
                            $sc->indicator->code => $sc->score,
                        ]),
                    ]),
                ];
            }),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'case_' . strtolower(str_replace([' ', '/'], '_', $case->code)) . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimes:json,txt', 'max:5120'],
        ]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $data = json_decode($content, true);

        if (! is_array($data) || empty($data['case']['title']) || ! isset($data['scenarios'])) {
            return back()->withErrors(['import' => 'Format file JSON tidak valid. Pastikan file berisi metadata kasus dan array skenario.']);
        }

        $caseMeta = $data['case'];
        $baseCode = strtoupper(trim($caseMeta['code'] ?? 'CASE-IMPORT'));
        $code = $baseCode;
        $counter = 1;
        while (CaseModel::where('code', $code)->exists()) {
            $code = $baseCode . '-IMP-' . $counter;
            $counter++;
        }

        $newCase = DB::transaction(function () use ($caseMeta, $code, $data) {
            $case = CaseModel::create([
                'code' => $code,
                'title' => $caseMeta['title'],
                'description' => $caseMeta['description'] ?? '',
                'level' => in_array($caseMeta['level'] ?? '', ['foundation', 'developing', 'challenge']) ? $caseMeta['level'] : 'foundation',
                'role_title' => $caseMeta['role_title'] ?? null,
                'briefing_text' => $caseMeta['briefing_text'] ?? null,
                'estimated_time' => (int) ($caseMeta['estimated_time'] ?? 15),
                'is_active' => false,
                'sort_order' => ((int) CaseModel::max('sort_order')) + 1,
            ]);

            $stepMap = []; // step_code -> new scenario

            // Pass 1: create scenarios, items, hints
            foreach ($data['scenarios'] as $sData) {
                $scenario = $case->scenarios()->create([
                    'step_code' => $sData['step_code'] ?? 'S1',
                    'scenario_type' => $sData['scenario_type'] ?? 'main',
                    'is_start' => (bool) ($sData['is_start'] ?? false),
                    'is_ending' => (bool) ($sData['is_ending'] ?? false),
                    'decision_no' => $sData['decision_no'] ?? null,
                    'title' => $sData['title'] ?? 'Skenario',
                    'story_text' => $sData['story_text'] ?? '',
                    'customer_dialogue' => $sData['customer_dialogue'] ?? null,
                    'new_problem_text' => $sData['new_problem_text'] ?? null,
                    'max_investigations' => (int) ($sData['max_investigations'] ?? 2),
                    'sort_order' => (int) ($sData['sort_order'] ?? 0),
                ]);
                $stepMap[$scenario->step_code] = $scenario;

                foreach ($sData['investigation_items'] ?? [] as $iData) {
                    $scenario->investigationItems()->create([
                        'title' => $iData['title'] ?? 'Item',
                        'type' => $iData['type'] ?? 'sop',
                        'content' => $iData['content'] ?? '',
                        'is_relevant' => (bool) ($iData['is_relevant'] ?? false),
                        'sort_order' => (int) ($iData['sort_order'] ?? 0),
                    ]);
                }

                foreach ($sData['hints'] ?? [] as $hData) {
                    $scenario->hints()->create([
                        'level' => (int) ($hData['level'] ?? 1),
                        'hint_text' => $hData['hint_text'] ?? '',
                    ]);
                }
            }

            // Pass 2: options with next_step_code remapped
            $indicators = \App\Models\Indicator::all()->keyBy('code');

            foreach ($data['scenarios'] as $sData) {
                $stepCode = $sData['step_code'] ?? null;
                if (! isset($stepMap[$stepCode])) {
                    continue;
                }
                $scenario = $stepMap[$stepCode];

                foreach ($sData['options'] ?? [] as $oData) {
                    $targetStep = $oData['next_step_code'] ?? null;
                    $nextScenarioId = $targetStep && isset($stepMap[$targetStep]) ? $stepMap[$targetStep]->id : null;

                    $option = $scenario->options()->create([
                        'label' => $oData['label'] ?? 'A',
                        'option_text' => $oData['option_text'] ?? '',
                        'consequence_text' => $oData['consequence_text'] ?? '',
                        'consequence_type' => $oData['consequence_type'] ?? 'neutral',
                        'outcome_tag' => $oData['outcome_tag'] ?? null,
                        'next_scenario_id' => $nextScenarioId,
                        'sort_order' => (int) ($oData['sort_order'] ?? 0),
                    ]);

                    foreach ($oData['scores'] ?? [] as $indCode => $score) {
                        if (isset($indicators[$indCode])) {
                            $option->indicatorScores()->create([
                                'indicator_id' => $indicators[$indCode]->id,
                                'score' => (int) $score,
                            ]);
                        }
                    }
                }
            }

            return $case;
        });

        return redirect()->route('admin.cases.edit', $newCase)
            ->with('status', "Kasus berhasil diimpor sebagai {$newCase->code}.");
    }
}
