<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Simulasi Pengambilan Keputusan (BSDS)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Pilih studi kasus untuk melatih keterampilan layanan pelanggan dan penalaran situasional.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Sesi Berjalan --}}
            @if ($activeSessions->isNotEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-amber-900 mb-4 flex items-center">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 animate-ping"></span>
                        Simulasi Sedang Berjalan (Lanjutkan Sesi)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($activeSessions as $active)
                            <div class="bg-white p-4 rounded-lg shadow-sm border border-amber-100 flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $active->case->title }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Posisi: <span class="font-semibold text-indigo-600">{{ $active->currentScenario?->step_code ?? 'Refleksi' }}</span>
                                        &bull; Dimulai: {{ $active->started_at?->diffForHumans() }}
                                    </p>
                                </div>
                                <a href="{{ route('simulations.show', $active) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-md shadow-sm transition">
                                    Lanjutkan &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Daftar Kasus Tersedia --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kasus Simulasi Tersedia</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($cases as $case)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition">
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full uppercase bg-indigo-50 text-indigo-700 tracking-wider">
                                        {{ $case->code }}
                                    </span>
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-medium rounded-full {{ $case->difficulty === 'hard' ? 'bg-rose-100 text-rose-700' : ($case->difficulty === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        Tingkat: {{ ucfirst($case->difficulty) }}
                                    </span>
                                </div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ $case->title }}</h4>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-4 flex-1">
                                    {{ $case->description }}
                                </p>
                                <div class="flex items-center text-xs text-gray-500 pt-3 border-t border-gray-100 gap-4 mb-4">
                                    <span>⏱ Est. {{ $case->estimated_time }} Menit</span>
                                    <span>🔀 {{ $case->scenarios_count }} Titik Skenario</span>
                                </div>
                                <form method="POST" action="{{ route('simulations.start', $case) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-medium rounded-lg shadow-sm transition">
                                        Mulai Simulasi Baru
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white p-8 rounded-xl text-center text-gray-500 border border-dashed border-gray-300">
                            Belum ada kasus simulasi yang aktif saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat Sesi Selesai --}}
            @if ($completedSessions->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Riwayat Simulasi Terakhir Anda</h3>
                    <div class="divide-y divide-gray-100">
                        @foreach ($completedSessions as $done)
                            <div class="py-3.5 flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-sm text-gray-900">{{ $done->case->title }}</h4>
                                    <p class="text-xs text-gray-500">Selesai pada: {{ $done->completed_at?->format('d M Y, H:i') }}</p>
                                </div>
                                <a href="{{ route('simulations.journey', $done) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                    Lihat Decision Journey &rarr;
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
