<button {{ $attributes->merge(['type' => 'submit', 'class' => 'min-h-[44px] inline-flex items-center px-6 bg-bsds-error border border-transparent rounded-xl font-semibold text-sm text-white hover:opacity-90 active:opacity-80 focus:outline-none focus:ring-2 focus:ring-bsds-error focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
