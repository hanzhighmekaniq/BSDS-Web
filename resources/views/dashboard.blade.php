@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="w-full space-y-6">

            <div class="bg-white rounded-2xl border border-bsds-border p-6">
                <h3 class="font-heading font-semibold text-xl text-bsds-navy">{{ $greeting }}, {{ explode(' ', auth()->user()->name)[0] }}</h3>
                <p class="text-sm text-bsds-gray mt-1">
                    @role('student')
                        Siap mengambil keputusan hari ini?
                    @else
                        Selamat datang kembali di Business Service Decision Simulator.
                    @endrole
                </p>
            </div>

            @role('student')
                @unless (auth()->user()->studentProfile?->classroom_id)
                    <div class="flex flex-col gap-3 p-5 border sm:flex-row sm:items-center sm:justify-between bg-bsds-orange/10 border-bsds-orange/40 rounded-2xl">
                        <div>
                            <h4 class="font-semibold font-heading text-bsds-navy">Pilih kelas kamu</h4>
                            <p class="mt-1 text-sm text-bsds-gray">Kamu belum memilih kelas. Pilih kelas agar hasil belajar tercatat.</p>
                        </div>
                        <a href="{{ route('profile.edit') }}#kelas" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition shrink-0">Pilih Kelas &rarr;</a>
                    </div>
                @endunless

                {{-- Continue simulation --}}
                @if ($activeSession)
                    <div class="bg-bsds-navy rounded-2xl p-6 text-white">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-bsds-orange">
                            <x-heroicon-o-play class="w-3.5 h-3.5" /> Continue Simulation
                        </span>
                        <h4 class="font-heading font-semibold text-lg mt-1">{{ $activeSession->case->title }}</h4>
                        <p class="text-sm text-white/70 mt-1">Posisi: {{ $activeSession->currentScenario?->step_code ?? '-' }} &bull; {{ $activeSession->mode === 'practice' ? 'Practice' : 'Assessment' }} Mode</p>
                        <a href="{{ route('simulations.show', $activeSession) }}" class="inline-flex items-center mt-4 px-5 py-2.5 rounded-xl bg-white text-bsds-navy text-sm font-semibold hover:bg-bsds-bg transition">
                            Lanjutkan &rarr;
                        </a>
                    </div>
                @endif

                {{-- Stat strip --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                        <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $completedCount }}/{{ $totalCases }}</div>
                        <div class="text-xs text-bsds-gray mt-1">Kasus selesai</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center">
                        <div class="font-heading font-semibold text-2xl text-bsds-navy">{{ $inProgressCount }}</div>
                        <div class="text-xs text-bsds-gray mt-1">Sedang berjalan</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-bsds-border p-4 text-center col-span-2 md:col-span-1">
                        <div class="h-2 bg-bsds-border rounded-full mt-2.5 overflow-hidden">
                            <div class="h-2 rounded-full bg-bsds-teal" style="width: {{ $totalCases > 0 ? round($completedCount / $totalCases * 100) : 0 }}%"></div>
                        </div>
                        <div class="text-xs text-bsds-gray mt-2">Progres keseluruhan</div>
                    </div>
                </div>

                {{-- Quick menu --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('materials.index') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-bsds-teal/10 text-bsds-teal flex items-center justify-center mb-3">
                            <x-heroicon-o-academic-cap class="w-5 h-5" />
                        </div>
                        <h4 class="font-heading font-semibold text-bsds-navy">Materi</h4>
                        <p class="text-xs text-bsds-gray mt-1">Pelajari konsep sebelum simulasi.</p>
                    </a>
                    <a href="{{ route('quizzes.index') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-bsds-orange/10 text-bsds-orange flex items-center justify-center mb-3">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                        </div>
                        <h4 class="font-heading font-semibold text-bsds-navy">Quiz</h4>
                        <p class="text-xs text-bsds-gray mt-1">Uji pemahaman materi.</p>
                    </a>
                    <a href="{{ route('simulations.index') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-bsds-navy/10 text-bsds-navy flex items-center justify-center mb-3">
                            <x-heroicon-o-puzzle-piece class="w-5 h-5" />
                        </div>
                        <h4 class="font-heading font-semibold text-bsds-navy">Simulasi</h4>
                        <p class="text-xs text-bsds-gray mt-1">Pilih dan mulai studi kasus.</p>
                    </a>
                    <a href="{{ route('progress') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-bsds-teal/10 text-bsds-teal flex items-center justify-center mb-3">
                            <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                        </div>
                        <h4 class="font-heading font-semibold text-bsds-navy">Progress Saya</h4>
                        <p class="text-xs text-bsds-gray mt-1">Lihat capaian & skill breakdown.</p>
                    </a>
                    <a href="{{ route('guide') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                        <div class="w-10 h-10 rounded-xl bg-bsds-orange/10 text-bsds-orange flex items-center justify-center mb-3">
                            <x-heroicon-o-book-open class="w-5 h-5" />
                        </div>
                        <h4 class="font-heading font-semibold text-bsds-navy">Panduan</h4>
                        <p class="text-xs text-bsds-gray mt-1">Cara bermain &amp; tips keputusan.</p>
                    </a>
                </div>
            @endrole

            @role('admin|researcher')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @role('researcher|admin')
                        <a href="{{ route('research.index') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                            <div class="w-10 h-10 rounded-xl bg-bsds-teal/10 text-bsds-teal flex items-center justify-center mb-3">
                                <x-heroicon-o-chart-bar class="w-5 h-5" />
                            </div>
                            <h4 class="font-heading font-semibold text-bsds-navy">Analitik Penelitian</h4>
                            <p class="text-xs text-bsds-gray mt-1">Rekap sesi, rute keputusan, dan skor indikator.</p>
                        </a>
                    @endrole
                    @role('admin')
                        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-2xl border border-bsds-border p-5 hover:border-bsds-teal hover:shadow-md transition">
                            <div class="w-10 h-10 rounded-xl bg-bsds-navy/10 text-bsds-navy flex items-center justify-center mb-3">
                                <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                            </div>
                            <h4 class="font-heading font-semibold text-bsds-navy">Administrasi</h4>
                            <p class="text-xs text-bsds-gray mt-1">Kelola pengguna, kelas, kasus, dan indikator.</p>
                        </a>
                    @endrole
                </div>
            @endrole
        </div>
</x-app-layout>
