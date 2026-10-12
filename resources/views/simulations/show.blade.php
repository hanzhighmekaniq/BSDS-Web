@php
    $typeLabels = ['sop' => 'SOP', 'receipt' => 'BUKTI', 'transaction' => 'DATA', 'stock' => 'STOK', 'supervisor' => 'SPV'];
    $maxInv = $scenario->max_investigations;
    $decisionNo = $scenario->decision_no ?? $session->decisionLogs->count() + 1;
    $confidence = ['very_unsure' => 'Sangat ragu', 'unsure' => 'Ragu-ragu', 'fairly_confident' => 'Cukup yakin', 'confident' => 'Yakin', 'very_confident' => 'Sangat yakin'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-2 py-0.5 rounded">
                    {{ $session->case->code }} &bull; {{ $session->mode === 'practice' ? 'Practice Mode' : 'Assessment Mode' }}
                </span>
                <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight mt-1">{{ $scenario->title }}</h2>
            </div>
            <div class="text-right">
                <span class="text-sm font-semibold text-bsds-navy">Decision {{ $decisionNo }} of {{ $totalDecisions }}</span>
                <div class="flex gap-1 mt-1 justify-end" aria-hidden="true">
                    @for ($i = 1; $i <= $totalDecisions; $i++)
                        <span class="h-2 w-6 rounded-full {{ $i < $decisionNo ? 'bg-bsds-teal' : ($i === $decisionNo ? 'bg-bsds-orange' : 'bg-bsds-border') }}"></span>
                    @endfor
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg"
        x-data="simulation({
            investigateUrl: '{{ url('simulations/'.$session->id.'/investigate') }}',
            hintUrl: '{{ route('simulations.hint', $session) }}',
            csrf: '{{ csrf_token() }}',
            maxInv: {{ $maxInv }},
            viewed: @js($session->currentScenario->investigationItems->whereIn('id', $viewedItems)->mapWithKeys(fn ($i) => [$i->id => ['title' => $i->title, 'content' => $i->content]])),
            hints: @js($scenario->hints->whereIn('level', $usedHintLevels)->map(fn ($h) => ['level' => $h->level, 'text' => $h->hint_text])->values()),
            hintsTotal: {{ $scenario->hints->count() }},
        })">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-3 bg-bsds-teal/10 border border-bsds-teal/30 text-bsds-ink rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-3 bg-amber-50 border border-bsds-warning text-bsds-ink rounded-lg text-sm">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">

                {{-- Kolom utama (70%) --}}
                <div class="lg:col-span-7 space-y-6">

                    @if ($scenario->new_problem_text && $session->decisionLogs->isNotEmpty())
                        <div class="rounded-2xl border border-bsds-orange bg-bsds-orange/10 p-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-bsds-navy">New Situation</span>
                            <p class="text-sm text-bsds-ink mt-1">{{ $scenario->new_problem_text }}</p>
                        </div>
                    @endif

                    <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-bsds-gray">Situation</h3>
                        <p class="text-bsds-ink leading-relaxed">{{ $scenario->story_text }}</p>

                        @if ($scenario->customer_dialogue)
                            <blockquote class="border-l-4 border-bsds-orange bg-bsds-bg p-4 rounded-r-xl italic text-bsds-ink">
                                "{{ $scenario->customer_dialogue }}"
                            </blockquote>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('simulations.decision', $session) }}" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-6"
                        @submit="submitted = true; if (!option || !confidence) { $event.preventDefault(); } else { submitting = true; }">
                        @csrf
                        <h3 class="font-heading font-semibold text-bsds-navy">Apa yang akan Anda lakukan?</h3>

                        <div class="space-y-3">
                            @foreach ($scenario->options as $opt)
                                <label class="flex items-start gap-3 p-4 rounded-2xl border-2 border-bsds-border bg-white cursor-pointer transition hover:border-bsds-teal hover:bg-[#F0FAFA] has-[:checked]:border-bsds-teal has-[:checked]:bg-[#E8F6F6]">
                                    <input type="radio" name="option_id" value="{{ $opt->id }}" x-model="option" class="sr-only" required>
                                    <span class="inline-flex items-center justify-center w-7 h-7 shrink-0 rounded-full bg-bsds-navy text-white text-xs font-bold">{{ $opt->label }}</span>
                                    <span class="text-sm text-bsds-ink flex-1">{{ $opt->option_text }}</span>
                                    <span x-show="option == {{ $opt->id }}" x-cloak class="text-bsds-teal font-bold" aria-hidden="true">&#10003;</span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-3">Seberapa yakin Anda?</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                @foreach ($confidence as $value => $label)
                                    <label class="p-2.5 border-2 border-bsds-border rounded-xl text-center cursor-pointer text-xs font-medium hover:border-bsds-teal has-[:checked]:border-bsds-teal has-[:checked]:bg-[#E8F6F6] has-[:checked]:text-bsds-navy">
                                        <input type="radio" name="confidence_level" value="{{ $value }}" x-model="confidence" class="sr-only" required>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <p x-show="submitted && (!option || !confidence)" x-cloak class="text-sm text-bsds-warning font-medium">Please choose an action and your confidence before continuing.</p>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            @if ($session->isPractice() && $scenario->hints->isNotEmpty())
                                <button type="button" @click="getHint()" :disabled="hints.length >= hintsTotal"
                                    class="text-sm font-semibold text-bsds-teal hover:underline disabled:opacity-40 disabled:no-underline">
                                    Need a hint? (<span x-text="hints.length"></span>/{{ $scenario->hints->count() }})
                                </button>
                            @else
                                <span></span>
                            @endif
                            <button type="submit" :disabled="submitting"
                                class="min-h-[48px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition disabled:opacity-75">
                                <span x-text="submitting ? 'Decision Locked' : 'Confirm Decision →'"></span>
                            </button>
                        </div>

                        <template x-if="hints.length">
                            <div class="space-y-2 pt-2 border-t border-bsds-border">
                                <template x-for="h in hints" :key="h.level">
                                    <p class="text-sm bg-bsds-orange/10 border border-bsds-orange/40 rounded-xl p-3 text-bsds-ink">
                                        <span class="font-semibold">Hint <span x-text="h.level"></span>:</span> <span x-text="h.text"></span>
                                    </p>
                                </template>
                            </div>
                        </template>
                    </form>
                </div>

                {{-- Kolom informasi (30%) --}}
                <aside class="lg:col-span-3">
                    <div class="bg-white rounded-2xl border border-bsds-border p-5 space-y-4 lg:sticky lg:top-6">
                        <div class="flex items-center justify-between">
                            <h3 class="font-heading font-semibold text-bsds-navy text-sm">Information</h3>
                            @if ($scenario->investigationItems->isNotEmpty())
                                <span class="text-xs text-bsds-gray"><span x-text="Object.keys(viewed).length"></span>/{{ $maxInv }} dibuka</span>
                            @endif
                        </div>

                        <p x-show="error" x-cloak x-text="error" class="text-xs text-bsds-warning font-medium"></p>

                        @forelse ($scenario->investigationItems as $item)
                            <button type="button" @click="openInfo({{ $item->id }})"
                                class="w-full text-left p-3 rounded-xl border border-bsds-border bg-bsds-bg hover:bg-[#F0FAFA] flex items-center gap-2 text-xs font-semibold text-bsds-ink transition">
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-bsds-navy text-white font-bold">{{ $typeLabels[$item->type] ?? strtoupper($item->type) }}</span>
                                <span class="flex-1">{{ $item->title }}</span>
                                <span x-show="viewed[{{ $item->id }}]" x-cloak class="text-bsds-teal font-bold" aria-hidden="true">&#10003;</span>
                            </button>
                        @empty
                            <p class="text-xs text-bsds-gray text-center p-3 bg-bsds-bg rounded-xl">Tidak ada informasi tambahan pada langkah ini.</p>
                        @endforelse
                    </div>
                </aside>
            </div>
        </div>

        <x-modal name="investigation-info" maxWidth="md">
            <div class="p-6 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-heading font-semibold text-bsds-navy" x-text="viewed[activeItem]?.title"></h3>
                    <button type="button" class="text-bsds-gray hover:text-bsds-navy" @click="$dispatch('close')" aria-label="Tutup">&times;</button>
                </div>
                <p class="text-sm text-bsds-ink whitespace-pre-line" x-text="viewed[activeItem]?.content"></p>
                <div class="flex justify-end pt-2">
                    <x-secondary-button @click="$dispatch('close')">Tutup</x-secondary-button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('simulation', (cfg) => ({
                option: null,
                confidence: null,
                submitted: false,
                submitting: false,
                error: '',
                viewed: cfg.viewed || {},
                activeItem: null,
                hints: cfg.hints || [],
                hintsTotal: cfg.hintsTotal,
                async post(url) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                    });
                    return { ok: res.ok, data: await res.json().catch(() => ({})) };
                },
                async open(id) {
                    if (this.viewed[id]) { return; }
                    this.error = '';
                    const { ok, data } = await this.post(cfg.investigateUrl + '/' + id);
                    if (!ok) { this.error = data.message || 'Gagal membuka informasi.'; return; }
                    this.viewed = { ...this.viewed, [id]: data };
                },
                async openInfo(id) {
                    this.activeItem = id;
                    await this.open(id);
                    if (this.viewed[id]) { this.$dispatch('open-modal', 'investigation-info'); }
                },
                async getHint() {
                    const { ok, data } = await this.post(cfg.hintUrl);
                    if (ok) { this.hints = [...this.hints, data]; }
                },
            }));
        });
    </script>
</x-app-layout>
