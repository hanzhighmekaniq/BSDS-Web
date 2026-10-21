@props(['disabled' => false])

<div x-data="{ show: false }" class="relative mt-1">
    <input @disabled($disabled) x-bind:type="show ? 'text' : 'password'" type="password"
        {{ $attributes->merge(['class' => 'block w-full pr-11 rounded-xl border-bsds-border focus:border-bsds-teal focus:ring-bsds-teal shadow-sm text-sm']) }}>
    <button type="button" @click="show = !show"
        class="absolute inset-y-0 right-0 flex items-center px-3 text-bsds-gray hover:text-bsds-navy"
        x-bind:aria-label="show ? 'Sembunyikan password' : 'Lihat password'">
        <x-heroicon-o-eye x-show="!show" class="w-5 h-5" />
        <x-heroicon-o-eye-slash x-show="show" x-cloak class="w-5 h-5" />
    </button>
</div>
