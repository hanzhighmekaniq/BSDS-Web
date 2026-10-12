@php
    $editing = $user->exists;
    $currentRole = old('role', $user->roles->first()?->name ?? 'student');
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">{{ $editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}"
                x-data="{ role: '{{ $currentRole }}' }" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                @csrf
                @if ($editing) @method('PUT') @endif

                <label class="block text-sm font-semibold text-bsds-navy">Nama
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Email
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Kata sandi {{ $editing ? '(kosongkan jika tidak diubah)' : '' }}
                    <input type="password" name="password" autocomplete="new-password" @required(! $editing) class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Role
                    <select name="role" x-model="role" class="{{ $field }}">
                        @foreach (['student' => 'Siswa', 'researcher' => 'Peneliti', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div x-show="role === 'student'" x-cloak class="space-y-5 border-t border-bsds-border pt-5">
                    <label class="block text-sm font-semibold text-bsds-navy">NIS / Kode peserta
                        <input type="text" name="nis" value="{{ old('nis', $user->studentProfile?->nis) }}" maxlength="50" class="{{ $field }}">
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="block text-sm font-semibold text-bsds-navy">Kelas
                            <select name="classroom_id" class="{{ $field }}">
                                <option value="">-</option>
                                @foreach ($classrooms as $c)
                                    <option value="{{ $c->id }}" @selected(old('classroom_id', $user->studentProfile?->classroom_id) == $c->id)>{{ $c->name }} ({{ $c->academic_year }})</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-bsds-navy">No. absen
                            <input type="number" name="absence_no" min="1" max="200" value="{{ old('absence_no', $user->studentProfile?->absence_no) }}" class="{{ $field }}">
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </form>
        </div>
</x-app-layout>
