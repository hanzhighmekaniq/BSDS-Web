<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">Impor Siswa</h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full space-y-6">
            @include('admin._messages')

            @if ($report = session('import_report'))
                <div class="p-6 space-y-4 bg-white border-2 rounded-2xl border-bsds-teal">
                    <h3 class="font-semibold font-heading text-bsds-navy">Hasil impor: {{ count($report['created']) }} dibuat, {{ count($report['skipped']) }} dilewati</h3>

                    @if ($report['created'])
                        <p class="text-xs font-semibold text-bsds-warning">Catat/salin kata sandi berikut sekarang. Kata sandi tidak ditampilkan lagi setelah halaman ini ditutup.</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-xs tracking-wider uppercase text-bsds-gray"><tr><th class="py-2 text-left">Nama</th><th class="py-2 text-left">Email</th><th class="py-2 text-left">NIS</th><th class="py-2 text-left">Kata sandi</th></tr></thead>
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
                            <h4 class="mb-1 text-xs font-bold tracking-wider uppercase text-bsds-gray">Dilewati</h4>
                            @foreach ($report['skipped'] as $row)
                                <p class="text-sm text-bsds-ink">Baris {{ $row['line'] }} ({{ $row['name'] }}): {{ $row['reason'] }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="p-6 space-y-4 bg-white border rounded-2xl border-bsds-border">
                @csrf
                <p class="text-sm text-bsds-ink">Unggah file <strong>.csv</strong> atau <strong>.xlsx</strong> dengan baris pertama sebagai heading:</p>
                <code class="block p-3 text-xs bg-bsds-bg rounded-xl">name, email, nis, classroom, absence_no, password</code>
                <p class="text-xs text-bsds-gray">Kolom <em>classroom</em>, <em>absence_no</em>, dan <em>password</em> boleh kosong. Jika password kosong, sistem membuat kata sandi acak. Baris dengan email/NIS yang sudah ada dilewati.</p>
                <input type="file" name="file" accept=".csv,.xlsx" required class="block w-full text-sm">
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.users.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Kembali</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Impor</button>
                </div>
            </form>
        </div>
</x-app-layout>
