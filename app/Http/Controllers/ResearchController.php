<?php

namespace App\Http\Controllers;

use App\Exports\SimulationResultExport;
use App\Http\Requests\ResearchFilterRequest;
use App\Models\CaseModel;
use App\Models\Classroom;
use App\Models\Indicator;
use App\Models\SessionIndicatorResult;
use App\Models\SimulationSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResearchController extends Controller
{
    /**
     * Rekap responden + ringkasan statistik (filter kelas/kasus/mode/status).
     */
    public function index(ResearchFilterRequest $request): View
    {
        $filters = $request->validated();
        $base = $this->filteredSessions($filters);

        $sessions = (clone $base)
            ->with(['user.studentProfile.classroom', 'case', 'decisionLogs.scenario', 'decisionLogs.option'])
            ->withCount(['hintUsages', 'investigationViews'])
            ->latest('started_at')
            ->paginate(25)
            ->withQueryString();

        $completed = (clone $base)->where('status', 'completed');

        $summary = [
            'total' => (clone $base)->count(),
            'completed' => (clone $completed)->count(),
            'avg_duration' => (int) round((clone $completed)->avg('duration_seconds') ?? 0),
            'outcomes' => (clone $completed)->select('final_outcome', DB::raw('count(*) as total'))
                ->groupBy('final_outcome')->pluck('total', 'final_outcome'),
        ];

        $indicatorAverages = SessionIndicatorResult::query()
            ->whereIn('session_id', (clone $base)->select('simulation_sessions.id'))
            ->select('indicator_id', DB::raw('SUM(raw_score) as raw'), DB::raw('SUM(max_score) as max'))
            ->groupBy('indicator_id')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->indicator_id => $row->max > 0 ? round($row->raw / $row->max * 100) : 0]);

        return view('research.index', [
            'sessions' => $sessions,
            'summary' => $summary,
            'indicators' => Indicator::orderBy('sort_order')->get(),
            'indicatorAverages' => $indicatorAverages,
            'classrooms' => Classroom::orderBy('name')->get(),
            'cases' => CaseModel::orderBy('sort_order')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Detail satu sesi: rute, indikator, refleksi.
     */
    public function show(SimulationSession $session): View
    {
        $session->load([
            'user.studentProfile.classroom',
            'case',
            'decisionLogs.scenario',
            'decisionLogs.option',
            'hintUsages.hint',
            'investigationViews.item',
            'indicatorResults.indicator',
            'reflection',
            'currentScenario',
        ]);

        return view('research.show', compact('session'));
    }

    /**
     * Unduh data mentah (.xlsx) sesuai filter aktif.
     */
    public function export(ResearchFilterRequest $request): BinaryFileResponse
    {
        $sessions = $this->filteredSessions($request->validated())
            ->with(SimulationResultExport::relations())
            ->orderBy('id')
            ->get();

        return Excel::download(
            new SimulationResultExport($sessions),
            'bsds-hasil-simulasi-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    protected function filteredSessions(array $filters): Builder
    {
        return SimulationSession::query()
            ->when($filters['case_id'] ?? null, fn ($q, $v) => $q->where('case_id', $v))
            ->when($filters['mode'] ?? null, fn ($q, $v) => $q->where('mode', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['classroom_id'] ?? null, fn ($q, $v) => $q->whereHas(
                'user.studentProfile',
                fn ($p) => $p->where('classroom_id', $v)
            ));
    }
}
