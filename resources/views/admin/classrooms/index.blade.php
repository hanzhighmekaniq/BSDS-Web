<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            <div class="flex justify-end">
                <a href="{{ route('admin.classrooms.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah kelas</a>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-bsds-bg text-xs uppercase tracking-wider text-bsds-gray">
                        <tr><th class="px-4 py-3 text-left">Kelas</th><th class="px-4 py-3 text-left">Tahun ajaran</th><th class="px-4 py-3 text-left">Siswa</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-bsds-border">
                        @forelse ($classrooms as $c)
                            <tr>
                                <td class="px-4 py-3 font-medium text-bsds-ink">{{ $c->name }}</td>
                                <td class="px-4 py-3 text-bsds-gray">{{ $c->academic_year }}</td>
                                <td class="px-4 py-3">{{ $c->student_profiles_count }}</td>
                                <td class="px-4 py-3">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.classrooms.edit', $c) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('admin.classrooms.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus kelas ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-bsds-error hover:underline ml-3">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-bsds-gray">Belum ada kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
