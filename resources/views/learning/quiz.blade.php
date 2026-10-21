<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('quizzes.index') }}" class="text-xs font-semibold text-bsds-teal hover:underline">&larr; Semua quiz</a>
        <h2 class="mt-1 text-xl font-semibold leading-tight font-heading text-bsds-navy">{{ $quiz->title }}</h2>
    </x-slot>

    <form method="POST" action="{{ route('quizzes.submit', $quiz) }}" class="w-full space-y-4">
        @csrf
        <x-input-error :messages="$errors->all()" />

        @foreach ($quiz->questions as $n => $q)
            <fieldset class="p-6 bg-white border rounded-2xl border-bsds-border">
                <legend class="sr-only">Soal {{ $n + 1 }}</legend>
                <p class="font-medium text-bsds-navy"><span class="text-bsds-teal">{{ $n + 1 }}.</span> {{ $q->question }}</p>
                <div class="mt-4 space-y-2">
                    @foreach ($q->options as $key => $text)
                        <label
                            class="flex items-start gap-3 p-3 border-2 cursor-pointer rounded-xl border-bsds-border hover:border-bsds-teal has-[:checked]:border-bsds-teal has-[:checked]:bg-[#E8F6F6]">
                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $key }}" required
                                @checked(old("answers.$q->id") === $key)
                                class="mt-0.5 text-bsds-teal focus:ring-bsds-teal border-bsds-border">
                            <span class="text-sm text-bsds-ink"><strong>{{ $key }}.</strong> {{ $text }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div class="flex justify-end">
            <button class="min-h-[48px] px-8 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">
                Kumpulkan Jawaban
            </button>
        </div>
    </form>
</x-app-layout>
