<?php

namespace App\Exports;

use App\Exports\Sheets\DecisionsSheet;
use App\Exports\Sheets\IndicatorsSheet;
use App\Exports\Sheets\ReflectionsSheet;
use App\Exports\Sheets\RespondentsSheet;
use App\Models\Indicator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Ekspor data penelitian: 4 sheet (Responden, Keputusan, Indikator, Refleksi).
 * Sesi harus sudah di-eager-load lewat SimulationResultExport::relations().
 */
class SimulationResultExport implements Export, WithMultipleSheets
{
    public const CONFIDENCE_SCORE = [
        'very_unsure' => 1,
        'unsure' => 2,
        'fairly_confident' => 3,
        'confident' => 4,
        'very_confident' => 5,
    ];

    public function __construct(protected Collection $sessions) {}

    /**
     * Relasi yang dibutuhkan sheet-sheet ekspor.
     *
     * @return array<int,string>
     */
    public static function relations(): array
    {
        return [
            'user.studentProfile.classroom',
            'case',
            'decisionLogs.scenario.hints',
            'decisionLogs.option',
            'hintUsages.hint',
            'investigationViews.item',
            'indicatorResults.indicator',
            'reflection',
        ];
    }

    public function sheets(): array
    {
        $indicators = Indicator::orderBy('sort_order')->get();

        return [
            new RespondentsSheet($this->sessions, $indicators),
            new DecisionsSheet($this->sessions),
            new IndicatorsSheet($this->sessions),
            new ReflectionsSheet($this->sessions),
        ];
    }
}
