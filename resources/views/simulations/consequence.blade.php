@php
    $type = $log->consequence_type ?? 'neutral';
    $tone = $type === 'positive'
        ? ['border-bsds-teal', 'bg-bsds-teal/10', 'text-bsds-teal', 'Keputusan yang tepat']
        : ['border-bsds-warning', 'bg-bsds-warning/10', 'text-bsds-ink', $type === 'negative' ? 'A new problem has emerged' : 'Hasil keputusan'];
    $isEnd = ! $next || $next->is_ending;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">Consequence</span>
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">{{ $session->case->title }}</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-gray">Your decision</h3>
                <p class="text-bsds-ink"><span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-bsds-navy text-white text-xs font-bold mr-2">{{ $log->option->label }}</span>{{ $log->option->option_text }}</p>
            </div>

            <div class="rounded-2xl border-2 {{ $tone[0] }} {{ $tone[1] }} p-6 space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider {{ $tone[2] }}">{{ $tone[3] }}</span>
                <h3 class="font-heading font-semibold text-bsds-navy">What happens?</h3>
                <p class="text-bsds-ink leading-relaxed">{{ $log->option->consequence_text }}</p>
                @if ($type === 'negative' && ! $isEnd)
                    <p class="text-sm text-bsds-gray">This is part of the simulation. Gunakan informasi baru untuk menentukan tindakan berikutnya.</p>
                @endif
            </div>

            @if (! $isEnd && $next?->new_problem_text)
                <div class="rounded-2xl border border-bsds-orange bg-bsds-orange/10 p-5">
                    <span class="text-xs font-bold uppercase tracking-wider text-bsds-navy">New Situation</span>
                    <p class="text-sm text-bsds-ink mt-1">{{ $next->new_problem_text }}</p>
                </div>
            @endif

            <div class="flex justify-end">
                @if ($isEnd)
                    <a href="{{ route('simulations.journey', $session) }}" class="min-h-[48px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                        Lihat Hasil Simulasi &rarr;
                    </a>
                @else
                    <a href="{{ route('simulations.show', $session) }}" class="min-h-[48px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                        Continue &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
