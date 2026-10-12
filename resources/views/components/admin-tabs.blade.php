@php
    $tabs = [
        ['admin.users.*', 'admin.users.index', 'Pengguna'],
        ['admin.classrooms.*', 'admin.classrooms.index', 'Kelas'],
        ['admin.cases.*', 'admin.cases.index', 'Kasus'],
        ['admin.indicators.*', 'admin.indicators.index', 'Indikator'],
    ];
@endphp
<nav class="flex flex-wrap gap-2" aria-label="Menu admin">
    @foreach ($tabs as [$pattern, $route, $label])
        <a href="{{ route($route) }}"
            class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request()->routeIs($pattern) ? 'bg-bsds-navy text-white' : 'bg-white border border-bsds-border text-bsds-navy hover:border-bsds-teal' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
