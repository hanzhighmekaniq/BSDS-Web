<?php

namespace App\Exports\Sheets;

use App\Models\SimulationSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Satu baris per sesi (format lebar, siap SPSS/SmartPLS):
 * identitas, mode, durasi, hasil akhir, rute, dan persentase tiap indikator.
 */
class RespondentsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(protected Collection $sessions, protected Collection $indicators) {}

    public function title(): string
    {
        return 'Responden';
    }

    public function headings(): array
    {
        return array_merge([
            'session_id', 'nis', 'nama', 'kelas', 'no_absen', 'kasus', 'mode', 'percobaan', 'status',
            'mulai', 'selesai', 'durasi_detik', 'hasil_akhir', 'jumlah_keputusan', 'konsekuensi_negatif',
            'jumlah_investigasi', 'jumlah_hint', 'rute',
        ], $this->indicators->map(fn ($i) => 'ind_'.$i->code.'_persen')->all());
    }

    public function collection(): Enumerable
    {
        return $this->sessions->map(function (SimulationSession $s) {
            $profile = $s->user?->studentProfile;

            $indicatorColumns = $this->indicators->map(function ($indicator) use ($s) {
                $result = $s->indicatorResults->firstWhere('indicator_id', $indicator->id);

                return $result && $result->max_score > 0 ? round($result->raw_score / $result->max_score * 100, 1) : null;
            })->all();

            return array_merge([
                $s->id,
                $profile?->nis,
                $s->user?->name,
                $profile?->classroom?->name,
                $profile?->absence_no,
                $s->case?->code,
                $s->mode,
                $s->attempt_no,
                $s->status,
                $s->started_at?->format('Y-m-d H:i:s'),
                $s->completed_at?->format('Y-m-d H:i:s'),
                $s->duration_seconds,
                $s->final_outcome,
                $s->decisionLogs->count(),
                $s->decisionLogs->where('consequence_type', 'negative')->count(),
                $s->investigationViews->count(),
                $s->hintUsages->count(),
                $s->decisionLogs->sortBy('step_order')->map(fn ($l) => $l->scenario?->step_code.':'.$l->option?->label)->implode(' > '),
            ], $indicatorColumns);
        });
    }
}
