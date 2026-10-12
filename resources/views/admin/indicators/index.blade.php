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

            <div class="p-3 bg-bsds-orange/10 border border-bsds-orange/40 rounded-lg text-sm text-bsds-ink">Indikator saat ini bersifat sementara. Sesuaikan nama dan umpan baliknya dengan indikator final pada Bab III sebelum pengambilan data.</div>

            <div class="bg-white rounded-2xl border border-bsds-border overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-bsds-bg text-xs uppercase tracking-wider text-bsds-gray">
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
    </div>
</x-app-layout>
