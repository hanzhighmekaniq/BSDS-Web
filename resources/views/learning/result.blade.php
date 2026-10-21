<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('quizzes.index') }}" class="text-xs font-semibold text-bsds-teal hover:underline">&larr; Semua quiz</a>
        <h2 class="mt-1 text-xl font-semibold leading-tight font-heading text-bsds-navy">Hasil: {{ $quiz->title }}</h2>
    </x-slot>

    <div class="w-full space-y-4">
        <div class="p-6 text-center text-white bg-bsds-navy rounded-2xl">
            <div class="text-5xl font-extrabold font-heading">{{ $attempt->score }}</div>
            <p class="mt-2 text-sm text-white/80">{{ $attempt->correct }} dari {{ $attempt->total }} jawaban benar</p>
            <a href="{{ route('quizzes.show', $quiz) }}"
                class="inline-flex items-center mt-4 px-5 py-2.5 rounded-xl bg-white text-bsds-navy text-sm font-semibold hover:bg-bsds-bg transition">Kerjakan
                Lagi</a>
        </div>

        @foreach ($quiz->questions as $n => $q)
            @php
                $picked = $attempt->answers[$q->id] ?? null;
                $ok = $picked === $q->answer;
            @endphp
            <div class="p-5 bg-white border-2 rounded-2xl {{ $ok ? 'border-bsds-success/40' : 'border-bsds-error/40' }}">
                <p class="font-medium text-bsds-navy">{{ $n + 1 }}. {{ $q->question }}</p>
                <p class="mt-2 text-sm {{ $ok ? 'text-bsds-success' : 'text-bsds-error' }}">
                    Jawabanmu: {{ $picked ? $picked.'. '.($q->options[$picked] ?? '') : '-' }}
                </p>
                @unless ($ok)
                    <p class="mt-1 text-sm text-bsds-ink">Jawaban benar: {{ $q->answer }}. {{ $q->options[$q->answer] ?? '' }}</p>
                @endunless
            </div>
        @endforeach
    </div>
</x-app-layout>
