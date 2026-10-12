<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            <div class="flex justify-end">
                <a href="{{ route('admin.classrooms.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah kelas</a>
            </div>

            <div class="overflow-x-auto bg-white border rounded-2xl border-bsds-border">
                <table class="min-w-full text-sm">
                    <thead class="text-xs tracking-wider uppercase bg-bsds-bg text-bsds-gray">
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
                                        <button class="ml-3 text-xs font-semibold text-bsds-error hover:underline">Hapus</button>
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
</x-app-layout>
