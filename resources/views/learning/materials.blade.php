<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight font-heading text-bsds-navy">Materi</h2>
        <p class="mt-1 text-sm text-bsds-gray">Pelajari materi sebelum mengerjakan quiz dan simulasi.</p>
    </x-slot>

    <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($materials as $i => $material)
            <a href="{{ route('materials.show', $material) }}"
                class="flex flex-col p-6 transition bg-white border rounded-2xl border-bsds-border hover:border-bsds-teal hover:shadow-md">
                <span class="text-xs font-semibold text-bsds-teal">Materi {{ $i + 1 }}</span>
                <h3 class="mt-1 font-semibold font-heading text-bsds-navy">{{ $material->title }}</h3>
                @if ($material->summary)
                    <p class="flex-1 mt-2 text-sm text-bsds-gray line-clamp-3">{{ $material->summary }}</p>
                @endif
                <span class="mt-4 text-xs font-semibold text-bsds-teal">Baca materi &rarr;</span>
            </a>
        @empty
            <div class="p-8 text-center bg-white border border-dashed col-span-full rounded-2xl text-bsds-gray border-bsds-border">
                Belum ada materi.</div>
        @endforelse
    </div>
</x-app-layout>
