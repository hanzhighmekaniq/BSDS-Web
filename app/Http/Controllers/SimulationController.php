<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\DecisionLog;
use App\Models\ScenarioOption;
use App\Models\SimulationSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    /**
     * Display a listing of available simulation cases for students.
     */
    public function index(): View
    {
        $cases = CaseModel::where('is_active', true)
            ->withCount('scenarios')
            ->latest()
            ->get();

        $activeSessions = SimulationSession::where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->with(['case', 'currentScenario'])
            ->latest()
            ->get();

        $completedSessions = SimulationSession::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->with(['case', 'reflection'])
            ->latest()
            ->take(5)
            ->get();

        return view('simulations.index', compact('cases', 'activeSessions', 'completedSessions'));
    }

    /**
     * Start a new simulation session for the chosen case.
     */
    public function start(CaseModel $case): RedirectResponse
    {
        abort_unless($case->is_active, 404, 'Kasus simulasi tidak aktif.');

        $initialScenario = $case->initialScenario();
        abort_unless($initialScenario, 422, 'Kasus belum memiliki skenario awal.');

        $session = SimulationSession::create([
            'user_id' => auth()->id(),
            'case_id' => $case->id,
            'current_scenario_id' => $initialScenario->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('simulations.show', $session)
            ->with('status', 'Simulasi baru berhasil dimulai.');
    }

    /**
     * Display active scenario, options, and investigation items.
     */
    public function show(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->status === 'completed') {
            return redirect()->route('simulations.journey', $session);
        }

        $session->load([
            'case',
            'currentScenario.options',
            'currentScenario.investigationItems',
            'decisionLogs.option',
        ]);

        $scenario = $session->currentScenario;

        // If scenario reached ending with no options left, prompt reflection
        if (! $scenario) {
            return redirect()->route('simulations.reflection', $session);
        }

        return view('simulations.show', compact('session', 'scenario'));
    }

    /**
     * Record decision, viewed investigations, and advance scenario.
     */
    public function submitDecision(Request $request, SimulationSession $session): RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        abort_if($session->status === 'completed', 400, 'Sesi simulasi sudah selesai.');

        $validated = $request->validate([
            'option_id' => ['required', 'exists:scenario_options,id'],
            'confidence_level' => ['required', 'string', 'in:unsure,fairly_confident,confident,very_confident'],
            'investigations_viewed' => ['nullable', 'array'],
            'investigations_viewed.*' => ['string'],
        ]);

        $option = ScenarioOption::where('scenario_id', $session->current_scenario_id)
            ->findOrFail($validated['option_id']);

        DecisionLog::create([
            'session_id' => $session->id,
            'scenario_id' => $session->current_scenario_id,
            'option_id' => $option->id,
            'confidence_level' => $validated['confidence_level'],
            'investigations_viewed' => $validated['investigations_viewed'] ?? [],
        ]);

        // Branching step
        $nextScenario = $option->nextScenario;

        if (! $nextScenario) {
            // Reached branch termination
            return redirect()->route('simulations.reflection', $session)
                ->with('consequence', $option->consequence_text);
        }

        $session->update([
            'current_scenario_id' => $nextScenario->id,
        ]);

        return redirect()->route('simulations.show', $session)
            ->with('consequence', $option->consequence_text);
    }

    /**
     * Show the 3-question reflection form upon simulation completion.
     */
    public function reflection(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->reflection()->exists()) {
            return redirect()->route('simulations.journey', $session);
        }

        $session->load(['case', 'currentScenario', 'decisionLogs.option']);

        return view('simulations.reflection', compact('session'));
    }

    /**
     * Store reflection answers and mark session as completed.
     */
    public function storeReflection(Request $request, SimulationSession $session): RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->reflection()->exists()) {
            return redirect()->route('simulations.journey', $session);
        }

        $validated = $request->validate([
            'q1_reason' => ['required', 'string', 'min:5'],
            'q2_unexpected_consequence' => ['required', 'string', 'min:5'],
            'q3_future_action' => ['required', 'string', 'min:5'],
        ]);

        $session->reflection()->create($validated);

        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('simulations.journey', $session)
            ->with('status', 'Refleksi berhasil disimpan. Simulasi telah selesai!');
    }

    /**
     * Display decision journey history and consequence reflection.
     */
    public function journey(SimulationSession $session): View
    {
        $this->authorizeSessionOwner($session);

        $session->load([
            'case',
            'currentScenario',
            'decisionLogs.scenario',
            'decisionLogs.option',
            'reflection',
        ]);

        return view('simulations.journey', compact('session'));
    }

    /**
     * Guard to ensure user owns the simulation session.
     */
    protected function authorizeSessionOwner(SimulationSession $session): void
    {
        abort_unless($session->user_id === auth()->id(), 403, 'Akses tidak diizinkan untuk sesi ini.');
    }
}
