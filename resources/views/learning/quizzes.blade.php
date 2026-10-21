<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">Quiz</h2>
        <p class="mt-1 text-sm text-bsds-gray">Kerjakan quiz untuk menguji pemahaman materi.</p>
    </x-slot>

    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($quizzes as $quiz)
            <div class="flex flex-col p-6 bg-white border rounded-2xl border-bsds-border">
                @if ($quiz->material)
                    <span class="text-xs font-semibold text-bsds-teal">{{ $quiz->material->title }}</span>
                @endif
                <h3 class="mt-1 font-semibold font-heading text-bsds-navy">{{ $quiz->title }}</h3>
                @if ($quiz->description)
                    <p class="mt-2 text-sm text-bsds-gray">{{ $quiz->description }}</p>
                @endif
                <div class="flex items-center justify-between pt-3 mt-auto text-xs border-t text-bsds-gray border-bsds-border">
                    <span>{{ $quiz->questions_count }} soal</span>
                    @isset($best[$quiz->id])
                        <span class="font-semibold text-bsds-success">Skor terbaik: {{ $best[$quiz->id] }}</span>
                    @endisset
                </div>
                <a href="{{ route('quizzes.show', $quiz) }}"
                    class="inline-flex justify-center items-center mt-4 min-h-[44px] px-4 bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold rounded-xl transition">
                    {{ isset($best[$quiz->id]) ? 'Kerjakan Lagi' : 'Mulai Quiz' }}
                </a>
            </div>
        @empty
            <div class="p-8 text-center bg-white border border-dashed col-span-full rounded-2xl text-bsds-gray border-bsds-border">
                Belum ada quiz.</div>
        @endforelse
    </div>
</x-app-layout>
