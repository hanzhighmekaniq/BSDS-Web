@php
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Ubah Kasus: {{ $case->code }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            @if ($sessionCount > 0)
                <div class="p-3 bg-bsds-orange/10 border border-bsds-orange/40 rounded-lg text-sm text-bsds-ink">Kasus ini sudah memiliki {{ $sessionCount }} sesi. Hindari mengubah isi yang memengaruhi data penelitian yang sedang dikumpulkan.</div>
            @endif

            <form method="POST" action="{{ route('admin.cases.update', $case) }}" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                @csrf @method('PUT')

                <label class="block text-sm font-semibold text-bsds-navy">Judul
                    <input type="text" name="title" value="{{ old('title', $case->title) }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Deskripsi
                    <textarea name="description" rows="3" required maxlength="2000" class="{{ $field }}">{{ old('description', $case->description) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Briefing
                    <textarea name="briefing_text" rows="5" maxlength="5000" class="{{ $field }}">{{ old('briefing_text', $case->briefing_text) }}</textarea>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="block text-sm font-semibold text-bsds-navy">Peran
                        <input type="text" name="role_title" value="{{ old('role_title', $case->role_title) }}" maxlength="100" class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Level
                        <select name="level" class="{{ $field }}">
                            @foreach (['foundation' => 'Foundation', 'developing' => 'Developing', 'challenge' => 'Challenge'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('level', $case->level) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Estimasi (mnt)
                        <input type="number" name="estimated_time" min="1" max="240" value="{{ old('estimated_time', $case->estimated_time) }}" required class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Urutan
                        <input type="number" name="sort_order" min="0" max="1000" value="{{ old('sort_order', $case->sort_order) }}" class="{{ $field }}">
                    </label>
                </div>
                <label class="flex items-center gap-2 text-sm text-bsds-ink">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $case->is_active)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Aktif (tampil di katalog siswa)
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.cases.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </form>

            <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-3">
                <h3 class="font-heading font-semibold text-bsds-navy">Validasi graf</h3>
                @forelse ($graphErrors as $error)
                    <p class="text-sm text-bsds-error">&bull; {{ $error }}</p>
                @empty
                    <p class="text-sm text-bsds-success font-semibold">Graf skenario valid (tanpa jalan buntu, yatim, atau siklus).</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                <h3 class="font-heading font-semibold text-bsds-navy">Skenario ({{ $scenarios->count() }})</h3>
                @foreach ($scenarios as $s)
                    <div class="border border-bsds-border rounded-xl p-4">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="px-2 py-0.5 rounded bg-bsds-navy text-white text-xs font-bold">{{ $s->step_code }}</span>
                            <span class="font-medium text-bsds-ink">{{ $s->title }}</span>
                            <span class="text-xs text-bsds-gray">{{ $s->scenario_type }}{{ $s->is_start ? ' • awal' : '' }}{{ $s->is_ending ? ' • ending' : '' }}</span>
                            <span class="text-xs text-bsds-gray ml-auto">{{ $s->investigationItems->count() }} info &bull; {{ $s->hints->count() }} hint</span>
                        </div>
                        @if ($s->options->isNotEmpty())
                            <ul class="mt-2 text-xs text-bsds-gray space-y-1">
                                @foreach ($s->options as $o)
                                    <li><span class="font-semibold text-bsds-ink">{{ $o->label }}</span> ({{ $o->consequence_type }}) &rarr; {{ $o->nextScenario?->step_code ?? '?' }} &mdash; {{ \Illuminate\Support\Str::limit($o->option_text, 80) }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
