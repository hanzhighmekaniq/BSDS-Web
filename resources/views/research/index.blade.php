@php
    $statusLabel = ['in_progress' => 'Berjalan', 'completed' => 'Selesai', 'abandoned' => 'Ditinggalkan'];
    $outcomeLabel = ['baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_latihan' => 'Perlu latihan'];
    $fmt = fn ($s) => $s ? sprintf('%d:%02d', intdiv($s, 60), $s % 60) : '-';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Analitik Penelitian</h2>
                <p class="text-sm text-bsds-gray mt-1">Rekap responden, rute keputusan, dan skor indikator.</p>
            </div>
            <a href="{{ route('research.export', $filters) }}" class="min-h-[44px] inline-flex items-center px-5 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white font-semibold text-sm transition">
                Ekspor .xlsx
            </a>
        </div>
    </x-slot>

    <div class="w-full space-y-6">

            {{-- Filter --}}
            <form method="GET" class="bg-white rounded-2xl border border-bsds-border p-4 grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                @foreach ([
                    ['classroom_id', 'Kelas', $classrooms->pluck('name', 'id')],
                    ['case_id', 'Kasus', $cases->pluck('code', 'id')],
                    ['mode', 'Mode', collect(['practice' => 'Practice', 'assessment' => 'Assessment'])],
                    ['status', 'Status', collect($statusLabel)],
                ] as [$name, $label, $options])
                    <label class="text-xs font-semibold text-bsds-gray">
                        {{ $label }}
                        <select name="{{ $name }}" class="mt-1 w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal">
                            <option value="">Semua</option>
                            @foreach ($options as $value => $text)
                                <option value="{{ $value }}" @selected(($filters[$name] ?? null) == $value)>{{ $text }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
                <div class="flex gap-2">
                    <button class="min-h-[40px] px-4 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition">Terapkan</button>
                    <a href="{{ route('research.index') }}" class="min-h-[40px] inline-flex items-center px-3 text-sm text-bsds-gray hover:underline">Reset</a>
                </div>
            </form>

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                    <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $summary['total'] }}</div>
                    <div class="text-xs text-bsds-gray mt-1">Total sesi</div>
                </div>
                <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                    <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $summary['completed'] }}</div>
                    <div class="text-xs text-bsds-gray mt-1">Selesai</div>
                </div>
                <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                    <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $fmt($summary['avg_duration']) }}</div>
                    <div class="text-xs text-bsds-gray mt-1">Rata-rata durasi (mnt:dtk)</div>
                </div>
                <div class="bg-white rounded-2xl border border-bsds-border p-4">
                    <div class="text-xs text-bsds-gray mb-1">Hasil akhir</div>
                    @forelse ($summary['outcomes'] as $outcome => $total)
                        <div class="text-xs text-bsds-ink flex justify-between"><span>{{ $outcomeLabel[$outcome] ?? $outcome }}</span><span class="font-semibold">{{ $total }}</span></div>
                    @empty
                        <div class="text-xs text-bsds-gray">-</div>
                    @endforelse
                </div>
            </div>

            {{-- Rata-rata indikator --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-6">
                <h3 class="font-heading font-semibold text-bsds-navy mb-4">Rata-rata skor indikator <span class="text-xs font-normal text-bsds-gray">(indikator sementara)</span></h3>
                <div class="space-y-3">
                    @foreach ($indicators as $indicator)
                        @php $avg = $indicatorAverages[$indicator->id] ?? null; @endphp
                        <div>
                            <div class="flex justify-between text-sm"><span class="text-bsds-ink">{{ $indicator->name }}</span><span class="font-semibold text-bsds-navy">{{ $avg !== null ? $avg.'%' : '-' }}</span></div>
                            <div class="h-2 bg-bsds-border rounded-full mt-1 overflow-hidden"><div class="h-2 rounded-full bg-bsds-teal" style="width: {{ $avg ?? 0 }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tabel responden --}}
            <div class="bg-white rounded-2xl border border-bsds-border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-bsds-bg text-xs uppercase tracking-wider text-bsds-gray">
                        <tr>
                            <th class="px-4 py-3 text-left">Responden</th>
                            <th class="px-4 py-3 text-left">Kelas</th>
                            <th class="px-4 py-3 text-left">Kasus / Mode</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Durasi</th>
                            <th class="px-4 py-3 text-left">Rute</th>
                            <th class="px-4 py-3 text-left">Info/Hint</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bsds-border">
                        @forelse ($sessions as $s)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-bsds-ink">{{ $s->user?->name }}</div>
                                    <div class="text-xs text-bsds-gray">{{ $s->user?->studentProfile?->nis }}</div>
                                </td>
                                <td class="px-4 py-3 text-bsds-ink">{{ $s->user?->studentProfile?->classroom?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-bsds-ink">{{ $s->case?->code }}</div>
                                    <div class="text-xs text-bsds-gray">{{ $s->mode }} &bull; ke-{{ $s->attempt_no }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold">{{ $statusLabel[$s->status] ?? $s->status }}</span>
                                    @if ($s->final_outcome)<div class="text-xs text-bsds-gray">{{ $outcomeLabel[$s->final_outcome] ?? $s->final_outcome }}</div>@endif
                                </td>
                                <td class="px-4 py-3 text-bsds-ink">{{ $fmt($s->duration_seconds) }}</td>
                                <td class="px-4 py-3 text-xs text-bsds-gray">{{ $s->decisionLogs->sortBy('step_order')->map(fn ($l) => $l->scenario?->step_code.':'.$l->option?->label)->implode(' › ') ?: '-' }}</td>
                                <td class="px-4 py-3 text-bsds-ink">{{ $s->investigation_views_count }} / {{ $s->hint_usages_count }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('research.show', $s) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-bsds-gray">Belum ada data sesi untuk filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sessions->links() }}
        </div>
</x-app-layout>
