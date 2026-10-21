@php
    $linkClass = fn (bool $active) => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition '
        .($active ? 'bg-bsds-navy text-white' : 'text-bsds-gray hover:bg-bsds-bg hover:text-bsds-navy');
@endphp
<div class="flex flex-col h-full">
    <a href="{{ route('dashboard') }}" class="h-16 flex items-center gap-2.5 px-5 border-b border-bsds-border shrink-0">
        <x-application-logo class="h-8 w-auto text-bsds-navy shrink-0" />
        <span class="font-heading font-semibold text-bsds-navy leading-none">BSDS</span>
    </a>

    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="{{ $linkClass(request()->routeIs('dashboard')) }}">
            <x-heroicon-o-home class="w-5 h-5 shrink-0" />
            {{ __('Dashboard') }}
        </a>

        @role('student')
            <a href="{{ route('materials.index') }}" class="{{ $linkClass(request()->routeIs('materials.*')) }}">
                <x-heroicon-o-academic-cap class="w-5 h-5 shrink-0" />
                Materi
            </a>
            <a href="{{ route('quizzes.index') }}" class="{{ $linkClass(request()->routeIs('quizzes.*')) }}">
                <x-heroicon-o-clipboard-document-check class="w-5 h-5 shrink-0" />
                Quiz
            </a>
            <a href="{{ route('simulations.index') }}" class="{{ $linkClass(request()->routeIs('simulations.*')) }}">
                <x-heroicon-o-puzzle-piece class="w-5 h-5 shrink-0" />
                {{ __('Simulasi') }}
            </a>
            <a href="{{ route('progress') }}" class="{{ $linkClass(request()->routeIs('progress')) }}">
                <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />
                {{ __('Progress') }}
            </a>
            <a href="{{ route('guide') }}" class="{{ $linkClass(request()->routeIs('guide')) }}">
                <x-heroicon-o-book-open class="w-5 h-5 shrink-0" />
                {{ __('Panduan') }}
            </a>
        @endrole

        @role('researcher|admin')
            <a href="{{ route('research.index') }}" class="{{ $linkClass(request()->routeIs('research.*')) }}">
                <x-heroicon-o-chart-bar class="w-5 h-5 shrink-0" />
                {{ __('Analitik Penelitian') }}
            </a>
        @endrole

        @role('admin')
            <a href="{{ route('admin.users.index') }}" class="{{ $linkClass(request()->routeIs('admin.*')) }}">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />
                {{ __('Admin') }}
            </a>
        @endrole
    </nav>

    <div class="border-t border-bsds-border p-4 shrink-0">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-9 h-9 rounded-full bg-bsds-navy text-white flex items-center justify-center text-sm font-bold shrink-0">
                {{ Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-bsds-navy truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-bsds-gray truncate capitalize">{{ auth()->user()->getRoleNames()->first() ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('profile.edit') }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-bsds-navy hover:bg-bsds-bg transition">
                <x-heroicon-o-user-circle class="w-4 h-4" /> {{ __('Profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-bsds-error hover:bg-bsds-error/10 transition">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
