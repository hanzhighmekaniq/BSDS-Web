<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">Administrasi</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            <div class="p-3 text-sm border rounded-lg bg-bsds-orange/10 border-bsds-orange/40 text-bsds-ink">Indikator saat ini bersifat sementara. Sesuaikan nama dan umpan baliknya dengan indikator final pada Bab III sebelum pengambilan data.</div>

            <div class="overflow-x-auto bg-white border rounded-2xl border-bsds-border">
                <table class="min-w-full text-sm">
                    <thead class="text-xs tracking-wider uppercase bg-bsds-bg text-bsds-gray">
                        <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">Kode</th><th class="px-4 py-3 text-left">Indikator</th><th class="px-4 py-3 text-left">Hasil tercatat</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-bsds-border">
                        @foreach ($indicators as $i)
                            <tr>
                                <td class="px-4 py-3">{{ $i->sort_order }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $i->code }}</td>
                                <td class="px-4 py-3 text-bsds-ink">{{ $i->name }}</td>
                                <td class="px-4 py-3">{{ $i->session_results_count }}</td>
                                <td class="px-4 py-3">{{ $i->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-4 py-3 text-right"><a href="{{ route('admin.indicators.edit', $i) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</x-app-layout>
