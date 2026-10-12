@php
    $levelLabel = ['foundation' => 'Level 1 - Foundation', 'developing' => 'Level 2 - Developing', 'challenge' => 'Level 3 - Challenge'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Choose Your Scenario</h2>
            <p class="text-sm text-bsds-gray mt-1">Pilih studi kasus untuk melatih keterampilan pengambilan keputusan dalam pelayanan bisnis.</p>
        </div>
    </x-slot>

    <div class="w-full space-y-8">
            @if (session('status'))
                <div class="p-3 bg-bsds-teal/10 border border-bsds-teal/30 text-bsds-ink rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            {{-- Progres --}}
            <div class="bg-white rounded-2xl border border-bsds-border p-5 flex items-center justify-between">
                <span class="text-sm text-bsds-gray">Your progress</span>
                <span class="font-heading font-semibold text-bsds-navy">{{ $completedCaseIds->count() }}/{{ $cases->count() }} kasus selesai</span>
            </div>

            {{-- Sesi berjalan --}}
            @if ($activeSessions->isNotEmpty())
                <div class="bg-bsds-orange/10 border border-bsds-orange/40 rounded-2xl p-6">
                    <h3 class="font-heading font-semibold text-bsds-navy mb-4">Continue simulation</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($activeSessions as $active)
                            <div class="bg-white p-4 rounded-xl border border-bsds-border flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-bsds-ink">{{ $active->case->title }}</h4>
                                    <p class="text-xs text-bsds-gray mt-1">
                                        {{ $active->mode === 'practice' ? 'Practice' : 'Assessment' }} &bull; Posisi: {{ $active->currentScenario?->step_code ?? '-' }}
                                    </p>
                                </div>
                                <a href="{{ route('simulations.show', $active) }}" class="inline-flex items-center px-4 py-2 bg-bsds-navy hover:bg-bsds-teal text-white text-xs font-semibold rounded-xl transition">Lanjutkan &rarr;</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Kasus --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($cases as $case)
                    <div class="bg-white rounded-2xl border border-bsds-border overflow-hidden flex flex-col hover:shadow-md transition">
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">{{ $case->code }}</span>
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-bsds-teal/10 text-bsds-teal">{{ $levelLabel[$case->level] ?? ucfirst($case->level) }}</span>
                            </div>
                            <h4 class="font-heading font-semibold text-bsds-navy mb-2">{{ $case->title }}</h4>
                            <p class="text-sm text-bsds-gray line-clamp-3 mb-4 flex-1">{{ $case->description }}</p>
                            <div class="flex items-center text-xs text-bsds-gray pt-3 border-t border-bsds-border gap-4 mb-4">
                                <span>Est. {{ $case->estimated_time }} menit</span>
                                @if ($case->role_title)<span>{{ $case->role_title }}</span>@endif
                                @if ($completedCaseIds->contains($case->id))<span class="text-bsds-success font-semibold">&#10003; Selesai</span>@endif
                            </div>
                            <form method="GET" action="{{ route('simulations.briefing', $case) }}" class="space-y-3" x-data="{ mode: 'practice' }">
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach (['practice' => 'Practice', 'assessment' => 'Assessment'] as $value => $label)
                                        <label class="p-2 border-2 border-bsds-border rounded-xl text-center text-xs font-semibold cursor-pointer hover:border-bsds-teal has-[:checked]:border-bsds-teal has-[:checked]:bg-[#E8F6F6] has-[:checked]:text-bsds-navy">
                                            <input type="radio" name="mode" value="{{ $value }}" class="sr-only" @checked($value === 'practice')>
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="w-full min-h-[48px] inline-flex justify-center items-center px-4 bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold rounded-xl transition">
                                    Start Case
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-8 rounded-2xl text-center text-bsds-gray border border-dashed border-bsds-border">Belum ada kasus simulasi yang aktif.</div>
                @endforelse
            </div>

            {{-- Riwayat --}}
            @if ($completedSessions->isNotEmpty())
                <div class="bg-white rounded-2xl border border-bsds-border p-6">
                    <h3 class="font-heading font-semibold text-bsds-navy mb-4">Hasil Saya</h3>
                    <div class="divide-y divide-bsds-border">
                        @foreach ($completedSessions as $done)
                            <div class="py-3.5 flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-sm text-bsds-ink">{{ $done->case->title }}</h4>
                                    <p class="text-xs text-bsds-gray">
                                        {{ $done->mode === 'practice' ? 'Practice' : 'Assessment' }} &bull; Percobaan ke-{{ $done->attempt_no }} &bull; {{ $done->completed_at?->format('d M Y, H:i') }}
                                        @unless ($done->reflection) &bull; <span class="text-bsds-warning font-semibold">Refleksi belum diisi</span> @endunless
                                    </p>
                                </div>
                                <a href="{{ route('simulations.journey', $done) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Lihat Hasil &rarr;</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
</x-app-layout>
