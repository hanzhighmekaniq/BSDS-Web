@php
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">
                {{ $case->exists ? 'Ubah Kasus: '.$case->code : 'Tambah Kasus Baru' }}
            </h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            @if ($sessionCount > 0)
                <div class="p-3 text-sm border rounded-lg bg-bsds-orange/10 border-bsds-orange/40 text-bsds-ink">Kasus ini sudah memiliki {{ $sessionCount }} sesi. Hindari mengubah isi yang memengaruhi data penelitian yang sedang dikumpulkan.</div>
            @endif

            <form method="POST" action="{{ $case->exists ? route('admin.cases.update', $case) : route('admin.cases.store') }}" class="p-6 space-y-5 bg-white border rounded-2xl border-bsds-border">
                @csrf
                @if ($case->exists) @method('PUT') @endif

                @if (! $case->exists)
                    <label class="block text-sm font-semibold text-bsds-navy">Kode Kasus
                        <input type="text" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="CASE-05" class="{{ $field }} font-mono uppercase">
                    </label>
                    <p class="-mt-3 text-xs text-bsds-gray">Huruf besar, angka, strip, atau underscore. Tidak dapat diubah setelah dibuat.</p>
                @else
                    <p class="text-xs text-bsds-gray">Kode: <span class="font-mono font-semibold text-bsds-ink">{{ $case->code }}</span> (tidak dapat diubah)</p>
                @endif

                <label class="block text-sm font-semibold text-bsds-navy">Judul
                    <input type="text" name="title" value="{{ old('title', $case->title) }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Deskripsi
                    <textarea name="description" rows="3" required maxlength="2000" class="{{ $field }}">{{ old('description', $case->description) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Briefing
                    <textarea name="briefing_text" rows="5" maxlength="5000" class="{{ $field }}">{{ old('briefing_text', $case->briefing_text) }}</textarea>
                </label>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
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

                @if ($case->exists)
                    <label class="flex items-center gap-2 text-sm text-bsds-ink">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $case->is_active)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Aktif (tampil di katalog siswa)
                    </label>
                @endif

                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-3">
                        @if ($case->exists)
                            <a href="{{ route('admin.cases.export', $case) }}" class="text-xs font-semibold text-bsds-gray hover:underline">Download JSON</a>
                        @endif
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.cases.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                        <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                    </div>
                </div>
            </form>

            @if ($case->exists)
                <div class="p-6 space-y-3 bg-white border rounded-2xl border-bsds-border">
                    <h3 class="font-semibold font-heading text-bsds-navy">Validasi graf</h3>
                    @forelse ($graphErrors as $error)
                        <p class="text-sm text-bsds-error">&bull; {{ $error }}</p>
                    @empty
                        <p class="text-sm font-semibold text-bsds-success">Graf skenario valid (tanpa jalan buntu, yatim, atau siklus).</p>
                    @endforelse
                </div>

                <div class="p-6 space-y-4 bg-white border rounded-2xl border-bsds-border">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold font-heading text-bsds-navy">Skenario ({{ $scenarios->count() }})</h3>
                        <a href="{{ route('admin.cases.scenarios.create', $case) }}" class="text-xs font-semibold text-bsds-teal hover:underline">+ Tambah Skenario</a>
                    </div>
                    @foreach ($scenarios as $s)
                        <div class="p-4 border border-bsds-border rounded-xl">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="px-2 py-0.5 rounded bg-bsds-navy text-white text-xs font-bold">{{ $s->step_code }}</span>
                                <span class="font-medium text-bsds-ink">{{ $s->title }}</span>
                                <span class="text-xs text-bsds-gray">{{ $s->scenario_type }}{{ $s->is_start ? ' • awal' : '' }}{{ $s->is_ending ? ' • ending' : '' }}</span>
                                <span class="ml-auto text-xs text-bsds-gray">{{ $s->investigationItems->count() }} info &bull; {{ $s->hints->count() }} hint</span>
                                <a href="{{ route('admin.cases.scenarios.edit', [$case, $s]) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                            </div>
                            @if ($s->options->isNotEmpty())
                                <ul class="mt-2 space-y-1 text-xs text-bsds-gray">
                                    @foreach ($s->options as $o)
                                        <li><span class="font-semibold text-bsds-ink">{{ $o->label }}</span> ({{ $o->consequence_type }}) &rarr; {{ $o->nextScenario?->step_code ?? '?' }} &mdash; {{ \Illuminate\Support\Str::limit($o->option_text, 80) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                    @if ($scenarios->isEmpty())
                        <p class="py-4 text-sm text-center text-bsds-gray">Belum ada skenario. Tambahkan minimal satu skenario awal (is_start) dan satu skenario akhir (is_ending) agar kasus ini bisa diaktifkan.</p>
                    @endif
                </div>
            @endif
        </div>
</x-app-layout>
