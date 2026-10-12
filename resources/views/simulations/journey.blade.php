@php
    $categoryLabel = ['baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_latihan' => 'Perlu latihan'];
    $categoryColor = ['baik' => 'bg-bsds-success', 'cukup' => 'bg-bsds-warning', 'perlu_latihan' => 'bg-bsds-orange'];
    $confidenceLabel = ['very_unsure' => 'Sangat ragu', 'unsure' => 'Ragu-ragu', 'fairly_confident' => 'Cukup yakin', 'confident' => 'Yakin', 'very_confident' => 'Sangat yakin'];
    $typeBorder = ['positive' => 'border-bsds-teal', 'neutral' => 'border-bsds-warning', 'negative' => 'border-bsds-orange'];
    $questions = [
        'q1_reason' => 'Mengapa Anda memilih keputusan pertama Anda?',
        'q2_hardest_decision' => 'Keputusan mana yang paling sulit?',
        'q3_unexpected_consequence' => 'Apakah ada konsekuensi yang tidak Anda perkirakan?',
        'q4_future_action' => 'Jika menghadapi situasi yang sama di dunia kerja, apa yang akan Anda lakukan?',
    ];
    $duration = $session->duration_seconds ? gmdate($session->duration_seconds >= 3600 ? 'H:i:s' : 'i:s', $session->duration_seconds) : '-';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">Simulation Complete</span>
                <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">{{ $session->case->title }}</h2>
                <p class="text-sm text-bsds-gray mt-0.5">{{ $session->mode === 'practice' ? 'Practice Mode' : 'Assessment Mode' }} &bull; Percobaan ke-{{ $session->attempt_no }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($session->reflection)
                    <a href="{{ route('simulations.feedback', $session) }}" class="inline-flex items-center px-4 py-2 bg-white border border-bsds-border hover:border-bsds-teal text-bsds-navy text-xs font-semibold rounded-xl transition">
                        Lihat Feedback
                    </a>
                @endif
                <a href="{{ route('simulations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-bsds-border hover:border-bsds-teal text-bsds-navy text-xs font-semibold rounded-xl transition">
                    &larr; Kembali ke Daftar Kasus
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="p-3 bg-bsds-teal/10 border border-bsds-teal/30 text-bsds-ink rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            {{-- Ringkasan --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([['Keputusan', $stats['decisions']], ['Konsekuensi negatif', $stats['negative']], ['Informasi dibuka', $stats['investigations']], ['Durasi', $duration]] as [$label, $value])
                    <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                        <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $value }}</div>
                        <div class="text-xs text-bsds-gray mt-1">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Final outcome --}}
            @if ($session->currentScenario)
                <div class="bg-white rounded-2xl border-2 border-bsds-teal p-6 space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-bsds-teal">Final Outcome</span>
                    <h3 class="font-heading font-semibold text-bsds-navy">{{ $session->currentScenario->title }}</h3>
                    <p class="text-bsds-ink leading-relaxed">{{ $session->currentScenario->story_text }}</p>
                    @if ($session->currentScenario->customer_dialogue)
                        <blockquote class="border-l-4 border-bsds-orange pl-3 italic text-sm text-bsds-ink">"{{ $session->currentScenario->customer_dialogue }}"</blockquote>
                    @endif
                </div>
            @endif

            {{-- Profil performa + feedback --}}
            @if ($feedback->isNotEmpty())
                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                    <h3 class="font-heading font-semibold text-bsds-navy">Profil Performa</h3>
                    <p class="text-xs text-bsds-gray">Indikator berikut bersifat sementara dan akan disesuaikan dengan instrumen penelitian.</p>
                    <div class="space-y-4">
                        @foreach ($feedback as $row)
                            @php
                                $result = $row['result'];
                                $percent = $result->max_score > 0 ? round($result->raw_score / $result->max_score * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-bsds-ink">{{ $row['indicator']->name }}</span>
                                    <span class="text-xs font-semibold text-bsds-navy">{{ $categoryLabel[$result->category] ?? $result->category }}</span>
                                </div>
                                <div class="h-2 bg-bsds-border rounded-full mt-1.5 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $categoryColor[$result->category] ?? 'bg-bsds-gray' }}" style="width: {{ $percent }}%"></div>
                                </div>
                                @if ($row['feedback'])
                                    <p class="text-xs text-bsds-gray mt-1.5">{{ $row['feedback'] }} <span class="text-bsds-ink">{{ $row['recommendation'] }}</span></p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Decision journey --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8">
                <h3 class="font-heading font-semibold text-bsds-navy mb-6">Linimasa Jejak Keputusan (Your Decision Journey)</h3>

                <div class="relative pl-6 border-l-2 border-bsds-teal/40 space-y-8 ml-2">
                    @foreach ($session->decisionLogs as $log)
                        @php
                            $viewed = $session->investigationViews->where('scenario_id', $log->scenario_id);
                        @endphp
                        <div class="relative">
                            <div class="absolute -left-[33px] top-0 w-6 h-6 rounded-full bg-bsds-navy text-white flex items-center justify-center text-xs font-bold ring-4 ring-white">{{ $log->step_order }}</div>
                            <div class="rounded-2xl p-5 border-2 {{ $typeBorder[$log->consequence_type] ?? 'border-bsds-border' }} bg-bsds-bg space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-bsds-navy">{{ $log->scenario?->step_code }}: {{ $log->scenario?->title }}</span>
                                    @if ($log->time_spent_seconds !== null)
                                        <span class="text-xs text-bsds-gray">{{ $log->time_spent_seconds }} dtk</span>
                                    @endif
                                </div>
                                <p class="text-sm text-bsds-ink"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-bsds-navy text-white text-xs font-bold mr-1.5">{{ $log->option?->label }}</span>{{ $log->option?->option_text }}</p>
                                <p class="text-xs text-bsds-ink bg-white rounded-xl p-3 border border-bsds-border"><span class="font-bold">Konsekuensi:</span> {{ $log->option?->consequence_text }}</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-bsds-gray pt-2 border-t border-bsds-border">
                                    <span>Keyakinan: <span class="font-semibold text-bsds-ink">{{ $confidenceLabel[$log->confidence_level] ?? '-' }}</span></span>
                                    @if ($viewed->isNotEmpty())
                                        <span>Informasi: <span class="font-semibold text-bsds-ink">{{ $viewed->map(fn ($v) => $v->item?->title)->filter()->implode(', ') }}</span></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- What-if (Practice) --}}
            @if ($whatIf)
                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                    <h3 class="font-heading font-semibold text-bsds-navy">What if you chose differently?</h3>
                    <p class="text-sm text-bsds-gray">Pada <strong>{{ $whatIf['log']->scenario->title }}</strong> Anda memilih "{{ \Illuminate\Support\Str::limit($whatIf['log']->option->option_text, 90) }}". Bagaimana jika Anda memilih yang lain?</p>
                    <div class="space-y-3">
                        @foreach ($whatIf['alternatives'] as $alt)
                            <div class="rounded-2xl border-2 {{ $typeBorder[$alt->consequence_type] ?? 'border-bsds-border' }} p-4">
                                <p class="text-sm text-bsds-ink"><span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-bsds-navy text-white text-xs font-bold mr-1.5">{{ $alt->label }}</span>{{ $alt->option_text }}</p>
                                <p class="text-xs text-bsds-gray mt-2">&rarr; {{ $alt->consequence_text }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Refleksi --}}
            @if ($session->reflection)
                <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8 space-y-4">
                    <h3 class="font-heading font-semibold text-bsds-navy">Refleksi Anda</h3>
                    @foreach ($questions as $field => $label)
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-1">{{ $loop->iteration }}. {{ $label }}</h4>
                            <p class="text-sm text-bsds-ink bg-bsds-bg p-3 rounded-xl whitespace-pre-line">{{ $session->reflection->{$field} }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border-2 border-bsds-orange p-6 text-center space-y-3">
                    <h3 class="font-heading font-semibold text-bsds-navy">Langkah terakhir: Reflect</h3>
                    <p class="text-sm text-bsds-gray">Tuliskan refleksi Anda tentang keputusan yang telah diambil.</p>
                    <a href="{{ route('simulations.reflection', $session) }}" class="min-h-[48px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Isi Refleksi &rarr;</a>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('simulations.index') }}" class="min-h-[48px] inline-flex items-center px-6 rounded-xl border-2 border-bsds-navy text-bsds-navy hover:bg-bsds-navy hover:text-white font-semibold text-sm transition">Kembali ke Daftar Kasus</a>
            </div>
        </div>
    </div>
</x-app-layout>
