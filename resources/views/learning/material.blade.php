<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('materials.index') }}" class="text-xs font-semibold text-bsds-teal hover:underline">&larr; Semua materi</a>
        <h2 class="mt-1 text-xl font-semibold leading-tight font-heading text-bsds-navy">{{ $material->title }}</h2>
    </x-slot>

    <div class="w-full space-y-6">
        <article class="p-6 bg-white border sm:p-8 rounded-2xl border-bsds-border">
            {{-- ponytail: teks polos dengan baris baru; ganti ke Markdown (Str::markdown) bila butuh format kaya. --}}
            <div class="text-sm leading-relaxed whitespace-pre-line sm:text-base text-bsds-ink">{{ $material->content }}</div>
        </article>

        @if ($quizzes->isNotEmpty())
            <div class="p-6 border bg-bsds-teal/10 border-bsds-teal/30 rounded-2xl">
                <h3 class="font-semibold font-heading text-bsds-navy">Uji pemahamanmu</h3>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach ($quizzes as $quiz)
                        <a href="{{ route('quizzes.show', $quiz) }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white transition rounded-xl bg-bsds-navy hover:bg-bsds-teal">{{ $quiz->title }}
                            &rarr;</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
