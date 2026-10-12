<button {{ $attributes->merge(['type' => 'submit', 'class' => 'min-h-[44px] inline-flex items-center px-6 bg-bsds-navy border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-bsds-teal focus:bg-bsds-teal active:bg-bsds-navy focus:outline-none focus:ring-2 focus:ring-bsds-teal focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
