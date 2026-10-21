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
            <a href="{{ route('admin.quizzes.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah quiz</a>
        </div>

        <div class="overflow-x-auto bg-white border rounded-2xl border-bsds-border">
            <table class="min-w-full text-sm">
                <thead class="text-xs tracking-wider uppercase bg-bsds-bg text-bsds-gray">
                    <tr><th class="px-4 py-3 text-left">Judul</th><th class="px-4 py-3 text-left">Materi</th><th class="px-4 py-3 text-left">Soal</th><th class="px-4 py-3 text-left">Dikerjakan</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-bsds-border">
                    @forelse ($quizzes as $q)
                        <tr>
                            <td class="px-4 py-3 font-medium text-bsds-ink">{{ $q->title }}</td>
                            <td class="px-4 py-3 text-bsds-gray">{{ $q->material?->title ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $q->questions_count }}</td>
                            <td class="px-4 py-3">{{ $q->attempts_count }}x</td>
                            <td class="px-4 py-3">{{ $q->is_published ? 'Tampil' : 'Draft' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.quizzes.edit', $q) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('admin.quizzes.destroy', $q) }}" class="inline" onsubmit="return confirm('Hapus quiz ini beserta seluruh hasil pengerjaannya?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-3 text-xs font-semibold text-bsds-error hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-bsds-gray">Belum ada quiz.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
