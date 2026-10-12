<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReflectionsSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(protected Collection $sessions) {}

    public function title(): string
    {
        return 'Refleksi';
    }

    public function headings(): array
    {
        return ['session_id', 'nis', 'nama', 'q1_alasan_keputusan', 'q2_keputusan_tersulit', 'q3_konsekuensi_tak_terduga', 'q4_tindakan_ke_depan'];
    }

    public function collection(): Enumerable
    {
        return $this->sessions
            ->filter(fn ($s) => $s->reflection)
            ->map(fn ($s) => [
                $s->id,
                $s->user?->studentProfile?->nis,
                $s->user?->name,
                self::safe($s->reflection->q1_reason),
                self::safe($s->reflection->q2_hardest_decision),
                self::safe($s->reflection->q3_unexpected_consequence),
                self::safe($s->reflection->q4_future_action),
            ])
            ->values();
    }

    /**
     * Cegah formula injection saat sel dibuka di Excel (=, +, -, @ di awal teks).
     */
    public static function safe(?string $value): ?string
    {
        if ($value !== null && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
