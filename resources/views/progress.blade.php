@php
    $totalCases = $cases->count();
    $completedCount = $completedCaseIds->count();
    $percent = $totalCases > 0 ? round($completedCount / $totalCases * 100) : 0;
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">
            {{ __('Progress Saya') }}
        </h2>
    </x-slot>

    <div class="w-full space-y-6">

            {{-- Ringkasan --}}
            <div class="p-6 bg-white border rounded-2xl border-bsds-border">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold font-heading text-bsds-navy">Cases Completed</h3>
                    <span class="text-2xl font-semibold font-heading text-bsds-navy">{{ $completedCount }}/{{ $totalCases }}</span>
                </div>
                <div class="h-2.5 bg-bsds-border rounded-full overflow-hidden">
                    <div class="h-2.5 rounded-full bg-bsds-teal" style="width: {{ $percent }}%"></div>
                </div>
            </div>

            {{-- Skill breakdown --}}
            <div class="p-6 bg-white border rounded-2xl border-bsds-border md:p-8">
                <h3 class="mb-1 font-semibold font-heading text-bsds-navy">Skill Breakdown</h3>
                <p class="mb-6 text-xs text-bsds-gray">Rata-rata dihitung dari seluruh kasus yang sudah Anda selesaikan.</p>

                @forelse ($indicators as $indicator)
                    @php $avg = $indicatorAverages[$indicator->id] ?? null; @endphp
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center justify-between text-sm mb-1.5">
                            <span class="text-bsds-ink">{{ $indicator->name }}</span>
                            <span class="font-semibold text-bsds-navy">{{ $avg !== null ? $avg.'%' : '-' }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-bsds-border">
                            <div class="h-2 rounded-full bg-bsds-teal" style="width: {{ $avg ?? 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-bsds-gray">Belum ada data indikator.</p>
                @endforelse

                @if ($completedCount === 0)
                    <p class="pt-4 mt-4 text-sm border-t text-bsds-gray border-bsds-border">Selesaikan minimal satu kasus untuk melihat skill breakdown Anda.</p>
                @endif
            </div>

            {{-- Case checklist --}}
            <div class="p-6 bg-white border rounded-2xl border-bsds-border md:p-8">
                <h3 class="mb-4 font-semibold font-heading text-bsds-navy">Cases</h3>
                <div class="divide-y divide-bsds-border">
                    @foreach ($cases as $case)
                        @php $done = $completedCaseIds->contains($case->id); @endphp
                        <div class="flex items-center gap-3 py-3">
                            @if ($done)
                                <span class="flex items-center justify-center w-6 h-6 text-white rounded-full bg-bsds-success shrink-0">
                                    <x-heroicon-o-check class="w-4 h-4" />
                                </span>
                            @else
                                <span class="w-6 h-6 border-2 rounded-full border-bsds-border shrink-0"></span>
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
</x-app-layout>
