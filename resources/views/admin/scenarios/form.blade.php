@php
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
    $consequenceTypes = ['positive' => 'Positive', 'neutral' => 'Neutral', 'negative' => 'Negative'];
    $scenarioTypes = ['main' => 'Main', 'branch' => 'Branch', 'remedial' => 'Remedial', 'ending' => 'Ending'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
                {{ $scenario->exists ? 'Ubah Skenario: '.$scenario->step_code : 'Tambah Skenario Baru' }} &mdash; {{ $case->code }}
            </h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="w-full"
        x-data="scenarioForm({
            scenarioId: {{ $scenario->id ?? 'null' }},
            options: @js($scenario->options->map(fn ($o) => [
                'id' => $o->id, 'label' => $o->label, 'option_text' => $o->option_text,
                'consequence_text' => $o->consequence_text, 'consequence_type' => $o->consequence_type,
                'outcome_tag' => $o->outcome_tag, 'next_scenario_id' => $o->next_scenario_id,
                'scores' => $o->indicatorScores->pluck('score', 'indicator_id'),
            ])->values()),
            items: @js($scenario->investigationItems->map(fn ($i) => [
                'id' => $i->id, 'title' => $i->title, 'type' => $i->type, 'content' => $i->content,
                'is_relevant' => $i->is_relevant,
            ])->values()),
            hints: @js($scenario->hints->map(fn ($h) => [
                'id' => $h->id, 'level' => $h->level, 'hint_text' => $h->hint_text,
            ])->values()),
        })">
        <div class="w-full space-y-6">
            @include('admin._messages')

            <div class="flex justify-end">
                <button type="button" @click="showPreview = true" class="min-h-[38px] px-4 rounded-xl bg-bsds-orange hover:bg-bsds-navy text-white text-xs font-semibold transition">
                    Preview Tampilan Siswa
                </button>
            </div>

            <form method="POST" action="{{ $scenario->exists ? route('admin.cases.scenarios.update', [$case, $scenario]) : route('admin.cases.scenarios.store', $case) }}" class="space-y-6">
                @csrf
                @if ($scenario->exists) @method('PUT') @endif

                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                    <h3 class="font-heading font-semibold text-bsds-navy">Detail Skenario</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="block text-sm font-semibold text-bsds-navy">Step Code
                            <input type="text" name="step_code" value="{{ old('step_code', $scenario->step_code) }}" required maxlength="30" placeholder="S1" class="{{ $field }}">
                        </label>
                        <label class="block text-sm font-semibold text-bsds-navy">Tipe
                            <select name="scenario_type" class="{{ $field }}">
                                @foreach ($scenarioTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('scenario_type', $scenario->scenario_type ?? 'main') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-bsds-navy">No. Keputusan
                            <input type="number" name="decision_no" min="1" max="50" value="{{ old('decision_no', $scenario->decision_no) }}" class="{{ $field }}">
                        </label>
                        <label class="block text-sm font-semibold text-bsds-navy">Maks. Investigasi
                            <input type="number" name="max_investigations" min="0" max="10" value="{{ old('max_investigations', $scenario->max_investigations ?? 2) }}" required class="{{ $field }}">
                        </label>
                    </div>
                    <label class="block text-sm font-semibold text-bsds-navy">Judul
                        <input type="text" name="title" x-model="title" required maxlength="255" class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Cerita / Situasi
                        <textarea name="story_text" x-model="storyText" rows="4" required maxlength="3000" class="{{ $field }}"></textarea>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Dialog Pelanggan (opsional)
                        <textarea name="customer_dialogue" x-model="customerDialogue" rows="2" maxlength="1000" class="{{ $field }}"></textarea>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Teks "New Situation" (opsional, tampil saat siswa baru masuk ke skenario ini)
                        <textarea name="new_problem_text" x-model="newProblemText" rows="2" maxlength="1000" class="{{ $field }}"></textarea>
                    </label>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 text-sm text-bsds-ink">
                            <input type="checkbox" name="is_start" value="1" x-model="isStart" @checked(old('is_start', $scenario->is_start)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal">
                            Skenario awal (hanya 1 per kasus — otomatis melepas status awal dari skenario lain)
                        </label>
                        <label class="flex items-center gap-2 text-sm text-bsds-ink">
                            <input type="checkbox" name="is_ending" value="1" x-model="isEnding" @checked(old('is_ending', $scenario->is_ending)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal">
                            Skenario akhir (ending — tidak boleh punya opsi)
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4" x-show="!isEnding" x-cloak>
                    <div class="flex items-center justify-between">
                        <h3 class="font-heading font-semibold text-bsds-navy">Opsi Keputusan</h3>
                        <button type="button" @click="addOption()" class="text-xs font-semibold text-bsds-teal hover:underline">+ Tambah Opsi</button>
                    </div>

                    <template x-for="(opt, idx) in options" :key="opt._key">
                        <div class="border border-bsds-border rounded-xl p-4 space-y-3">
                            <input type="hidden" :name="`options[${idx}][id]`" :value="opt.id">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <label class="block text-xs font-semibold text-bsds-navy">Label
                                    <input type="text" :name="`options[${idx}][label]`" x-model="opt.label" maxlength="5" class="{{ $field }}" placeholder="A">
                                </label>
                                <label class="block text-xs font-semibold text-bsds-navy md:col-span-2">Tujuan (skenario berikutnya)
                                    <select :name="`options[${idx}][next_scenario_id]`" x-model="opt.next_scenario_id" class="{{ $field }}">
                                        <option value="">— pilih —</option>
                                        <template x-for="s in siblings.filter(s => s.id !== scenarioId)" :key="s.id">
                                            <option :value="s.id" x-text="s.step_code + ' — ' + s.title"></option>
                                        </template>
                                    </select>
                                </label>
                                <label class="block text-xs font-semibold text-bsds-navy">Tipe Konsekuensi
                                    <select :name="`options[${idx}][consequence_type]`" x-model="opt.consequence_type" class="{{ $field }}">
                                        @foreach ($consequenceTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                            <label class="block text-xs font-semibold text-bsds-navy">Teks Opsi
                                <textarea :name="`options[${idx}][option_text]`" x-model="opt.option_text" rows="2" class="{{ $field }}"></textarea>
                            </label>
                            <label class="block text-xs font-semibold text-bsds-navy">Teks Konsekuensi
                                <textarea :name="`options[${idx}][consequence_text]`" x-model="opt.consequence_text" rows="2" class="{{ $field }}"></textarea>
                            </label>
                            <label class="block text-xs font-semibold text-bsds-navy">Outcome Tag (opsional, untuk analisis)
                                <input type="text" :name="`options[${idx}][outcome_tag]`" x-model="opt.outcome_tag" maxlength="100" class="{{ $field }}">
                            </label>

                            <div class="pt-2 border-t border-bsds-border">
                                <p class="text-xs font-bold uppercase tracking-wider text-bsds-gray mb-2">Skor indikator (kosongkan jika tidak relevan)</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach ($indicators as $indicator)
                                        <label class="text-xs text-bsds-ink flex items-center justify-between gap-2 border border-bsds-border rounded-lg px-2 py-1.5">
                                            {{ $indicator->code }}
                                            <select :name="`options[${idx}][scores][{{ $indicator->id }}]`" x-model="opt.scores['{{ $indicator->id }}']" class="rounded-lg border-bsds-border text-xs">
                                                <option value="">—</option>
                                                <option value="0">0</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                            </select>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="button" @click="options.splice(idx, 1)" class="text-xs font-semibold text-bsds-error hover:underline">Hapus opsi</button>
                            </div>
                        </div>
                    </template>
                    <p class="text-xs text-bsds-gray" x-show="options.length === 0">Belum ada opsi. Skenario tanpa opsi dianggap jalan buntu oleh validator graf (kecuali ending).</p>
                </div>

                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-heading font-semibold text-bsds-navy">Item Investigasi</h3>
                        <button type="button" @click="addItem()" class="text-xs font-semibold text-bsds-teal hover:underline">+ Tambah Item</button>
                    </div>
                    <template x-for="(item, idx) in items" :key="item._key">
                        <div class="border border-bsds-border rounded-xl p-4 space-y-3">
                            <input type="hidden" :name="`investigation_items[${idx}][id]`" :value="item.id">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <label class="block text-xs font-semibold text-bsds-navy">Judul
                                    <input type="text" :name="`investigation_items[${idx}][title]`" x-model="item.title" maxlength="255" class="{{ $field }}">
                                </label>
                                <label class="block text-xs font-semibold text-bsds-navy">Tipe
                                    <input list="inv-types" type="text" :name="`investigation_items[${idx}][type]`" x-model="item.type" maxlength="50" class="{{ $field }}" placeholder="sop">
                                </label>
                                <label class="flex items-end gap-2 text-xs text-bsds-ink pb-2">
                                    <input type="checkbox" :name="`investigation_items[${idx}][is_relevant]`" value="1" x-model="item.is_relevant" class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal">
                                    Relevan (memengaruhi skor informasi)
                                </label>
                            </div>
                            <label class="block text-xs font-semibold text-bsds-navy">Konten
                                <textarea :name="`investigation_items[${idx}][content]`" x-model="item.content" rows="3" class="{{ $field }}"></textarea>
                            </label>
                            <div class="flex justify-end">
                                <button type="button" @click="items.splice(idx, 1)" class="text-xs font-semibold text-bsds-error hover:underline">Hapus item</button>
                            </div>
                        </div>
                    </template>
                    <datalist id="inv-types">
                        <option value="sop"><option value="receipt"><option value="transaction"><option value="stock"><option value="supervisor">
                    </datalist>
                </div>

                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-heading font-semibold text-bsds-navy">Hint (Practice Mode)</h3>
                        <button type="button" @click="addHint()" :disabled="hints.length >= 5" class="text-xs font-semibold text-bsds-teal hover:underline disabled:opacity-40">+ Tambah Hint</button>
                    </div>
                    <template x-for="(hint, idx) in hints" :key="hint._key">
                        <div class="flex gap-3 items-start border border-bsds-border rounded-xl p-3">
                            <input type="hidden" :name="`hints[${idx}][id]`" :value="hint.id">
                            <label class="w-24 text-xs font-semibold text-bsds-navy">Level
                                <input type="number" :name="`hints[${idx}][level]`" x-model.number="hint.level" min="1" max="5" class="{{ $field }}">
                            </label>
                            <label class="flex-1 text-xs font-semibold text-bsds-navy">Teks hint
                                <textarea :name="`hints[${idx}][hint_text]`" x-model="hint.hint_text" rows="2" class="{{ $field }}"></textarea>
                            </label>
                            <button type="button" @click="hints.splice(idx, 1)" class="text-xs font-semibold text-bsds-error hover:underline mt-5">Hapus</button>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.cases.edit', $case) }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan Skenario</button>
                </div>
            </form>

            @if ($scenario->exists)
                <form method="POST" action="{{ route('admin.cases.scenarios.destroy', [$case, $scenario]) }}" onsubmit="return confirm('Hapus skenario {{ $scenario->step_code }}? Opsi lain yang menuju ke sini akan kehilangan tujuannya (muncul sebagai error di Validasi Graf).')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-bsds-error hover:underline">Hapus skenario ini</button>
                </form>
            @endif

            {{-- Modal Preview Siswa --}}
            <div x-show="showPreview" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl border border-bsds-border max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden" @click.away="showPreview = false">
                    <div class="px-6 py-4 border-b border-bsds-border flex items-center justify-between bg-bsds-bg">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-bsds-teal">Preview Tampilan Siswa</div>
                            <h3 class="font-heading font-semibold text-lg text-bsds-navy" x-text="title || 'Judul Skenario'"></h3>
                        </div>
                        <button type="button" @click="showPreview = false" class="text-bsds-gray hover:text-bsds-navy text-xl font-bold">&times;</button>
                    </div>

                    <div class="p-6 overflow-y-auto space-y-6">
                        <template x-if="newProblemText">
                            <div class="rounded-2xl border border-bsds-orange bg-bsds-orange/10 p-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-bsds-navy">New Situation</span>
                                <p class="text-sm text-bsds-ink mt-1" x-text="newProblemText"></p>
                            </div>
                        </template>

                        <div class="bg-white rounded-2xl border border-bsds-border p-5 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bsds-gray">Situation</h4>
                            <p class="text-bsds-ink leading-relaxed" x-text="storyText || 'Cerita / situasi skenario...'"></p>

                            <template x-if="customerDialogue">
                                <blockquote class="border-l-4 border-bsds-orange bg-bsds-bg p-3 rounded-r-xl italic text-bsds-ink" x-text="`&ldquo;${customerDialogue}&rdquo;`"></blockquote>
                            </template>
                        </div>

                        {{-- Opsi --}}
                        <div class="bg-white rounded-2xl border border-bsds-border p-5 space-y-4" x-show="!isEnding">
                            <h4 class="font-heading font-semibold text-bsds-navy text-sm">Apa yang akan Anda lakukan?</h4>
                            <div class="space-y-3">
                                <template x-for="(opt, idx) in options.filter(o => o.option_text)" :key="opt._key">
                                    <div class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-bsds-border bg-white">
                                        <span class="inline-flex items-center justify-center w-7 h-7 shrink-0 rounded-full bg-bsds-navy text-white text-xs font-bold" x-text="opt.label || (idx + 1)"></span>
                                        <div class="flex-1">
                                            <div class="text-sm text-bsds-ink" x-text="opt.option_text"></div>
                                            <template x-if="opt.consequence_text">
                                                <div class="mt-2 text-xs text-bsds-gray border-t border-bsds-border pt-1">
                                                    <span class="font-semibold text-bsds-navy">Konsekuensi:</span> <span x-text="opt.consequence_text"></span>
                                                    <span class="ml-2 font-mono text-[10px] uppercase px-1.5 py-0.5 rounded bg-bsds-bg text-bsds-teal" x-text="opt.consequence_type"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="options.filter(o => o.option_text).length === 0">
                                    <p class="text-xs text-bsds-gray italic">Belum ada opsi dengan teks yang diisi.</p>
                                </template>
                            </div>
                        </div>

                        {{-- Item Investigasi & Hints --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-bsds-border bg-bsds-bg space-y-2">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-bsds-navy">Item Investigasi (<span x-text="items.filter(i => i.title).length"></span>)</h5>
                                <template x-for="item in items.filter(i => i.title)" :key="item._key">
                                    <div class="p-2.5 rounded-lg bg-white border border-bsds-border text-xs">
                                        <div class="font-semibold text-bsds-ink" x-text="item.title"></div>
                                        <div class="text-[11px] text-bsds-gray mt-1 line-clamp-2" x-text="item.content"></div>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 rounded-xl border border-bsds-border bg-bsds-bg space-y-2">
                                <h5 class="text-xs font-bold uppercase tracking-wider text-bsds-navy">Hints Practice Mode (<span x-text="hints.filter(h => h.hint_text).length"></span>)</h5>
                                <template x-for="hint in hints.filter(h => h.hint_text)" :key="hint._key">
                                    <div class="p-2.5 rounded-lg bg-white border border-bsds-border text-xs">
                                        <div class="font-bold text-bsds-teal" x-text="`Level ${hint.level}`"></div>
                                        <div class="text-[11px] text-bsds-ink mt-0.5" x-text="hint.hint_text"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-3 border-t border-bsds-border bg-bsds-bg flex justify-end">
                        <button type="button" @click="showPreview = false" class="px-4 py-2 rounded-xl bg-bsds-navy text-white text-xs font-semibold hover:bg-bsds-teal transition">Tutup Preview</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scenarioForm', (cfg) => ({
                scenarioId: cfg.scenarioId,
                showPreview: false,
                title: @js(old('title', $scenario->title ?? '')),
                storyText: @js(old('story_text', $scenario->story_text ?? '')),
                customerDialogue: @js(old('customer_dialogue', $scenario->customer_dialogue ?? '')),
                newProblemText: @js(old('new_problem_text', $scenario->new_problem_text ?? '')),
                isStart: {{ old('is_start', $scenario->is_start ?? false) ? 'true' : 'false' }},
                isEnding: {{ old('is_ending', $scenario->is_ending ?? false) ? 'true' : 'false' }},
                siblings: @js($siblingScenarios->map(fn ($s) => ['id' => $s->id, 'step_code' => $s->step_code, 'title' => $s->title])),
                options: cfg.options.map((o, i) => ({ ...o, _key: 'o' + i })),
                items: cfg.items.map((it, i) => ({ ...it, _key: 'i' + i })),
                hints: cfg.hints.map((h, i) => ({ ...h, _key: 'h' + i })),
                addOption() {
                    this.options.push({ _key: 'o' + Date.now(), id: null, label: '', option_text: '', consequence_text: '', consequence_type: 'neutral', outcome_tag: '', next_scenario_id: '', scores: {} });
                },
                addItem() {
                    this.items.push({ _key: 'i' + Date.now(), id: null, title: '', type: 'sop', content: '', is_relevant: false });
                },
                addHint() {
                    this.hints.push({ _key: 'h' + Date.now(), id: null, level: this.hints.length + 1, hint_text: '' });
                },
            }));
        });
    </script>
</x-app-layout>
