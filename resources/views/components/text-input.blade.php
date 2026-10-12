@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-bsds-border focus:border-bsds-teal focus:ring-bsds-teal shadow-sm text-sm']) }}>
