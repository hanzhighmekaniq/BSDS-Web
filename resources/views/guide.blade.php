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
        <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">
            {{ __('Panduan') }}
        </h2>
    </x-slot>

    <div class="w-full space-y-6">

            <div class="p-6 bg-white border rounded-2xl border-bsds-border md:p-8">
                <h3 class="mb-6 text-lg font-semibold font-heading text-bsds-navy">How to Play</h3>

                <div class="space-y-5">
                    @foreach ($steps as $step)
                        <div class="flex items-start gap-4">
                            <span class="flex items-center justify-center w-10 h-10 text-sm font-semibold text-white shrink-0 rounded-xl bg-bsds-navy font-heading">
                                {{ $step['code'] }}
                            </span>
                            <div>
                                <h4 class="font-semibold font-heading text-bsds-navy">{{ $step['title'] }}</h4>
                                <p class="text-sm text-bsds-gray mt-0.5">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                        @unless ($loop->last)
                            <div class="h-5 ml-5 border-l-2 border-dashed border-bsds-border"></div>
                        @endunless
                    @endforeach
                </div>

                <div class="p-4 mt-6 border rounded-xl border-bsds-orange bg-bsds-orange/10">
                    <p class="text-sm text-bsds-ink">
                        <span class="font-semibold">Ingat:</span> bisa jadi ada lebih dari satu tindakan yang masuk akal. Yang terpenting adalah bagaimana Anda mempertimbangkan masalah, informasi, dan konsekuensinya.
                    </p>
                </div>
            </div>

            <div class="p-6 bg-white border rounded-2xl border-bsds-border md:p-8">
                <h3 class="mb-6 text-lg font-semibold font-heading text-bsds-navy">Fitur yang Akan Anda Temui</h3>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    @foreach ($features as $feature)
                        <div>
                            <div class="flex items-center justify-center w-10 h-10 mb-3 rounded-xl bg-bsds-teal/10 text-bsds-teal">
                                <x-dynamic-component :component="'heroicon-o-'.$feature['icon']" class="w-5 h-5" />
                            </div>
                            <h4 class="text-sm font-semibold font-heading text-bsds-navy">{{ $feature['title'] }}</h4>
                            <p class="mt-1 text-xs leading-relaxed text-bsds-gray">{{ $feature['desc'] }}</p>
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
</x-app-layout>
