<x-app-layout>
    <x-slot name="header">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                Refleksi Mandiri Simulasi
            </span>
            <h2 class="font-bold text-xl text-gray-900 leading-tight mt-1">
                Evaluasi Kritis & Pembelajaran Diri
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">Kasus: {{ $session->case->title }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('consequence'))
                <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg shadow-sm">
                    <h4 class="text-sm font-bold text-blue-900">Hasil Akhir Skenario:</h4>
                    <p class="text-sm text-blue-800 mt-1">{{ session('consequence') }}</p>
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h3 class="text-base font-bold text-gray-900">Lembar Refleksi Pengambilan Keputusan</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Jawab ketiga pertanyaan reflektif berikut secara jujur dan mendalam untuk menilai proses pertimbangan logis Anda selama menghadapi krisis layanan pelanggan.
                    </p>
                </div>

                <form method="POST" action="{{ route('simulations.reflection.store', $session) }}" class="space-y-6">
                    @csrf

                    {{-- Pertanyaan 1 --}}
                    <div class="space-y-2">
                        <label for="q1_reason" class="block text-sm font-semibold text-gray-800">
                            1. Mengapa Anda mengambil pilihan-pilihan keputusan tersebut selama simulasi berlangsung?
                        </label>
                        <p class="text-xs text-gray-500">Jelaskan dasar pertimbangan Anda (misal: empati pada konsumen, kepatuhan SOP, atau batasan sumber daya).</p>
                        <textarea id="q1_reason" name="q1_reason" rows="4" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Tuliskan analisis pertimbangan Anda di sini...">{{ old('q1_reason') }}</textarea>
                    </div>

                    {{-- Pertanyaan 2 --}}
                    <div class="space-y-2">
                        <label for="q2_unexpected_consequence" class="block text-sm font-semibold text-gray-800">
                            2. Apakah ada konsekuensi dari keputusan Anda yang terjadi di luar dugaan awal Anda?
                        </label>
                        <p class="text-xs text-gray-500">Uraikan apakah reaksi pelanggan atau situasi cabang toko berjalan sesuai perkiraan Anda atau justru mengejutkan.</p>
                        <textarea id="q2_unexpected_consequence" name="q2_unexpected_consequence" rows="4" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Tuliskan evaluasi konsekuensi tak terduga...">{{ old('q2_unexpected_consequence') }}</textarea>
                    </div>

                    {{-- Pertanyaan 3 --}}
                    <div class="space-y-2">
                        <label for="q3_future_action" class="block text-sm font-semibold text-gray-800">
                            3. Apa yang akan Anda lakukan secara berbeda jika menghadapi situasi serupa di dunia nyata kelak?
                        </label>
                        <p class="text-xs text-gray-500">Sebutkan strategi perbaikan atau keterampilan komunikasi yang ingin Anda optimalkan.</p>
                        <textarea id="q3_future_action" name="q3_future_action" rows="4" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Tuliskan rencana tindakan perbaikan masa depan...">{{ old('q3_future_action') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400">Semua jawaban akan direkam ke riwayat Decision Journey Anda.</span>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm transition">
                            Simpan Refleksi & Selesaikan Simulasi &rarr;
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
