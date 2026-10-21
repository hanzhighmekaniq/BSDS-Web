@php
    $editing = $material->exists;
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">{{ $editing ? 'Ubah Materi' : 'Tambah Materi' }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
        @include('admin._messages')

        <form method="POST" action="{{ $editing ? route('admin.materials.update', $material) : route('admin.materials.store') }}" class="p-6 space-y-5 bg-white border rounded-2xl border-bsds-border">
            @csrf
            @if ($editing) @method('PUT') @endif

            <label class="block text-sm font-semibold text-bsds-navy">Judul
                <input type="text" name="title" value="{{ old('title', $material->title) }}" required maxlength="255" class="{{ $field }}">
            </label>
            <label class="block text-sm font-semibold text-bsds-navy">Ringkasan
                <textarea name="summary" rows="2" maxlength="1000" class="{{ $field }}">{{ old('summary', $material->summary) }}</textarea>
            </label>
            <label class="block text-sm font-semibold text-bsds-navy">Isi materi
                <textarea name="content" rows="16" required class="{{ $field }}">{{ old('content', $material->content) }}</textarea>
            </label>
            <div class="flex flex-wrap items-end gap-6">
                <label class="block text-sm font-semibold text-bsds-navy">Urutan
                    <input type="number" name="sort_order" min="0" max="1000" value="{{ old('sort_order', $material->sort_order) }}" class="{{ $field }} w-32">
                </label>
                <label class="flex items-center gap-2 pb-2 text-sm text-bsds-ink">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $material->is_published)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Tampilkan ke siswa
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.materials.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
            </div>
        </form>
    </div>
</x-app-layout>
