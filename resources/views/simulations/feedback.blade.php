@php
    $categoryLabel = ['baik' => 'Baik', 'cukup' => 'Cukup', 'perlu_latihan' => 'Perlu latihan'];
    $categoryColor = ['baik' => 'bg-bsds-success', 'cukup' => 'bg-bsds-warning', 'perlu_latihan' => 'bg-bsds-orange'];
    $descriptions = [
        'Analyze' => [
            'baik' => 'Anda mampu mengidentifikasi masalah utama dan mengumpulkan informasi yang relevan sebelum bertindak.',
            'cukup' => 'Anda cukup mampu mengenali masalah dan mencari informasi, namun belum selalu menyentuh akar masalah atau informasi yang paling relevan.',
            'perlu_latihan' => 'Anda perlu lebih teliti mengenali masalah utama dan mengumpulkan informasi sebelum mengambil keputusan.',
        ],
        'Decide' => [
            'baik' => 'Anda mampu memilih tindakan berdasarkan informasi yang tersedia dan mempertimbangkan beberapa alternatif.',
            'cukup' => 'Anda sudah mempertimbangkan tindakan, namun belum selalu membandingkan alternatif secara menyeluruh.',
            'perlu_latihan' => 'Anda perlu membandingkan lebih banyak alternatif sebelum memilih tindakan.',
        ],
        'Adapt' => [
            'baik' => 'Anda mampu menyesuaikan tindakan ketika situasi berubah dan mengantisipasi dampak lanjutan.',
            'cukup' => 'Anda cukup mampu beradaptasi, namun respons terhadap situasi baru belum selalu tepat.',
            'perlu_latihan' => 'Anda masih kesulitan menyesuaikan tindakan ketika muncul masalah baru atau dampak lanjutan.',
        ],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">Feedback</span>
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">{{ $session->case->title }}</h2>
        </div>
    </x-slot>

    <div class="w-full space-y-6">

            @if (session('status'))
                <div class="p-3 bg-bsds-teal/10 border border-bsds-teal/30 text-bsds-ink rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            <p class="text-sm text-bsds-gray">Ringkasan keterampilan pengambilan keputusan Anda pada simulasi ini.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($groups as $group)
                    <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-heading font-semibold text-bsds-navy">{{ $group['icon'] }} {{ strtoupper($group['label']) }}</h3>
                            <span class="text-xs font-semibold text-bsds-navy">{{ $categoryLabel[$group['category']] ?? $group['category'] }}</span>
                        </div>

                        <div class="h-2 bg-bsds-border rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $categoryColor[$group['category']] ?? 'bg-bsds-gray' }}" style="width: {{ $group['percent'] }}%"></div>
                        </div>

                        <p class="text-sm text-bsds-ink leading-relaxed">{{ $descriptions[$group['label']][$group['category']] ?? '' }}</p>

                        @if (! empty($group['recommendations']))
                            <div class="pt-3 border-t border-bsds-border space-y-1.5">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray">Rekomendasi latihan</h4>
                                @foreach ($group['recommendations'] as $recommendation)
                                    <p class="text-xs text-bsds-gray">&bull; {{ $recommendation }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <a href="{{ route('simulations.journey', $session) }}" class="min-h-[48px] inline-flex items-center px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                    Lihat Hasil Lengkap &rarr;
                </a>
            </div>
        </div>
</x-app-layout>
