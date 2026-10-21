@php
    $editing = $quiz->exists;
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
    $keys = \App\Models\QuizQuestion::KEYS;
    $blank = ['question' => '', 'options' => array_fill_keys($keys, ''), 'answer' => 'A'];
    $questions = old('questions', $editing ? $quiz->questions->map->only(['question', 'options', 'answer'])->all() : [$blank]);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">{{ $editing ? 'Ubah Quiz' : 'Tambah Quiz' }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
        @include('admin._messages')

        <form method="POST" action="{{ $editing ? route('admin.quizzes.update', $quiz) : route('admin.quizzes.store') }}"
            x-data="{ questions: {{ Js::from(array_values($questions)) }}, blank: {{ Js::from($blank) }} }" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="p-6 space-y-5 bg-white border rounded-2xl border-bsds-border">
                <label class="block text-sm font-semibold text-bsds-navy">Judul
                    <input type="text" name="title" value="{{ old('title', $quiz->title) }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Deskripsi
                    <textarea name="description" rows="2" maxlength="1000" class="{{ $field }}">{{ old('description', $quiz->description) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Terkait materi
                    <select name="material_id" class="{{ $field }}">
                        <option value="">- Tidak terkait -</option>
                        @foreach ($materials as $m)
                            <option value="{{ $m->id }}" @selected(old('material_id', $quiz->material_id) == $m->id)>{{ $m->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex items-center gap-2 text-sm text-bsds-ink">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $quiz->is_published)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Tampilkan ke siswa
                </label>
                @if ($editing && $quiz->attempts()->exists())
                    <p class="text-xs text-bsds-warning">Quiz ini sudah pernah dikerjakan. Mengubah soal tidak mengubah skor lama, tetapi rincian jawaban lama tidak akan cocok dengan soal baru.</p>
                @endif
            </div>

            <template x-for="(q, i) in questions" :key="i">
                <div class="p-6 space-y-3 bg-white border rounded-2xl border-bsds-border">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold font-heading text-bsds-navy" x-text="'Soal ' + (i + 1)"></h3>
                        <button type="button" x-show="questions.length > 1" @click="questions.splice(i, 1)" class="text-xs font-semibold text-bsds-error hover:underline">Hapus soal</button>
                    </div>
                    <textarea :name="`questions[${i}][question]`" x-model="q.question" rows="2" required placeholder="Tulis pertanyaan" class="{{ $field }}"></textarea>
                    @foreach ($keys as $k)
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-bsds-gray shrink-0" title="Tandai sebagai jawaban benar">
                                <input type="radio" :name="`questions[${i}][answer]`" value="{{ $k }}" x-model="q.answer" class="text-bsds-teal focus:ring-bsds-teal"> {{ $k }}
                            </label>
                            <input type="text" :name="`questions[${i}][options][{{ $k }}]`" x-model="q.options['{{ $k }}']" required maxlength="500" placeholder="Pilihan {{ $k }}" class="{{ $field }} !mt-0">
                        </div>
                    @endforeach
                    <p class="text-xs text-bsds-gray">Pilih bulatan di kiri untuk menandai jawaban benar.</p>
                </div>
            </template>

            <div class="flex flex-wrap justify-between gap-3">
                <button type="button" @click="questions.push(JSON.parse(JSON.stringify(blank)))" class="min-h-[44px] px-4 rounded-xl border border-bsds-border bg-white text-bsds-navy text-sm font-semibold hover:border-bsds-teal">+ Tambah soal</button>
                <div class="flex gap-3">
                    <a href="{{ route('admin.quizzes.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
