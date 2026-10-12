@if (session('status'))
    <div class="p-3 bg-bsds-teal/10 border border-bsds-teal/30 text-bsds-ink rounded-lg text-sm">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="p-3 bg-amber-50 border border-bsds-warning text-bsds-ink rounded-lg text-sm">
        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
