<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Format panjang: satu baris per indikator per sesi.
 */
class IndicatorsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(protected Collection $sessions) {}

    public function title(): string
    {
        return 'Indikator';
    }

    public function headings(): array
    {
        return ['session_id', 'nis', 'kode_indikator', 'indikator', 'skor_mentah', 'skor_maks', 'persen', 'kategori'];
    }

    public function collection(): Enumerable
    {
        $rows = collect();

        foreach ($this->sessions as $session) {
            $nis = $session->user?->studentProfile?->nis;

            foreach ($session->indicatorResults->sortBy('indicator.sort_order') as $result) {
                $rows->push([
                    $session->id,
                    $nis,
                    $result->indicator?->code,
                    $result->indicator?->name,
                    $result->raw_score,
                    $result->max_score,
                    $result->max_score > 0 ? round($result->raw_score / $result->max_score * 100, 1) : null,
                    $result->category,
                ]);
            }
        }

        return $rows;
    }
}
