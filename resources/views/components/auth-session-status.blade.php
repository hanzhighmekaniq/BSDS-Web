@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-bsds-success']) }}>
        {{ $status }}
    </div>
@endif
