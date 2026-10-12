<button {{ $attributes->merge(['type' => 'button', 'class' => 'min-h-[44px] inline-flex items-center px-6 bg-white border-2 border-bsds-navy rounded-xl font-semibold text-sm text-bsds-navy hover:bg-bsds-navy hover:text-white focus:outline-none focus:ring-2 focus:ring-bsds-teal focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
