<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                    Laporan Simulasi Selesai
                </span>
                <h2 class="font-bold text-xl text-gray-900 leading-tight mt-1">
                    Decision Journey & Analisis Refleksi
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $session->case->title }}</p>
            </div>
            <div>
                <a href="{{ route('simulations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    &larr; Kembali ke Daftar Kasus
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Ringkasan Sesi --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-gray-400 block">Kode Kasus</span>
                    <span class="font-bold text-base text-gray-900">{{ $session->case->code }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Dimulai Pada</span>
                    <span class="font-semibold text-sm text-gray-700">{{ $session->started_at?->format('d M Y, H:i') }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Diselesaikan Pada</span>
                    <span class="font-semibold text-sm text-gray-700">{{ $session->completed_at?->format('d M Y, H:i') ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 block">Total Keputusan</span>
                    <span class="font-semibold text-sm text-indigo-600">{{ $session->decisionLogs->count() }} Langkah</span>
                </div>
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">
                        {{ $session->status }}
                    </span>
                </div>
            </div>

            {{-- Linimasa Perjalanan Keputusan (Decision Journey) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                <h3 class="text-base font-bold text-gray-900 mb-6 flex items-center">
                    <span class="mr-2">🛤️</span> Linimasa Jejak Keputusan (Decision Pathway)
                </h3>

                <div class="relative pl-6 border-l-2 border-indigo-200 space-y-8 ml-2">
                    @forelse ($session->decisionLogs as $index => $log)
                        <div class="relative">
                            {{-- Dot Marker --}}
                            <div class="absolute -left-[33px] top-0 w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-white">
                                {{ $index + 1 }}
                            </div>

                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 bg-white px-2 py-0.5 rounded border border-indigo-100">
                                        {{ $log->scenario?->step_code }}: {{ $log->scenario?->title }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                                </div>

                                {{-- Pilihan yang Diambil --}}
                                <div>
                                    <span class="text-xs text-gray-500 font-semibold uppercase block mb-1">Keputusan yang Anda Ambil:</span>
                                    <div class="flex items-start text-sm text-gray-900 font-medium bg-white p-3 rounded border border-gray-200">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold mr-2 mt-0.5">
                                            {{ $log->option?->label }}
                                        </span>
                                        <span>{{ $log->option?->option_text }}</span>
                                    </div>
                                </div>

                                {{-- Konsekuensi --}}
                                <div class="bg-blue-50/70 p-3 rounded border border-blue-100 text-xs text-blue-900">
                                    <span class="font-bold block mb-0.5 text-blue-800">Konsekuensi Langsung:</span>
                                    {{ $log->option?->consequence_text }}
                                </div>

                                {{-- Metadata Keyakinan & Investigasi --}}
                                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-gray-600 border-t border-gray-200">
                                    <div>
                                        <span class="font-medium">Tingkat Keyakinan:</span>
                                        <span class="font-semibold text-gray-900">
                                            @php
                                                $labels = [
                                                    'unsure' => 'Ragu-ragu',
                                                    'fairly_confident' => 'Cukup Yakin',
                                                    'confident' => 'Yakin',
                                                    'very_confident' => 'Sangat Yakin',
                                                ];
                                            @endphp
                                            {{ $labels[$log->confidence_level] ?? $log->confidence_level }}
                                        </span>
                                    </div>
                                    @if ($log->investigations_viewed && count($log->investigations_viewed) > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="font-medium">Dokumen dibuka:</span>
                                            @foreach ($log->investigations_viewed as $item)
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-white border border-gray-300 text-gray-700">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada jejak keputusan pada sesi ini.</p>
                    @endforelse

                    {{-- Titik Akhir / Ending yang Dicapai --}}
                    @if ($session->currentScenario)
                        <div class="relative">
                            <div class="absolute -left-[33px] top-0 w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-white">
                                🏁
                            </div>
                            <div class="bg-emerald-50 rounded-lg p-5 border border-emerald-200 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">
                                        {{ $session->currentScenario->step_code }}: {{ $session->currentScenario->title }}
                                    </span>
                                </div>
                                <p class="text-sm text-emerald-900 leading-relaxed font-medium">
                                    {{ $session->currentScenario->story_text }}
                                </p>
                                @if ($session->currentScenario->customer_dialogue)
                                    <blockquote class="text-xs text-emerald-800 italic border-l-2 border-emerald-400 pl-3">
                                        "{{ $session->currentScenario->customer_dialogue }}"
                                    </blockquote>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Refleksi Mandiri --}}
            @if ($session->reflection)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-6">
                    <h3 class="text-base font-bold text-gray-900 flex items-center">
                        <span class="mr-2">📝</span> Lembar Refleksi Diri (Self-Reflection)
                    </h3>

                    <div class="space-y-4 divide-y divide-gray-100">
                        <div class="pt-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                1. Alasan dan Dasar Pengambilan Keputusan:
                            </h4>
                            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-line">
                                {{ $session->reflection->q1_reason }}
                            </p>
                        </div>

                        <div class="pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                2. Evaluasi Konsekuensi Tak Terduga:
                            </h4>
                            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-line">
                                {{ $session->reflection->q2_unexpected_consequence }}
                            </p>
                        </div>

                        <div class="pt-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                3. Rencana Tindakan & Perbaikan di Masa Depan:
                            </h4>
                            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 whitespace-pre-line">
                                {{ $session->reflection->q3_future_action }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="text-center pt-2">
                <a href="{{ route('simulations.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                    &larr; Selesai & Kembali ke Katalog Simulasi
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
