@php
    $editing = $classroom->exists;
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">{{ $editing ? 'Ubah Kelas' : 'Tambah Kelas' }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            <form method="POST" action="{{ $editing ? route('admin.classrooms.update', $classroom) : route('admin.classrooms.store') }}" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                @csrf
                @if ($editing) @method('PUT') @endif

                <label class="block text-sm font-semibold text-bsds-navy">Nama kelas
                    <input type="text" name="name" value="{{ old('name', $classroom->name) }}" required maxlength="100" placeholder="XI MPLB 1" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Tahun ajaran
                    <input type="text" name="academic_year" value="{{ old('academic_year', $classroom->academic_year) }}" required maxlength="20" placeholder="2026/2027" class="{{ $field }}">
                </label>
                <label class="flex items-center gap-2 text-sm text-bsds-ink">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $classroom->is_active)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Aktif
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.classrooms.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
