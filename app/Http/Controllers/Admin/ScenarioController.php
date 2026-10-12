<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScenarioRequest;
use App\Models\CaseModel;
use App\Models\Indicator;
use App\Models\Scenario;
use App\Services\SimulationScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function create(CaseModel $case): View
    {
        return $this->form($case, new Scenario(['scenario_type' => 'main', 'max_investigations' => 2]));
    }

    public function store(ScenarioRequest $request, CaseModel $case): RedirectResponse
    {
        $skipped = [];
        $scenario = DB::transaction(function () use ($request, $case, &$skipped) {
            $scenario = $case->scenarios()->create($this->scenarioData($request));
            $skipped = $this->syncChildren($scenario, $request);
            $this->enforceSingleStart($case, $scenario);

            return $scenario;
        });

        $message = 'Skenario berhasil dibuat.';
        if (! empty($skipped)) {
            $message .= ' Catatan: ' . implode(', ', $skipped) . ' diabaikan karena belum lengkap.';
        }

        return redirect()->route('admin.cases.scenarios.edit', [$case, $scenario])
            ->with('status', $message);
    }

    public function edit(CaseModel $case, Scenario $scenario): View
    {
        abort_unless($scenario->case_id === $case->id, 404);
        $scenario->load(['options.indicatorScores', 'investigationItems', 'hints']);

        return $this->form($case, $scenario);
    }

    public function update(ScenarioRequest $request, CaseModel $case, Scenario $scenario): RedirectResponse
    {
        abort_unless($scenario->case_id === $case->id, 404);

        $skipped = [];
        DB::transaction(function () use ($request, $case, $scenario, &$skipped) {
            $scenario->update($this->scenarioData($request));
            $skipped = $this->syncChildren($scenario, $request);
            $this->enforceSingleStart($case, $scenario);
        });

        $message = 'Skenario berhasil diperbarui.';
        if (! empty($skipped)) {
            $message .= ' Catatan: ' . implode(', ', $skipped) . ' diabaikan karena belum lengkap.';
        }

        return redirect()->route('admin.cases.edit', $case)
            ->with('status', $message);
    }

    public function destroy(CaseModel $case, Scenario $scenario): RedirectResponse
    {
        abort_unless($scenario->case_id === $case->id, 404);

        if ($scenario->decisionLogs()->exists()) {
            return back()->withErrors([
                'delete' => 'Skenario ini sudah memiliki data keputusan siswa (sesi nyata) dan tidak dapat dihapus demi menjaga integritas data penelitian.',
            ]);
        }

        $scenario->delete();

        return redirect()->route('admin.cases.edit', $case)
            ->with('status', 'Skenario berhasil dihapus.');
    }

    protected function form(CaseModel $case, Scenario $scenario): View
    {
        return view('admin.scenarios.form', [
            'case' => $case,
            'scenario' => $scenario,
            'siblingScenarios' => $case->scenarios()->orderBy('sort_order')->get(['id', 'step_code', 'title']),
            'indicators' => Indicator::where('is_active', true)
                ->where('code', '!=', SimulationScorer::INFORMATION_CODE)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    protected function scenarioData(ScenarioRequest $request): array
    {
        return $request->safe()->only([
            'step_code', 'scenario_type', 'is_start', 'is_ending', 'decision_no',
            'title', 'story_text', 'customer_dialogue', 'new_problem_text',
            'max_investigations', 'sort_order',
        ]);
    }

    protected function enforceSingleStart(CaseModel $case, Scenario $scenario): void
    {
        if ($scenario->is_start) {
            $case->scenarios()->where('id', '!=', $scenario->id)->update(['is_start' => false]);
        }
    }

    /**
     * Sinkronkan opsi, item investigasi, dan hint: update yang punya id,
     * buat baru yang tidak punya id (dan punya isi), hapus yang tidak lagi dikirim.
     * Baris kosong (tanpa field inti terisi) diabaikan, dan dihitung untuk notifikasi feedback.
     */
    protected function syncChildren(Scenario $scenario, ScenarioRequest $request): array
    {
        $rawOptions = $request->validated('options') ?? [];
        $rawItems = $request->validated('investigation_items') ?? [];
        $rawHints = $request->validated('hints') ?? [];

        $skipped = [];

        // Opsi dihapus semua jika skenario ini ending (ending tidak boleh punya opsi).
        if ($scenario->is_ending) {
            $incomingOptions = [];
        } else {
            $incomingOptions = [];
            $skippedOptions = 0;
            foreach ($rawOptions as $o) {
                if (filled($o['label'] ?? null) && filled($o['option_text'] ?? null)) {
                    $incomingOptions[] = $o;
                } elseif (filled($o['label'] ?? null) || filled($o['option_text'] ?? null) || filled($o['consequence_text'] ?? null)) {
                    $skippedOptions++;
                }
            }
            if ($skippedOptions > 0) {
                $skipped[] = "{$skippedOptions} opsi";
            }
        }

        $keepIds = [];
        foreach (array_values($incomingOptions) as $i => $data) {
            if (! empty($data['id'])) {
                $option = $scenario->options()->findOrFail($data['id']);
                $option->update($this->optionData($data, $i));
            } else {
                $option = $scenario->options()->create($this->optionData($data, $i));
            }
            $keepIds[] = $option->id;

            $scores = array_filter($data['scores'] ?? [], fn ($v) => $v !== null && $v !== '');
            $option->indicatorScores()->whereNotIn('indicator_id', array_keys($scores))->delete();
            foreach ($scores as $indicatorId => $score) {
                $option->indicatorScores()->updateOrCreate(
                    ['indicator_id' => $indicatorId],
                    ['score' => (int) $score]
                );
            }
        }
        $scenario->options()->whereNotIn('id', $keepIds ?: [0])->delete();

        // Item investigasi
        $incomingItems = [];
        $skippedItems = 0;
        foreach ($rawItems as $it) {
            if (filled($it['title'] ?? null) && filled($it['content'] ?? null)) {
                $incomingItems[] = $it;
            } elseif (filled($it['title'] ?? null) || filled($it['content'] ?? null)) {
                $skippedItems++;
            }
        }
        if ($skippedItems > 0) {
            $skipped[] = "{$skippedItems} item investigasi";
        }

        $keepItemIds = [];
        foreach (array_values($incomingItems) as $i => $data) {
            if (! empty($data['id'])) {
                $item = $scenario->investigationItems()->findOrFail($data['id']);
                $item->update($this->itemData($data, $i));
            } else {
                $item = $scenario->investigationItems()->create($this->itemData($data, $i));
            }
            $keepItemIds[] = $item->id;
        }
        $scenario->investigationItems()->whereNotIn('id', $keepItemIds ?: [0])->delete();

        // Hint
        $incomingHints = [];
        $skippedHints = 0;
        foreach ($rawHints as $h) {
            if (filled($h['hint_text'] ?? null) && filled($h['level'] ?? null)) {
                $incomingHints[] = $h;
            } elseif (filled($h['hint_text'] ?? null)) {
                $skippedHints++;
            }
        }
        if ($skippedHints > 0) {
            $skipped[] = "{$skippedHints} hint";
        }

        $keepHintIds = [];
        foreach (array_values($incomingHints) as $data) {
            if (! empty($data['id'])) {
                $hint = $scenario->hints()->findOrFail($data['id']);
                $hint->update(['level' => $data['level'], 'hint_text' => $data['hint_text']]);
            } else {
                $hint = $scenario->hints()->updateOrCreate(
                    ['level' => $data['level']],
                    ['hint_text' => $data['hint_text']]
                );
            }
            $keepHintIds[] = $hint->id;
        }
        $scenario->hints()->whereNotIn('id', $keepHintIds ?: [0])->delete();

        return $skipped;
    }

    protected function optionData(array $data, int $index): array
    {
        return [
            'label' => $data['label'],
            'option_text' => $data['option_text'],
            'consequence_text' => $data['consequence_text'] ?? '',
            'consequence_type' => $data['consequence_type'] ?? 'neutral',
            'outcome_tag' => ($data['outcome_tag'] ?? null) ?: null,
            'next_scenario_id' => ($data['next_scenario_id'] ?? null) ?: null,
            'sort_order' => $data['sort_order'] ?? $index,
        ];
    }

    protected function itemData(array $data, int $index): array
    {
        return [
            'title' => $data['title'],
            'type' => ($data['type'] ?? null) ?: 'sop',
            'content' => $data['content'],
            'is_relevant' => (bool) ($data['is_relevant'] ?? false),
            'sort_order' => $data['sort_order'] ?? $index,
        ];
    }
}
