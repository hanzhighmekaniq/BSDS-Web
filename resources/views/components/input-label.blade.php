@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-heading font-semibold text-sm text-bsds-navy']) }}>
    {{ $value ?? $slot }}
</label>
