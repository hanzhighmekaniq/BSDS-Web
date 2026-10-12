@php
    $steps = [
        ['code' => '01', 'title' => 'Read', 'desc' => 'Baca situasi dengan teliti sebelum bertindak.'],
        ['code' => '02', 'title' => 'Investigate', 'desc' => 'Gunakan informasi yang tersedia (SOP, data transaksi, stok) untuk memahami kondisi.'],
        ['code' => '03', 'title' => 'Decide', 'desc' => 'Pilih tindakan yang menurut Anda paling sesuai dengan situasi.'],
        ['code' => '04', 'title' => 'Experience', 'desc' => 'Lihat konsekuensi dari keputusan yang Anda ambil.'],
        ['code' => '05', 'title' => 'Adapt', 'desc' => 'Hadapi masalah baru yang mungkin muncul akibat keputusan Anda.'],
        ['code' => '06', 'title' => 'Reflect', 'desc' => 'Evaluasi keputusan Anda di akhir simulasi.'],
    ];

    $features = [
        ['icon' => 'magnifying-glass', 'title' => 'Information', 'desc' => 'Periksa SOP, data transaksi, stok, dan informasi pelanggan sebelum memilih tindakan. Tujuannya agar Anda tidak asal memilih keputusan.'],
        ['icon' => 'light-bulb', 'title' => 'Hint', 'desc' => 'Jika bingung, gunakan hint bertahap. Hint tidak memberikan jawaban langsung, hanya mengarahkan cara berpikir Anda.'],
        ['icon' => 'rectangle-stack', 'title' => 'Decision Journey', 'desc' => 'Setelah kasus selesai, Anda dapat melihat seluruh perjalanan keputusan yang telah diambil.'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
            {{ __('Panduan') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8">
                <h3 class="font-heading font-semibold text-lg text-bsds-navy mb-6">How to Play</h3>

                <div class="space-y-5">
                    @foreach ($steps as $step)
                        <div class="flex items-start gap-4">
                            <span class="shrink-0 w-10 h-10 rounded-xl bg-bsds-navy text-white flex items-center justify-center font-heading font-semibold text-sm">
                                {{ $step['code'] }}
                            </span>
                            <div>
                                <h4 class="font-heading font-semibold text-bsds-navy">{{ $step['title'] }}</h4>
                                <p class="text-sm text-bsds-gray mt-0.5">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                        @unless ($loop->last)
                            <div class="ml-5 h-5 border-l-2 border-dashed border-bsds-border"></div>
                        @endunless
                    @endforeach
                </div>

                <div class="mt-6 rounded-xl border border-bsds-orange bg-bsds-orange/10 p-4">
                    <p class="text-sm text-bsds-ink">
                        <span class="font-semibold">Ingat:</span> bisa jadi ada lebih dari satu tindakan yang masuk akal. Yang terpenting adalah bagaimana Anda mempertimbangkan masalah, informasi, dan konsekuensinya.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8">
                <h3 class="font-heading font-semibold text-lg text-bsds-navy mb-6">Fitur yang Akan Anda Temui</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach ($features as $feature)
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-bsds-teal/10 text-bsds-teal flex items-center justify-center mb-3">
                                <x-dynamic-component :component="'heroicon-o-'.$feature['icon']" class="w-5 h-5" />
                            </div>
                            <h4 class="font-heading font-semibold text-bsds-navy text-sm">{{ $feature['title'] }}</h4>
                            <p class="text-xs text-bsds-gray mt-1 leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="min-h-[44px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
