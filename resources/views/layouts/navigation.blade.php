<!-- Desktop sidebar -->
<aside class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:w-64 bg-white border-r border-bsds-border z-30">
    <x-sidebar-nav />
</aside>

<!-- Mobile top bar -->
<div class="lg:hidden sticky top-0 z-30 bg-white border-b border-bsds-border h-16 flex items-center justify-between px-4">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <x-application-logo class="h-7 w-auto text-bsds-navy" />
        <span class="font-heading font-semibold text-bsds-navy">BSDS</span>
    </a>
    <button @click="sidebarOpen = true" class="p-2 -mr-2 rounded-xl text-bsds-navy hover:bg-bsds-bg transition">
        <x-heroicon-o-bars-3 class="w-6 h-6" />
    </button>
</div>

<!-- Mobile off-canvas drawer -->
<div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-40">
    <div class="absolute inset-0 bg-bsds-navy/40"
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"></div>

    <div class="absolute inset-y-0 left-0 w-72 max-w-[85vw] bg-white flex flex-col shadow-xl"
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
        <button @click="sidebarOpen = false" class="absolute top-3 right-3 p-1.5 rounded-lg text-bsds-gray hover:bg-bsds-bg transition">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
        <x-sidebar-nav />
    </div>
</div>
