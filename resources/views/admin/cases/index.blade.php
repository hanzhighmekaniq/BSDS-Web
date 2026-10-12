<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

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
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.cases.edit', $c) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-bsds-gray">Isi skenario (opsi, konsekuensi, hint, skor) dikelola lewat seeder. Panel ini mengubah metadata kasus dan menampilkan validasi graf.</p>
        </div>
    </div>
</x-app-layout>
