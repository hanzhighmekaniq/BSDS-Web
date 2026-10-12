<?php

namespace App\Services;

use App\Models\FeedbackTemplate;
use App\Models\Indicator;
use App\Models\SessionIndicatorResult;
use App\Models\SimulationSession;
use Illuminate\Support\Collection;

/**
 * Menghitung profil performa per indikator saat sesi selesai.
 *
 * - Indikator dari opsi: raw = skor opsi terpilih, max = skor tertinggi opsi pada skenario tsb.
 * - Indikator INF (pengumpulan informasi): dari investigasi yang dibuka
 *   (relevan +1, tidak relevan -1, minimal 0 per skenario; max = min(batas, jumlah relevan)).
 */
class SimulationScorer
{
    public const INFORMATION_CODE = 'INF';

    public function score(SimulationSession $session): Collection
    {
        $session->load([
            'decisionLogs.option.indicatorScores',
            'decisionLogs.scenario.options.indicatorScores',
            'investigationViews.item',
        ]);

        $raw = [];
        $max = [];

        foreach ($session->decisionLogs as $log) {
            $scenarioOptions = $log->scenario->options;

            $indicatorIds = $scenarioOptions->flatMap(fn ($o) => $o->indicatorScores->pluck('indicator_id'))->unique();

            foreach ($indicatorIds as $indicatorId) {
                $max[$indicatorId] = ($max[$indicatorId] ?? 0)
                    + $scenarioOptions->map(fn ($o) => $o->indicatorScores->firstWhere('indicator_id', $indicatorId)?->score ?? 0)->max();

                $raw[$indicatorId] = ($raw[$indicatorId] ?? 0)
                    + ($log->option->indicatorScores->firstWhere('indicator_id', $indicatorId)?->score ?? 0);
            }
        }

        $information = Indicator::where('code', self::INFORMATION_CODE)->first();

        if ($information) {
            [$infRaw, $infMax] = $this->informationScore($session);
            if ($infMax > 0) {
                $raw[$information->id] = $infRaw;
                $max[$information->id] = $infMax;
            }
        }

        $results = collect();

        foreach ($max as $indicatorId => $maxScore) {
            if ($maxScore <= 0) {
                continue;
            }

            $results->push(SessionIndicatorResult::updateOrCreate(
                ['session_id' => $session->id, 'indicator_id' => $indicatorId],
                [
                    'raw_score' => $raw[$indicatorId] ?? 0,
                    'max_score' => $maxScore,
                    'category' => $this->category(($raw[$indicatorId] ?? 0) / $maxScore),
                ]
            ));
        }

        return $results;
    }

    /**
     * Ringkasan hasil akhir sesi berdasarkan total skor.
     */
    public function finalOutcome(Collection $results): string
    {
        $max = $results->sum('max_score');

        if ($max === 0) {
            return 'cukup';
        }

        return $this->category($results->sum('raw_score') / $max);
    }

    public function category(float $ratio): string
    {
        return match (true) {
            $ratio >= 0.75 => 'baik',
            $ratio >= 0.5 => 'cukup',
            default => 'perlu_latihan',
        };
    }

    /**
     * Umpan balik per indikator dari template.
     */
    public function feedbackFor(Collection $results): Collection
    {
        return $results->map(function (SessionIndicatorResult $result) {
            $template = FeedbackTemplate::where('indicator_id', $result->indicator_id)
                ->where('category', $result->category)
                ->first();

            return [
                'indicator' => $result->indicator,
                'result' => $result,
                'feedback' => $template?->feedback_text,
                'recommendation' => $template?->recommendation_text,
            ];
        });
    }

    /**
     * Kelompokkan umpan balik per indikator menjadi 3 kartu Analyze/Decide/Adapt
     * untuk halaman Feedback (Analyze = IDN+INF, Decide = ALT+ACT, Adapt = CON+ADP).
     */
    public function groupedFeedbackFor(Collection $feedback): array
    {
        $groups = [
            'analyze' => ['label' => 'Analyze', 'icon' => '🔎', 'codes' => ['IDN', 'INF']],
            'decide' => ['label' => 'Decide', 'icon' => '⚖️', 'codes' => ['ALT', 'ACT']],
            'adapt' => ['label' => 'Adapt', 'icon' => '🔄', 'codes' => ['CON', 'ADP']],
        ];

        return collect($groups)->map(function (array $group) use ($feedback) {
            $rows = $feedback->filter(fn (array $row) => in_array($row['indicator']->code, $group['codes'], true));

            $raw = $rows->sum(fn (array $row) => $row['result']->raw_score);
            $max = $rows->sum(fn (array $row) => $row['result']->max_score);

            return [
                'label' => $group['label'],
                'icon' => $group['icon'],
                'category' => $max > 0 ? $this->category($raw / $max) : 'cukup',
                'percent' => $max > 0 ? round($raw / $max * 100) : 0,
                'recommendations' => $rows->pluck('recommendation')->filter()->unique()->values()->all(),
            ];
        })->all();
    }

    /**
     * @return array{0:int,1:int}
     */
    protected function informationScore(SimulationSession $session): array
    {
        $raw = 0;
        $max = 0;

        $visitedScenarios = $session->decisionLogs->pluck('scenario')->unique('id');

        foreach ($visitedScenarios as $scenario) {
            $items = $scenario->investigationItems()->get();
            $relevantCount = $items->where('is_relevant', true)->count();

            if ($relevantCount === 0) {
                continue;
            }

            $views = $session->investigationViews->where('scenario_id', $scenario->id);
            $relevantViewed = $views->filter(fn ($v) => $v->item?->is_relevant)->count();
            $irrelevantViewed = $views->count() - $relevantViewed;

            $max += min($scenario->max_investigations, $relevantCount);
            $raw += max(0, $relevantViewed - $irrelevantViewed);
        }

        return [min($raw, $max), $max];
    }
}
