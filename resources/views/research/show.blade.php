@php
    $profile = $session->user?->studentProfile;
    $confidenceLabel = ['very_unsure' => 'Sangat ragu', 'unsure' => 'Ragu-ragu', 'fairly_confident' => 'Cukup yakin', 'confident' => 'Yakin', 'very_confident' => 'Sangat yakin'];
    $categoryLabel = ['baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_latihan' => 'Perlu latihan'];
    $questions = [
        'q1_reason' => 'Mengapa Anda memilih keputusan pertama Anda?',
        'q2_hardest_decision' => 'Keputusan mana yang paling sulit?',
        'q3_unexpected_consequence' => 'Apakah ada konsekuensi yang tidak Anda perkirakan?',
        'q4_future_action' => 'Jika menghadapi situasi yang sama di dunia kerja, apa yang akan Anda lakukan?',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Detail Sesi #{{ $session->id }}</h2>
                <p class="text-sm text-bsds-gray mt-1">{{ $session->user?->name }} ({{ $profile?->nis }}) &bull; {{ $profile?->classroom?->name ?? '-' }} &bull; {{ $session->case?->title }}</p>
            </div>
            <a href="{{ route('research.index') }}" class="text-sm font-semibold text-bsds-teal hover:underline">&larr; Kembali ke rekap</a>
        </div>
    </x-slot>

    <div class="w-full space-y-6">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([['Mode', $session->mode], ['Status', $session->status], ['Durasi (dtk)', $session->duration_seconds ?? '-'], ['Hasil akhir', $categoryLabel[$session->final_outcome] ?? '-']] as [$label, $value])
                    <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                        <div class="font-heading font-semibold text-lg text-bsds-navy">{{ $value }}</div>
                        <div class="text-xs text-bsds-gray mt-1">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6">
                <h3 class="font-heading font-semibold text-bsds-navy mb-4">Rute keputusan</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-xs uppercase tracking-wider text-bsds-gray">
                            <tr><th class="py-2 text-left">#</th><th class="py-2 text-left">Skenario</th><th class="py-2 text-left">Pilihan</th><th class="py-2 text-left">Konsekuensi</th><th class="py-2 text-left">Keyakinan</th><th class="py-2 text-left">Waktu</th></tr>
                        </thead>
                        <tbody class="divide-y divide-bsds-border">
                            @foreach ($session->decisionLogs->sortBy('step_order') as $log)
                                <tr>
                                    <td class="py-2 pr-3">{{ $log->step_order }}</td>
                                    <td class="py-2 pr-3">{{ $log->scenario?->step_code }}</td>
                                    <td class="py-2 pr-3"><span class="font-semibold">{{ $log->option?->label }}</span> <span class="text-bsds-gray text-xs">{{ \Illuminate\Support\Str::limit($log->option?->option_text, 70) }}</span></td>
                                    <td class="py-2 pr-3">{{ $log->consequence_type }}</td>
                                    <td class="py-2 pr-3">{{ $confidenceLabel[$log->confidence_level] ?? '-' }}</td>
                                    <td class="py-2">{{ $log->time_spent_seconds ?? '-' }} dtk</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-bsds-gray mt-3">
                    Informasi dibuka: {{ $session->investigationViews->map(fn ($v) => $v->item?->title)->filter()->implode('; ') ?: '-' }}
                    &bull; Hint dipakai: {{ $session->hintUsages->count() }}
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6">
                <h3 class="font-heading font-semibold text-bsds-navy mb-4">Skor indikator</h3>
                @forelse ($session->indicatorResults->sortBy('indicator.sort_order') as $r)
                    <div class="flex justify-between text-sm py-1.5 border-b border-bsds-border last:border-0">
                        <span>{{ $r->indicator?->name }}</span>
                        <span class="font-semibold text-bsds-navy">{{ $r->raw_score }}/{{ $r->max_score }} &bull; {{ $categoryLabel[$r->category] ?? $r->category }}</span>
                    </div>
                @empty
                    <p class="text-sm text-bsds-gray">Belum ada skor (sesi belum selesai).</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                <h3 class="font-heading font-semibold text-bsds-navy">Refleksi</h3>
                @if ($session->reflection)
                    @foreach ($questions as $field => $label)
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-1">{{ $loop->iteration }}. {{ $label }}</h4>
                            <p class="text-sm text-bsds-ink bg-bsds-bg p-3 rounded-xl whitespace-pre-line">{{ $session->reflection->{$field} }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-bsds-gray">Belum mengisi refleksi.</p>
                @endif
            </div>
        </div>
</x-app-layout>
