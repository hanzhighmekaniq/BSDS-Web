<?php

namespace App\Exports\Sheets;

use App\Exports\SimulationResultExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Format panjang: satu baris per keputusan.
 */
class DecisionsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(protected Collection $sessions) {}

    public function title(): string
    {
        return 'Keputusan';
    }

    public function headings(): array
    {
        return [
            'session_id', 'nis', 'urutan', 'skenario', 'judul_skenario', 'opsi', 'teks_opsi', 'jenis_konsekuensi',
            'keyakinan', 'keyakinan_skor', 'waktu_detik', 'ditampilkan', 'dijawab', 'hint_dipakai', 'informasi_dibuka',
        ];
    }

    public function collection(): Enumerable
    {
        $rows = collect();

        foreach ($this->sessions as $session) {
            $nis = $session->user?->studentProfile?->nis;

            foreach ($session->decisionLogs->sortBy('step_order') as $log) {
                $viewed = $session->investigationViews
                    ->where('scenario_id', $log->scenario_id)
                    ->map(fn ($v) => $v->item?->title)
                    ->filter()
                    ->implode('; ');

                $rows->push([
                    $session->id,
                    $nis,
                    $log->step_order,
                    $log->scenario?->step_code,
                    $log->scenario?->title,
                    $log->option?->label,
                    $log->option?->option_text,
                    $log->consequence_type,
                    $log->confidence_level,
                    SimulationResultExport::CONFIDENCE_SCORE[$log->confidence_level] ?? null,
                    $log->time_spent_seconds,
                    $log->presented_at?->format('Y-m-d H:i:s'),
                    $log->answered_at?->format('Y-m-d H:i:s'),
                    $session->hintUsages->where('scenario_id', $log->scenario_id)->count(),
                    $viewed,
                ]);
            }
        }

        return $rows;
    }
}
