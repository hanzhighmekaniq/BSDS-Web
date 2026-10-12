<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartSimulationRequest;
use App\Http\Requests\StoreDecisionRequest;
use App\Http\Requests\StoreReflectionRequest;
use App\Models\CaseModel;
use App\Models\DecisionLog;
use App\Models\Hint;
use App\Models\HintUsage;
use App\Models\InvestigationItem;
use App\Models\InvestigationView;
use App\Models\ScenarioOption;
use App\Models\SimulationSession;
use App\Services\SimulationScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(protected SimulationScorer $scorer) {}

    /**
     * Katalog kasus + progres siswa.
     */
    public function index(): View
    {
        $cases = CaseModel::where('is_active', true)
            ->withCount('scenarios')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $userId = auth()->id();

        $activeSessions = SimulationSession::where('user_id', $userId)
            ->where('status', 'in_progress')
            ->with(['case', 'currentScenario'])
            ->latest()
            ->get();

        $completedSessions = SimulationSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->with(['case', 'reflection'])
            ->latest()
            ->take(10)
            ->get();

        $completedCaseIds = SimulationSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('case_id')
            ->unique();

        return view('simulations.index', compact('cases', 'activeSessions', 'completedSessions', 'completedCaseIds'));
    }

    /**
     * Halaman briefing kasus sebelum masuk Situasi 1.
     */
    public function briefing(CaseModel $case): View
    {
        abort_unless($case->is_active, 404, 'Kasus simulasi tidak aktif.');

        $mode = request('mode') === 'assessment' ? 'assessment' : 'practice';

        return view('simulations.briefing', compact('case', 'mode'));
    }

    /**
     * Mulai sesi baru (mode practice/assessment). Satu sesi aktif per kasus.
     */
    public function start(StartSimulationRequest $request, CaseModel $case): RedirectResponse
    {
        abort_unless($case->is_active, 404, 'Kasus simulasi tidak aktif.');

        $initialScenario = $case->initialScenario();
        abort_unless($initialScenario, 422, 'Kasus belum memiliki skenario awal.');

        $existing = SimulationSession::where('user_id', auth()->id())
            ->where('case_id', $case->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return redirect()->route('simulations.show', $existing)
                ->with('status', 'Anda masih memiliki sesi yang sedang berjalan untuk kasus ini.');
        }

        $attempt = SimulationSession::where('user_id', auth()->id())
            ->where('case_id', $case->id)
            ->count() + 1;

        $session = SimulationSession::create([
            'user_id' => auth()->id(),
            'case_id' => $case->id,
            'mode' => $request->validated('mode') ?? 'practice',
            'attempt_no' => $attempt,
            'current_scenario_id' => $initialScenario->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        return redirect()->route('simulations.show', $session)
            ->with('status', 'Simulasi baru berhasil dimulai.');
    }

    /**
     * Tampilkan skenario aktif.
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
            'currentScenario.hints',
            'decisionLogs',
        ]);

        $scenario = $session->currentScenario;

        if (! $scenario || $scenario->is_ending) {
            return redirect()->route('simulations.journey', $session);
        }

        $viewedItems = InvestigationView::where('session_id', $session->id)
            ->where('scenario_id', $scenario->id)
            ->pluck('investigation_item_id')
            ->all();

        $usedHintLevels = HintUsage::where('session_id', $session->id)
            ->where('scenario_id', $scenario->id)
            ->with('hint')
            ->get()
            ->pluck('hint.level')
            ->all();

        // Waktu keputusan dihitung sejak skenario pertama kali ditampilkan.
        $presentedAt = $this->presentedAt($session);

        $totalDecisions = $session->case->scenarios()->where('scenario_type', 'main')->whereNotNull('decision_no')->count();

        return view('simulations.show', compact('session', 'scenario', 'viewedItems', 'usedHintLevels', 'presentedAt', 'totalDecisions'));
    }

    /**
     * Catat investigasi yang dibuka (maks. max_investigations per skenario).
     */
    public function investigate(SimulationSession $session, InvestigationItem $item): JsonResponse
    {
        $this->authorizeSessionOwner($session);
        $this->ensureActive($session);

        abort_unless($item->scenario_id === $session->current_scenario_id, 403, 'Item bukan bagian dari skenario aktif.');

        $already = InvestigationView::where('session_id', $session->id)
            ->where('investigation_item_id', $item->id)
            ->exists();

        if (! $already) {
            $count = InvestigationView::where('session_id', $session->id)
                ->where('scenario_id', $item->scenario_id)
                ->count();

            if ($count >= $item->scenario->max_investigations) {
                return response()->json([
                    'message' => "Anda hanya dapat memeriksa maksimal {$item->scenario->max_investigations} informasi pada situasi ini.",
                ], 422);
            }

            InvestigationView::create([
                'session_id' => $session->id,
                'scenario_id' => $item->scenario_id,
                'investigation_item_id' => $item->id,
                'viewed_at' => now(),
            ]);
        }

        $this->touch($session);

        return response()->json(['title' => $item->title, 'content' => $item->content]);
    }

    /**
     * Buka hint berikutnya (hanya Practice Mode). Hint diberikan berurutan.
     */
    public function hint(SimulationSession $session): JsonResponse
    {
        $this->authorizeSessionOwner($session);
        $this->ensureActive($session);

        abort_unless($session->isPractice(), 403, 'Hint tidak tersedia pada Assessment Mode.');

        $usedHintIds = HintUsage::where('session_id', $session->id)
            ->where('scenario_id', $session->current_scenario_id)
            ->pluck('hint_id');

        $next = Hint::where('scenario_id', $session->current_scenario_id)
            ->whereNotIn('id', $usedHintIds)
            ->orderBy('level')
            ->first();

        if (! $next) {
            return response()->json(['message' => 'Tidak ada hint lagi untuk situasi ini.'], 404);
        }

        HintUsage::create([
            'session_id' => $session->id,
            'scenario_id' => $session->current_scenario_id,
            'hint_id' => $next->id,
            'used_at' => now(),
        ]);

        $this->touch($session);

        return response()->json(['level' => $next->level, 'text' => $next->hint_text]);
    }

    /**
     * Simpan keputusan, maju ke skenario berikutnya, selesaikan sesi bila ending.
     */
    public function submitDecision(StoreDecisionRequest $request, SimulationSession $session): RedirectResponse
    {
        $this->authorizeSessionOwner($session);
        $this->ensureActive($session);

        $option = ScenarioOption::where('scenario_id', $session->current_scenario_id)
            ->with('nextScenario')
            ->findOrFail($request->validated('option_id'));

        $presentedAt = $this->presentedAt($session);
        $answeredAt = now();

        DecisionLog::create([
            'session_id' => $session->id,
            'step_order' => $session->decisionLogs()->count() + 1,
            'scenario_id' => $session->current_scenario_id,
            'option_id' => $option->id,
            'confidence_level' => $request->validated('confidence_level'),
            'presented_at' => $presentedAt,
            'answered_at' => $answeredAt,
            'time_spent_seconds' => max(0, $presentedAt->diffInSeconds($answeredAt)),
            'consequence_type' => $option->consequence_type,
        ]);

        $next = $option->nextScenario;

        $session->update([
            'current_scenario_id' => $next?->id,
            'last_activity_at' => $answeredAt,
        ]);

        if (! $next || $next->is_ending) {
            $this->completeSession($session->fresh());
        }

        return redirect()->route('simulations.consequence', $session);
    }

    /**
     * Halaman konsekuensi dari keputusan terakhir.
     */
    public function consequence(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        $log = $session->decisionLogs()->with(['option', 'scenario'])->latest('step_order')->first();

        if (! $log) {
            return redirect()->route('simulations.show', $session);
        }

        $session->load('currentScenario');

        return view('simulations.consequence', [
            'session' => $session,
            'log' => $log,
            'next' => $session->currentScenario,
        ]);
    }

    /**
     * Form refleksi (hanya sesi selesai, sekali saja).
     */
    public function reflection(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->status !== 'completed') {
            return redirect()->route('simulations.show', $session);
        }

        if ($session->reflection()->exists()) {
            return redirect()->route('simulations.journey', $session);
        }

        $session->load('case');

        return view('simulations.reflection', compact('session'));
    }

    public function storeReflection(StoreReflectionRequest $request, SimulationSession $session): RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        abort_unless($session->status === 'completed', 403, 'Selesaikan simulasi sebelum mengisi refleksi.');

        if ($session->reflection()->exists()) {
            return redirect()->route('simulations.feedback', $session);
        }

        $session->reflection()->create($request->validated());

        return redirect()->route('simulations.feedback', $session)
            ->with('status', 'Refleksi berhasil disimpan.');
    }

    /**
     * Halaman Feedback: 3 kartu Analyze/Decide/Adapt + rekomendasi latihan.
     */
    public function feedback(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->status !== 'completed') {
            return redirect()->route('simulations.show', $session);
        }

        if (! $session->reflection()->exists()) {
            return redirect()->route('simulations.reflection', $session);
        }

        $session->load(['case', 'indicatorResults.indicator']);

        $feedback = $this->scorer->feedbackFor($session->indicatorResults->sortBy('indicator.sort_order'));
        $groups = $this->scorer->groupedFeedbackFor($feedback);

        return view('simulations.feedback', compact('session', 'groups'));
    }

    /**
     * Hasil akhir: profil performa, decision journey, what-if, feedback.
     */
    public function journey(SimulationSession $session): View|RedirectResponse
    {
        $this->authorizeSessionOwner($session);

        if ($session->status !== 'completed') {
            return redirect()->route('simulations.show', $session);
        }

        $session->load([
            'case',
            'currentScenario',
            'decisionLogs.scenario',
            'decisionLogs.option',
            'indicatorResults.indicator',
            'investigationViews.item',
            'reflection',
        ]);

        $feedback = $this->scorer->feedbackFor($session->indicatorResults->sortBy('indicator.sort_order'));

        $whatIf = null;
        if ($session->isPractice()) {
            $whatIf = $this->whatIf($session);
        }

        $stats = [
            'decisions' => $session->decisionLogs->count(),
            'negative' => $session->decisionLogs->where('consequence_type', 'negative')->count(),
            'investigations' => $session->investigationViews->count(),
            'hints' => $session->hintUsages()->count(),
        ];

        return view('simulations.journey', compact('session', 'feedback', 'whatIf', 'stats'));
    }

    /**
     * What-If: pilih satu keputusan siswa (prioritas yang negatif) dan tampilkan opsi lain.
     */
    protected function whatIf(SimulationSession $session): ?array
    {
        $logs = $session->decisionLogs->filter(fn ($l) => $l->scenario?->options()->count() > 1);

        $log = $logs->firstWhere('consequence_type', 'negative') ?? $logs->sortByDesc('step_order')->first();

        if (! $log) {
            return null;
        }

        $alternatives = $log->scenario->options()
            ->where('id', '!=', $log->option_id)
            ->orderBy('sort_order')
            ->get();

        return ['log' => $log, 'alternatives' => $alternatives];
    }

    protected function completeSession(SimulationSession $session): void
    {
        $results = $this->scorer->score($session);

        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => max(0, $session->started_at?->diffInSeconds(now()) ?? 0),
            'final_outcome' => $this->scorer->finalOutcome($results),
        ]);
    }

    protected function presentedAt(SimulationSession $session): Carbon
    {
        $lastLog = $session->decisionLogs()->latest('step_order')->first();

        return $lastLog?->answered_at ?? $session->started_at ?? now();
    }

    protected function touch(SimulationSession $session): void
    {
        $session->update(['last_activity_at' => now()]);
    }

    protected function ensureActive(SimulationSession $session): void
    {
        abort_if($session->status === 'completed', 400, 'Sesi simulasi sudah selesai.');
    }

    /**
     * Pastikan siswa adalah pemilik sesi.
     */
    protected function authorizeSessionOwner(SimulationSession $session): void
    {
        abort_unless($session->user_id === auth()->id(), 403, 'Akses tidak diizinkan untuk sesi ini.');
    }
}
