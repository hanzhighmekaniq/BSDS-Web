@php
    $questions = [
        'q1_reason' => ['Mengapa Anda memilih keputusan pertama Anda?', 'Jelaskan dasar pertimbangan Anda.'],
        'q2_hardest_decision' => ['Keputusan mana yang paling sulit?', 'Ceritakan mengapa keputusan itu terasa sulit.'],
        'q3_unexpected_consequence' => ['Apakah ada konsekuensi yang tidak Anda perkirakan?', 'Uraikan apa yang terjadi di luar dugaan Anda.'],
        'q4_future_action' => ['Jika menghadapi situasi yang sama di dunia kerja, apa yang akan Anda lakukan?', 'Sebutkan tindakan yang akan Anda pertahankan atau ubah.'],
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">Reflect</span>
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">Reflect on Your Experience</h2>
            <p class="text-sm text-bsds-gray mt-0.5">Kasus: {{ $session->case->title }}</p>
        </div>
    </x-slot>

    <div class="w-full space-y-6">

            @if ($errors->any())
                <div class="p-3 bg-amber-50 border border-bsds-warning text-bsds-ink rounded-lg text-sm">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('simulations.reflection.store', $session) }}" class="bg-white rounded-2xl border border-bsds-border p-6 md:p-8 space-y-6">
                @csrf
                <p class="text-sm text-bsds-gray">Take a moment to think about your decisions. Tidak ada jawaban benar atau salah.</p>

                @foreach ($questions as $field => [$label, $help])
                    <div class="space-y-2">
                        <label for="{{ $field }}" class="block text-sm font-semibold text-bsds-navy">
                            <span class="text-bsds-teal mr-1">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span> {{ $label }}
                        </label>
                        <p class="text-xs text-bsds-gray">{{ $help }}</p>
                        <textarea id="{{ $field }}" name="{{ $field }}" rows="4" required minlength="5" maxlength="2000"
                            class="w-full rounded-xl border-bsds-border shadow-sm focus:border-bsds-teal focus:ring-bsds-teal text-sm">{{ old($field) }}</textarea>
                    </div>
                @endforeach

                <div class="flex justify-end pt-2">
                    <button type="submit" class="min-h-[48px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                        Submit Reflection &rarr;
                    </button>
                </div>
            </form>
        </div>
</x-app-layout>
