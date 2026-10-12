<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            <div class="flex flex-wrap items-center justify-between gap-3">
                <form action="{{ route('admin.cases.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <label class="cursor-pointer inline-flex items-center px-3 py-2 rounded-xl border border-bsds-border bg-white text-xs font-semibold text-bsds-ink hover:bg-bsds-bg transition">
                        <span>Pilih JSON Kasus</span>
                        <input type="file" name="json_file" accept=".json,application/json" class="sr-only" onchange="this.form.submit()">
                    </label>
                    <span class="text-xs text-bsds-gray">atau</span>
                </form>
                <a href="{{ route('admin.cases.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah Kasus</a>
            </div>

            <div class="bg-white rounded-2xl border border-bsds-border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-bsds-bg text-xs uppercase tracking-wider text-bsds-gray">
                        <tr><th class="px-4 py-3 text-left">Kasus</th><th class="px-4 py-3 text-left">Level</th><th class="px-4 py-3 text-left">Skenario</th><th class="px-4 py-3 text-left">Sesi</th><th class="px-4 py-3 text-left">Graf</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-bsds-border">
                        @foreach ($cases as $c)
                            <tr>
                                <td class="px-4 py-3"><div class="font-medium text-bsds-ink">{{ $c->title }}</div><div class="text-xs text-bsds-gray">{{ $c->code }}</div></td>
                                <td class="px-4 py-3">{{ $c->level }}</td>
                                <td class="px-4 py-3">{{ $c->scenarios_count }}</td>
                                <td class="px-4 py-3">{{ $c->simulation_sessions_count }}</td>
                                <td class="px-4 py-3">
                                    @if (empty($graphErrors[$c->id]))
                                        <span class="text-bsds-success font-semibold text-xs">Valid</span>
                                    @else
                                        <span class="text-bsds-error font-semibold text-xs">{{ count($graphErrors[$c->id]) }} masalah</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.cases.edit', $c) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.cases.duplicate', $c) }}" onsubmit="return confirm('Duplikasi kasus {{ $c->code }} beserta seluruh skenario dan opsinya?')">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-bsds-navy hover:underline">Duplikat</button>
                                        </form>
                                        <a href="{{ route('admin.cases.export', $c) }}" class="text-xs font-semibold text-bsds-gray hover:underline">Export</a>
                                        @if ($c->simulation_sessions_count === 0)
                                            <form method="POST" action="{{ route('admin.cases.destroy', $c) }}" onsubmit="return confirm('Hapus permanen kasus {{ $c->code }}? Skenario di dalamnya juga akan terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-bsds-error hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-bsds-gray">Klik "Ubah" pada sebuah kasus untuk mengelola skenario, opsi, item investigasi, hint, dan skor indikatornya.</p>
        </div>
</x-app-layout>
