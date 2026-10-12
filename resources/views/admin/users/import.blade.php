<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">Impor Siswa</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            @if ($report = session('import_report'))
                <div class="bg-white rounded-2xl border-2 border-bsds-teal p-6 space-y-4">
                    <h3 class="font-heading font-semibold text-bsds-navy">Hasil impor: {{ count($report['created']) }} dibuat, {{ count($report['skipped']) }} dilewati</h3>

                    @if ($report['created'])
                        <p class="text-xs text-bsds-warning font-semibold">Catat/salin kata sandi berikut sekarang. Kata sandi tidak ditampilkan lagi setelah halaman ini ditutup.</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-xs uppercase tracking-wider text-bsds-gray"><tr><th class="py-2 text-left">Nama</th><th class="py-2 text-left">Email</th><th class="py-2 text-left">NIS</th><th class="py-2 text-left">Kata sandi</th></tr></thead>
                                <tbody class="divide-y divide-bsds-border">
                                    @foreach ($report['created'] as $row)
                                        <tr><td class="py-2 pr-3">{{ $row['name'] }}</td><td class="py-2 pr-3">{{ $row['email'] }}</td><td class="py-2 pr-3">{{ $row['nis'] }}</td><td class="py-2 font-mono">{{ $row['password'] }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($report['skipped'])
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-1">Dilewati</h4>
                            @foreach ($report['skipped'] as $row)
                                <p class="text-sm text-bsds-ink">Baris {{ $row['line'] }} ({{ $row['name'] }}): {{ $row['reason'] }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                @csrf
                <p class="text-sm text-bsds-ink">Unggah file <strong>.csv</strong> atau <strong>.xlsx</strong> dengan baris pertama sebagai heading:</p>
                <code class="block text-xs bg-bsds-bg rounded-xl p-3">name, email, nis, classroom, absence_no, password</code>
                <p class="text-xs text-bsds-gray">Kolom <em>classroom</em>, <em>absence_no</em>, dan <em>password</em> boleh kosong. Jika password kosong, sistem membuat kata sandi acak. Baris dengan email/NIS yang sudah ada dilewati.</p>
                <input type="file" name="file" accept=".csv,.xlsx" required class="block w-full text-sm">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Kembali</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Impor</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
