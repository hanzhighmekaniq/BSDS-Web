@php
    $totalCases = $cases->count();
    $completedCount = $completedCaseIds->count();
    $percent = $totalCases > 0 ? round($completedCount / $totalCases * 100) : 0;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
            {{ __('Progress Saya') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Ringkasan --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-heading font-semibold text-bsds-navy">Cases Completed</h3>
                    <span class="font-heading font-semibold text-2xl text-bsds-navy">{{ $completedCount }}/{{ $totalCases }}</span>
                </div>
                <div class="h-2.5 bg-bsds-border rounded-full overflow-hidden">
                    <div class="h-2.5 rounded-full bg-bsds-teal" style="width: {{ $percent }}%"></div>
                </div>
            </div>

            {{-- Skill breakdown --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8">
                <h3 class="font-heading font-semibold text-bsds-navy mb-1">Skill Breakdown</h3>
                <p class="text-xs text-bsds-gray mb-6">Rata-rata dihitung dari seluruh kasus yang sudah Anda selesaikan.</p>

                @forelse ($indicators as $indicator)
                    @php $avg = $indicatorAverages[$indicator->id] ?? null; @endphp
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="text-bsds-ink">{{ $indicator->name }}</span>
                            <span class="font-semibold text-bsds-navy">{{ $avg !== null ? $avg.'%' : '-' }}</span>
                        </div>
                        <div class="h-2 bg-bsds-border rounded-full overflow-hidden">
                            <div class="h-2 rounded-full bg-bsds-teal" style="width: {{ $avg ?? 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-bsds-gray">Belum ada data indikator.</p>
                @endforelse

                @if ($completedCount === 0)
                    <p class="text-sm text-bsds-gray mt-4 pt-4 border-t border-bsds-border">Selesaikan minimal satu kasus untuk melihat skill breakdown Anda.</p>
                @endif
            </div>

            {{-- Case checklist --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8">
                <h3 class="font-heading font-semibold text-bsds-navy mb-4">Cases</h3>
                <div class="divide-y divide-bsds-border">
                    @foreach ($cases as $case)
                        @php $done = $completedCaseIds->contains($case->id); @endphp
                        <div class="py-3 flex items-center gap-3">
                            @if ($done)
                                <span class="w-6 h-6 rounded-full bg-bsds-success text-white flex items-center justify-center shrink-0">
                                    <x-heroicon-o-check class="w-4 h-4" />
                                </span>
                            @else
                                <span class="w-6 h-6 rounded-full border-2 border-bsds-border shrink-0"></span>
                            @endif
                            <span class="text-sm {{ $done ? 'text-bsds-ink font-medium' : 'text-bsds-gray' }}">{{ $case->title }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('simulations.index') }}" class="min-h-[44px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                    Lanjutkan Simulasi
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
