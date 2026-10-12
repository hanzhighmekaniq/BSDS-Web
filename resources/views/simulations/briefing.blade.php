@php
    $levelLabel = ['foundation' => 'Level 1 - Foundation', 'developing' => 'Level 2 - Developing', 'challenge' => 'Level 3 - Challenge'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">{{ $case->code }}</span>
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">{{ $case->title }}</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">{{ $case->code }}</span>
                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-bsds-teal/10 text-bsds-teal">{{ $levelLabel[$case->level] ?? ucfirst($case->level) }}</span>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-gray">&#127919; Mission</h3>
                <p class="text-bsds-ink leading-relaxed whitespace-pre-line">{{ $case->briefing_text }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if ($case->role_title)
                    <div class="bg-white rounded-2xl border border-bsds-border p-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-1">&#128100; Your Role</h3>
                        <p class="text-bsds-ink font-medium">{{ $case->role_title }}</p>
                    </div>
                @endif
                <div class="bg-white rounded-2xl border border-bsds-border p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-1">&#9201; Estimated Time</h3>
                    <p class="text-bsds-ink font-medium">{{ $case->estimated_time }} menit</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-bsds-orange bg-bsds-orange/10 p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-navy mb-1">&#9888;&#65039; Remember</h3>
                <p class="text-sm text-bsds-ink">Keputusan Anda dapat memengaruhi situasi berikutnya.</p>
            </div>

            <div class="flex justify-end">
                <form method="POST" action="{{ route('simulations.start', $case) }}">
                    @csrf
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <button type="submit" class="min-h-[48px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                        Mulai Misi &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
