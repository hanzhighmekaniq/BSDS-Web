<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                    {{ $session->case->code }} &bull; Skenario: {{ $scenario->step_code }}
                </span>
                <h2 class="font-bold text-xl text-gray-900 leading-tight mt-1">
                    {{ $scenario->title }}
                </h2>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-500 block">Status Sesi</span>
                <span class="text-xs font-bold text-amber-600 uppercase bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                    {{ $session->status }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        viewed: [],
        markViewed(item) {
            if (!this.viewed.includes(item)) {
                this.viewed.push(item);
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Konsekuensi dari Langkah Sebelumnya (jika ada) --}}
            @if (session('consequence'))
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 text-blue-500 text-lg">💡</div>
                        <div class="ml-3">
                            <h4 class="text-sm font-bold text-blue-900">Hasil Keputusan Terakhir Anda:</h4>
                            <p class="text-sm text-blue-800 mt-1">{{ session('consequence') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Kolom Kiri & Tengah: Narasi Kasus & Opsi Keputusan --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Kotak Cerita Situasi --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Deskripsi Situasi</h3>
                        <p class="text-gray-800 text-base leading-relaxed">
                            {{ $scenario->story_text }}
                        </p>

                        {{-- Dialog Pelanggan --}}
                        @if ($scenario->customer_dialogue)
                            <div class="mt-4 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                                <div class="flex items-center text-xs font-semibold text-amber-800 mb-1">
                                    <span class="mr-1.5">👤</span> Dialog Pelanggan:
                                </div>
                                <blockquote class="italic text-gray-800 font-medium">
                                    "{{ $scenario->customer_dialogue }}"
                                </blockquote>
                            </div>
                        @endif
                    </div>

                    {{-- Jika Skenario adalah Ending --}}
                    @if ($scenario->is_ending)
                        <div class="bg-emerald-50 border border-emerald-300 rounded-xl p-6 text-center space-y-4">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                                🏁
                            </div>
                            <h3 class="text-lg font-bold text-emerald-900">Titik Akhir Skenario Tercapai</h3>
                            <p class="text-sm text-emerald-800 max-w-xl mx-auto">
                                Anda telah mencapai akhir alur percabangan ini. Langkah berikutnya adalah mengisi lembar refleksi mandiri untuk menganalisis keputusan yang telah diambil.
                            </p>
                            <div class="pt-2">
                                <a href="{{ route('simulations.reflection', $session) }}" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-sm transition">
                                    Lanjut ke Refleksi Mandiri &rarr;
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Form Pengambilan Keputusan --}}
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-base font-bold text-gray-900 mb-4">Pilihan Keputusan Frontline</h3>

                            <form method="POST" action="{{ route('simulations.decision', $session) }}" class="space-y-6">
                                @csrf

                                {{-- Hidden viewed investigations input --}}
                                <template x-for="(item, index) in viewed" :key="index">
                                    <input type="hidden" name="investigations_viewed[]" :value="item">
                                </template>

                                {{-- Opsi Pilihan A/B/C --}}
                                <div class="space-y-3">
                                    @foreach ($scenario->options as $opt)
                                        <label class="block p-4 border rounded-xl cursor-pointer hover:bg-indigo-50/50 hover:border-indigo-300 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/70 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                                            <div class="flex items-start">
                                                <input type="radio" name="option_id" value="{{ $opt->id }}" class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" required>
                                                <div class="ml-3">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-bold text-gray-700 mr-1.5">
                                                        {{ $opt->label }}
                                                    </span>
                                                    <span class="text-sm font-medium text-gray-900">{{ $opt->option_text }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Tingkat Keyakinan (Confidence Level) --}}
                                <div class="pt-4 border-t border-gray-100">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">
                                        Tingkat Keyakinan Anda Terhadap Keputusan Ini:
                                    </h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <label class="p-3 border rounded-lg text-center cursor-pointer hover:bg-gray-50 text-xs font-medium has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700">
                                            <input type="radio" name="confidence_level" value="unsure" class="sr-only" required>
                                            <span>Ragu-ragu</span>
                                        </label>
                                        <label class="p-3 border rounded-lg text-center cursor-pointer hover:bg-gray-50 text-xs font-medium has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700">
                                            <input type="radio" name="confidence_level" value="fairly_confident" class="sr-only">
                                            <span>Cukup Yakin</span>
                                        </label>
                                        <label class="p-3 border rounded-lg text-center cursor-pointer hover:bg-gray-50 text-xs font-medium has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700">
                                            <input type="radio" name="confidence_level" value="confident" class="sr-only">
                                            <span>Yakin</span>
                                        </label>
                                        <label class="p-3 border rounded-lg text-center cursor-pointer hover:bg-gray-50 text-xs font-medium has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700">
                                            <input type="radio" name="confidence_level" value="very_confident" class="sr-only">
                                            <span>Sangat Yakin</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="pt-2 flex justify-end">
                                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                                        Kirim Keputusan & Lanjutkan &rarr;
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Kolom Kanan: Meja Investigasi (SOP, Resi, Stock, Supervisor) --}}
                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center">
                                <span class="mr-1.5">🔍</span> Meja Investigasi Frontline
                            </h3>
                            <span class="text-xs text-gray-400">Bukti & Panduan</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                            Buka dokumen atau sistem berikut sebelum memutuskan tindakan. Setiap dokumen yang Anda buka akan dicatat sebagai bagian dari penalaran Anda.
                        </p>

                        <div class="space-y-3">
                            @forelse ($scenario->investigationItems as $item)
                                <div x-data="{ open: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button"
                                        @click="open = !open; markViewed('{{ $item->title }}')"
                                        class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition text-xs font-semibold text-gray-800">
                                        <div class="flex items-center space-x-2">
                                            @if ($item->type === 'sop')
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-100 text-blue-700 font-bold uppercase">SOP</span>
                                            @elseif ($item->type === 'receipt')
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-100 text-purple-700 font-bold uppercase">RESI</span>
                                            @elseif ($item->type === 'stock')
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-700 font-bold uppercase">STOK</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-amber-100 text-amber-700 font-bold uppercase">SPV</span>
                                            @endif
                                            <span>{{ $item->title }}</span>
                                        </div>
                                        <span x-text="open ? '−' : '+'" class="text-gray-400 font-bold"></span>
                                    </button>
                                    <div x-show="open" x-cloak class="p-3 bg-white text-xs text-gray-700 whitespace-pre-line border-t border-gray-100 font-mono">
                                        {{ $item->content }}
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-400 text-center">
                                    Tidak ada item investigasi tambahan pada langkah ini.
                                </div>
                            @endforelse
                        </div>

                        {{-- Badge item yang telah dilihat --}}
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <span class="text-[11px] font-semibold text-gray-500 block mb-1">Item yang diperiksa:</span>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="item in viewed" :key="item">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="item"></span>
                                </template>
                                <span x-show="viewed.length === 0" class="text-[11px] text-gray-400 italic">Belum ada dokumen dibuka.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
