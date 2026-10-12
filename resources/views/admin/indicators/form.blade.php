@php
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
    $templates = $indicator->feedbackTemplates->keyBy('category');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Ubah Indikator: {{ $indicator->code }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            <form method="POST" action="{{ route('admin.indicators.update', $indicator) }}" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                @csrf @method('PUT')

                <label class="block text-sm font-semibold text-bsds-navy">Nama indikator
                    <input type="text" name="name" value="{{ old('name', $indicator->name) }}" required maxlength="150" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Deskripsi
                    <textarea name="description" rows="2" maxlength="1000" class="{{ $field }}">{{ old('description', $indicator->description) }}</textarea>
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="block text-sm font-semibold text-bsds-navy">Urutan
                        <input type="number" name="sort_order" min="0" max="100" value="{{ old('sort_order', $indicator->sort_order) }}" class="{{ $field }}">
                    </label>
                    <label class="flex items-end gap-2 text-sm text-bsds-ink pb-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $indicator->is_active)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Aktif
                    </label>
                </div>

                <div class="border-t border-bsds-border pt-5 space-y-5">
                    <h3 class="font-heading font-semibold text-bsds-navy">Umpan balik per kategori</h3>
                    @foreach ($categories as $key => $label)
                        <fieldset class="space-y-2">
                            <legend class="text-xs font-bold uppercase tracking-wider text-bsds-gray">{{ $label }}</legend>
                            <textarea name="feedback[{{ $key }}][feedback_text]" rows="2" maxlength="1000" placeholder="Umpan balik" class="{{ $field }}">{{ old("feedback.$key.feedback_text", $templates[$key]->feedback_text ?? '') }}</textarea>
                            <textarea name="feedback[{{ $key }}][recommendation_text]" rows="2" maxlength="1000" placeholder="Rekomendasi" class="{{ $field }}">{{ old("feedback.$key.recommendation_text", $templates[$key]->recommendation_text ?? '') }}</textarea>
                        </fieldset>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.indicators.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
