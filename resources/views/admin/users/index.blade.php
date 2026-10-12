<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            <div class="flex flex-wrap items-end justify-between gap-3">
                <form method="GET" class="flex flex-wrap gap-2 items-end">
                    <label class="text-xs font-semibold text-bsds-gray">Cari
                        <input type="text" name="q" value="{{ $search }}" placeholder="Nama, email, atau NIS" class="mt-1 block rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal">
                    </label>
                    <label class="text-xs font-semibold text-bsds-gray">Role
                        <select name="role" class="mt-1 block rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal">
                            <option value="">Semua</option>
                            @foreach (['student' => 'Siswa', 'researcher' => 'Peneliti', 'admin' => 'Admin'] as $value => $label)
                                <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="min-h-[40px] px-4 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition">Cari</button>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.import.form') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl border-2 border-bsds-navy text-bsds-navy hover:bg-bsds-navy hover:text-white text-sm font-semibold transition">Impor siswa</a>
                    <a href="{{ route('admin.users.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah pengguna</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-bsds-bg text-xs uppercase tracking-wider text-bsds-gray">
                        <tr><th class="px-4 py-3 text-left">Nama</th><th class="px-4 py-3 text-left">Email</th><th class="px-4 py-3 text-left">Role</th><th class="px-4 py-3 text-left">NIS / Kelas</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-bsds-border">
                        @forelse ($users as $u)
                            <tr>
                                <td class="px-4 py-3 font-medium text-bsds-ink">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-bsds-gray">{{ $u->email }}</td>
                                <td class="px-4 py-3">{{ $u->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                                <td class="px-4 py-3 text-bsds-gray">{{ $u->studentProfile ? $u->studentProfile->nis.' / '.($u->studentProfile->classroom?->name ?? '-') : '-' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                                    @unless ($u->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs font-semibold text-bsds-error hover:underline ml-3">Hapus</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-bsds-gray">Tidak ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
</x-app-layout>
