# BSDS — Dynamic Case/Scenario Builder (Admin CRUD untuk Isi Simulasi)

**Status:** Siap eksekusi. Ditulis 2026-10-12 setelah audit penuh terhadap model, migrasi, validator graf, dan pola kode Admin yang sudah ada.

**Konteks:** Saat ini metadata kasus (judul, deskripsi, role, briefing, level, estimasi waktu, aktif/nonaktif) dan indikator (nama, deskripsi, feedback per kategori) **sudah dinamis** — bisa diubah dari panel Admin. Tapi isi simulasi yang sebenarnya — skenario/situasi, pilihan A/B/C, konsekuensi, percabangan (`next_scenario_id`), item investigasi, hint, dan skor indikator per opsi — **masih hardcoded** di seeder PHP (`database/seeders/Case01Seeder.php` s/d `Case04Seeder.php`). Tidak ada cara menambah kasus baru atau mengubah alurnya tanpa menulis kode.

Dokumen ini adalah rencana lengkap untuk membuat SEMUA bagian itu dinamis lewat panel Admin, mengikuti arsitektur dan gaya kode yang SUDAH ADA di proyek ini (jangan menyimpang dari pola di bawah tanpa alasan kuat).

---

## 1. Tujuan & Lingkup

**Tujuan:** Admin dapat membuat kasus baru dari nol, dan mengelola seluruh skenario/opsi/item investigasi/hint/skor indikator-nya, sepenuhnya lewat form di browser — tanpa menyentuh kode atau menjalankan seeder.

**Termasuk dalam lingkup:**
- Buat kasus baru (form, bukan seeder)
- CRUD Skenario per kasus (cerita, dialog, tipe, flag awal/akhir, maks investigasi)
- CRUD Opsi Keputusan per skenario (teks, konsekuensi, tipe konsekuensi, tujuan percabangan, skor per indikator)
- CRUD Item Investigasi per skenario (judul, tipe, konten, relevan/tidak)
- CRUD Hint per skenario (level, teks)
- Validasi graf (`ScenarioGraphValidator`, sudah ada) tetap jadi gerbang sebelum kasus bisa diaktifkan — diterapkan juga di alur create, bukan cuma update
- Guard rails supaya admin tidak bisa merusak data penelitian yang sudah terekam

**TIDAK termasuk dalam lingkup ini (sengaja ditunda, lihat Section 9):**
- Visual drag-and-drop graph builder (flowchart interaktif)
- Duplikasi/cloning kasus
- Import/export kasus sebagai JSON
- Versioning/draft vs published
- Migrasi 4 kasus yang sudah ada (Case01–04Seeder) ke format baru ini — seeder lama **tetap jalan apa adanya** dan tidak perlu diubah; fitur baru ini hanya menambah *cara lain* untuk membuat/mengubah kasus, tidak mewajibkan migrasi data lama.

---

## 2. Keputusan Desain yang Sudah Diambil

Supaya sesi eksekusi tidak perlu berhenti untuk bertanya, keputusan berikut sudah final:

1. **Tidak ada migrasi database baru.** Skema (`cases`, `scenarios`, `scenario_options`, `investigation_items`, `hints`, `option_indicator_scores`) sudah punya semua kolom yang dibutuhkan. Satu-satunya aturan baru (`step_code` unik per kasus) divalidasi di level aplikasi (Form Request), bukan constraint DB — konsisten dengan proyek ini yang juga tidak memberi unique constraint DB untuk `step_code` saat ini.
2. **`code` kasus tidak bisa diubah setelah dibuat** — mengikuti preseden `Indicator.code` yang immutable (lihat komentar di `IndicatorController::update()`). Field `code` hanya muncul di form create.
3. **Kasus baru selalu dibuat `is_active = false`.** Tidak ada checkbox aktif di form create (graf skenario pasti invalid saat kasus baru dibuat kosong — tidak ada gunanya menampilkan opsi yang pasti gagal). Admin mengaktifkan dari halaman edit setelah skenario lengkap dan graf valid, pakai guard yang **sudah ada** di `AdminCaseController::update()`.
4. **Reuse view, bukan bikin file baru untuk create vs edit.** Mengikuti pola `ClassroomController` (satu `admin.classrooms.form` dipakai untuk `create()` dan `edit()`). Terapkan pola yang sama ke `admin.cases.form` dan ke view skenario baru.
5. **Satu `CaseRequest` dipakai untuk store & update**, dengan rule `code` hanya aktif saat method POST (create). Mengikuti pola `ClassroomRequest` yang dipakai untuk store+update tanpa duplikasi class.
6. **Builder skenario pakai form biasa + Alpine.js untuk repeater (opsi/item investigasi/hint), BUKAN framework JS baru.** Proyek ini sudah pakai Alpine di `simulations/show.blade.php` untuk pola serupa (list dinamis + `x-for`). Konsisten dengan itu, bukan menambah dependency baru (Vue/React/Livewire).
7. **Alur dua langkah untuk percabangan:** admin membuat skenario satu per satu (minimal `step_code` + judul dulu), baru kemudian mengatur opsi & tujuannya — karena dropdown "skenario tujuan" di opsi butuh skenario lain sudah ada (foreign key, tidak bisa menunjuk ke baris yang belum ada). Ini sama persis dengan cara kerja seeder sekarang (semua step didefinisikan dulu secara konsep, baru opsi di-wire belakangan).
8. **Flag `is_start` bersifat eksklusif otomatis**: saat admin menyimpan skenario dengan `is_start = true`, server otomatis meng-set `is_start = false` untuk skenario lain di kasus yang sama. Ini mencegah pelanggaran aturan "harus ada tepat 1 skenario awal" yang sudah dicek `ScenarioGraphValidator`.
9. **Skenario `is_ending = true` tidak boleh punya opsi.** Saat disimpan dengan `is_ending = true`, server **mengabaikan/menghapus** data opsi yang mungkin terkirim (bukan menolak dengan error) — UI juga menyembunyikan section opsi lewat `x-show` saat checkbox ending dicentang, jadi submit opsi kosong adalah kasus normal, bukan error.
10. **Indikator `INF` (Pengumpulan Informasi) TIDAK muncul di matrix skor opsi.** Skor `INF` dihitung otomatis dari item investigasi (`SimulationScorer::informationScore()`), bukan diisi manual per opsi — lihat `SimulationScorer::INFORMATION_CODE`. Matrix skor hanya menampilkan indikator aktif selain `INF`.
11. **Skor indikator per opsi bersifat opsional per indikator** (bukan wajib 0 untuk semua). Pilihan dropdown: `—` (tidak dinilai, tidak membuat baris `option_indicator_scores`), `0`, `1`, `2`. Ini meniru persis pola seeder yang ada (`Case01Seeder`), di mana tidak semua opsi diberi skor untuk semua indikator.
12. **Menghapus skenario yang sudah punya `decision_logs` (sudah pernah dipakai sesi nyata) DIBLOKIR**, bukan cuma diberi peringatan — karena `decision_logs.scenario_id` adalah `cascadeOnDelete()`, menghapus skenario akan menghapus data penelitian secara diam-diam. Ini beda dari pola "peringatan saja" di `ClassroomController::destroy()` karena konsekuensinya jauh lebih merusak (hilang data riset, bukan sekadar siswa tanpa kelas).
13. **Menghapus skenario yang masih jadi tujuan opsi lain DIPERBOLEHKAN** (FK `next_scenario_id` sudah `nullOnDelete()`), tapi dikonfirmasi dulu di UI (`confirm()` JS, pola yang sama seperti hapus kelas), dan hasilnya akan langsung terlihat sebagai error baru di "Validasi graf" pada halaman edit kasus.

---

## 3. Peta Perubahan File

### Baru
| File | Isi |
|---|---|
| `app/Http/Controllers/Admin/ScenarioController.php` | CRUD skenario + anak-anaknya (opsi, item investigasi, hint) |
| `app/Http/Requests/Admin/ScenarioRequest.php` | Validasi skenario + nested arrays |
| `resources/views/admin/scenarios/form.blade.php` | Form besar (skenario + 3 repeater Alpine) dipakai untuk create & edit |
| `tests/Feature/AdminScenarioBuilderTest.php` | Test baru untuk seluruh alur CRUD |

### Diubah
| File | Perubahan |
|---|---|
| `routes/web.php` | Tambah route `cases.create`, `cases.store`, dan grup `cases.scenarios.*` |
| `app/Http/Controllers/Admin/CaseController.php` | Tambah `create()` dan `store()` |
| `app/Http/Requests/Admin/CaseRequest.php` | Tambah rule `code` kondisional (hanya saat POST) |
| `resources/views/admin/cases/index.blade.php` | Tambah tombol "+ Tambah Kasus"; perbarui catatan di footer (tidak lagi bilang "dikelola lewat seeder") |
| `resources/views/admin/cases/form.blade.php` | Reuse untuk create (field `code`, tanpa checkbox aktif, tanpa section skenario/graf) & edit (seperti sekarang + tombol "Ubah"/"+ Tambah Skenario" di tiap baris skenario) |

### Tidak berubah (dipastikan sudah cukup)
- Semua model (`Scenario`, `ScenarioOption`, `InvestigationItem`, `Hint`, `OptionIndicatorScore`, `Indicator`) — relasi sudah lengkap.
- `ScenarioGraphValidator` — dipakai apa adanya.
- `SimulationScorer` — tidak disentuh; builder ini hanya mengisi tabel yang sudah dibaca scorer.
- Seeder `Case01Seeder.php`...`Case04Seeder.php` — tetap ada, tetap bisa dijalankan ulang, tidak terpengaruh.

---

## 4. Detail Teknis per Lapisan

### 4.1 Routes (`routes/web.php`)

Tambahkan di dalam grup `Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(...)`, **sebelum** baris `Route::get('cases', ...)` yang sudah ada (urutan tidak krusial karena path berbeda jumlah segmen, tapi taruh berurutan secara logis):

```php
use App\Http\Controllers\Admin\ScenarioController as AdminScenarioController; // tambahkan di use block atas

Route::get('cases/create', [AdminCaseController::class, 'create'])->name('cases.create');
Route::post('cases', [AdminCaseController::class, 'store'])->name('cases.store');
Route::get('cases', [AdminCaseController::class, 'index'])->name('cases.index');
Route::get('cases/{case}/edit', [AdminCaseController::class, 'edit'])->name('cases.edit');
Route::put('cases/{case}', [AdminCaseController::class, 'update'])->name('cases.update');

Route::get('cases/{case}/scenarios/create', [AdminScenarioController::class, 'create'])->name('cases.scenarios.create');
Route::post('cases/{case}/scenarios', [AdminScenarioController::class, 'store'])->name('cases.scenarios.store');
Route::get('cases/{case}/scenarios/{scenario}/edit', [AdminScenarioController::class, 'edit'])->name('cases.scenarios.edit');
Route::put('cases/{case}/scenarios/{scenario}', [AdminScenarioController::class, 'update'])->name('cases.scenarios.update');
Route::delete('cases/{case}/scenarios/{scenario}', [AdminScenarioController::class, 'destroy'])->name('cases.scenarios.destroy');
```

**Catatan penting:** `{scenario}` di-bind secara implisit oleh Laravel berdasarkan `id` (default), BUKAN otomatis di-scope ke `{case}`. Setiap method controller WAJIB memverifikasi `abort_unless($scenario->case_id === $case->id, 404)` di baris pertama — ini mencegah IDOR (admin A tidak sengaja/sengaja mengedit skenario milik kasus lain lewat manipulasi URL). Lihat contoh di Section 4.3.

### 4.2 Form Requests

#### `app/Http/Requests/Admin/CaseRequest.php` (ubah)

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('post') && is_string($this->code)) {
            $this->merge(['code' => strtoupper(trim($this->code))]);
        }

        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'role_title' => ['nullable', 'string', 'max:100'],
            'briefing_text' => ['nullable', 'string', 'max:5000'],
            'level' => ['required', Rule::in(['foundation', 'developing', 'challenge'])],
            'estimated_time' => ['required', 'integer', 'min:1', 'max:240'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['boolean'],
        ];

        if ($this->isMethod('post')) {
            $rules['code'] = ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', 'unique:cases,code'];
        }

        return $rules;
    }
}
```

Alasan `isMethod('post')`: route `cases.store` adalah `POST`, route `cases.update` adalah `PUT`. Ini membedakan konteks create vs update tanpa dua class terpisah — identik filosofinya dengan bagaimana `UserRequest` sudah mengecek `$this->route('user')` untuk membedakan create/update pada kasus lain di file yang sama.

#### `app/Http/Requests/Admin/ScenarioRequest.php` (baru)

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ScenarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_start' => $this->boolean('is_start'),
            'is_ending' => $this->boolean('is_ending'),
        ]);
    }

    public function rules(): array
    {
        return [
            'step_code' => ['required', 'string', 'max:30'],
            'scenario_type' => ['required', Rule::in(['main', 'branch', 'remedial', 'ending'])],
            'is_start' => ['boolean'],
            'is_ending' => ['boolean'],
            'decision_no' => ['nullable', 'integer', 'min:1', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'story_text' => ['required', 'string', 'max:3000'],
            'customer_dialogue' => ['nullable', 'string', 'max:1000'],
            'new_problem_text' => ['nullable', 'string', 'max:1000'],
            'max_investigations' => ['required', 'integer', 'min:0', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],

            'options' => ['array'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.label' => ['nullable', 'string', 'max:5'],
            'options.*.option_text' => ['nullable', 'string', 'max:1000'],
            'options.*.consequence_text' => ['nullable', 'string', 'max:2000'],
            'options.*.consequence_type' => ['nullable', Rule::in(['positive', 'neutral', 'negative'])],
            'options.*.outcome_tag' => ['nullable', 'string', 'max:100'],
            'options.*.next_scenario_id' => ['nullable', 'integer', 'exists:scenarios,id'],
            'options.*.sort_order' => ['nullable', 'integer'],
            'options.*.scores' => ['array'],
            'options.*.scores.*' => ['nullable', Rule::in(['0', '1', '2'])],

            'investigation_items' => ['array'],
            'investigation_items.*.id' => ['nullable', 'integer'],
            'investigation_items.*.title' => ['nullable', 'string', 'max:255'],
            'investigation_items.*.type' => ['nullable', 'string', 'max:50'],
            'investigation_items.*.content' => ['nullable', 'string', 'max:2000'],
            'investigation_items.*.is_relevant' => ['boolean'],
            'investigation_items.*.sort_order' => ['nullable', 'integer'],

            'hints' => ['array', 'max:5'],
            'hints.*.id' => ['nullable', 'integer'],
            'hints.*.level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'hints.*.hint_text' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Validasi lintas-field yang butuh konteks route (case_id saat ini, id skenario saat update).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $case = $this->route('case');
            $scenario = $this->route('scenario'); // null saat create

            // step_code harus unik di dalam kasus ini.
            $exists = $case->scenarios()
                ->where('step_code', $this->input('step_code'))
                ->when($scenario, fn ($q) => $q->where('id', '!=', $scenario->id))
                ->exists();

            if ($exists) {
                $v->errors()->add('step_code', 'Step code ini sudah dipakai skenario lain di kasus yang sama.');
            }

            // next_scenario_id (jika diisi) harus milik kasus yang sama, bukan kasus lain.
            foreach ($this->input('options', []) as $i => $option) {
                $nextId = $option['next_scenario_id'] ?? null;
                if ($nextId && ! $case->scenarios()->where('id', $nextId)->exists()) {
                    $v->errors()->add("options.$i.next_scenario_id", 'Skenario tujuan harus berasal dari kasus yang sama.');
                }
            }
        });
    }
}
```

**Kenapa field-field nested (`options.*.label`, dsb.) semua `nullable`, bukan `required_with`?** Karena baris repeater kosong (admin klik "+ Tambah Opsi" lalu berubah pikiran dan tidak isi apa-apa) harus bisa lolos validasi dan nanti **di-skip** oleh controller (bukan dianggap error). Validasi "kelengkapan logis per baris" (kalau salah satu field opsi diisi, field lain opsi itu wajib diisi) sengaja TIDAK ditambahkan di versi ini — biar sederhana. Ini trade-off yang didokumentasikan di Section 9.

### 4.3 Controllers

#### `app/Http/Controllers/Admin/CaseController.php` (tambah 2 method)

```php
public function create(): View
{
    return view('admin.cases.form', [
        'case' => new CaseModel(['level' => 'foundation', 'estimated_time' => 15, 'sort_order' => 0, 'is_active' => false]),
        'scenarios' => collect(),
        'graphErrors' => [],
        'sessionCount' => 0,
    ]);
}

public function store(CaseRequest $request): RedirectResponse
{
    $data = $request->safe()->only([
        'code', 'title', 'description', 'role_title', 'briefing_text', 'level', 'estimated_time', 'sort_order',
    ]);
    $data['is_active'] = false;
    $data['sort_order'] = $data['sort_order'] ?? ((int) CaseModel::max('sort_order') + 1);

    $case = CaseModel::create($data);

    return redirect()->route('admin.cases.edit', $case)
        ->with('status', 'Kasus berhasil dibuat. Tambahkan skenario sebelum mengaktifkannya.');
}
```

`edit()` dan `update()` yang sudah ada **tidak perlu diubah** — `CaseRequest::rules()` otomatis tidak menyertakan `code` saat PUT, jadi `$request->validated()` yang sudah dipakai `update()` tetap aman (tidak pernah menyentuh `code`).

#### `app/Http/Controllers/Admin/ScenarioController.php` (baru, lengkap)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScenarioRequest;
use App\Models\CaseModel;
use App\Models\Indicator;
use App\Models\Scenario;
use App\Services\SimulationScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function create(CaseModel $case): View
    {
        return $this->form($case, new Scenario(['scenario_type' => 'main', 'max_investigations' => 2]));
    }

    public function store(ScenarioRequest $request, CaseModel $case): RedirectResponse
    {
        $scenario = DB::transaction(function () use ($request, $case) {
            $scenario = $case->scenarios()->create($this->scenarioData($request));
            $this->syncChildren($scenario, $request);
            $this->enforceSingleStart($case, $scenario);

            return $scenario;
        });

        return redirect()->route('admin.cases.scenarios.edit', [$case, $scenario])
            ->with('status', 'Skenario berhasil dibuat.');
    }

    public function edit(CaseModel $case, Scenario $scenario): View
    {
        abort_unless($scenario->case_id === $case->id, 404);
        $scenario->load(['options.indicatorScores', 'investigationItems', 'hints']);

        return $this->form($case, $scenario);
    }

    public function update(ScenarioRequest $request, CaseModel $case, Scenario $scenario): RedirectResponse
    {
        abort_unless($scenario->case_id === $case->id, 404);

        DB::transaction(function () use ($request, $case, $scenario) {
            $scenario->update($this->scenarioData($request));
            $this->syncChildren($scenario, $request);
            $this->enforceSingleStart($case, $scenario);
        });

        return redirect()->route('admin.cases.edit', $case)
            ->with('status', 'Skenario berhasil diperbarui.');
    }

    public function destroy(CaseModel $case, Scenario $scenario): RedirectResponse
    {
        abort_unless($scenario->case_id === $case->id, 404);

        if ($scenario->decisionLogs()->exists()) {
            return back()->withErrors([
                'delete' => 'Skenario ini sudah memiliki data keputusan siswa (sesi nyata) dan tidak dapat dihapus demi menjaga integritas data penelitian.',
            ]);
        }

        $scenario->delete();

        return redirect()->route('admin.cases.edit', $case)
            ->with('status', 'Skenario berhasil dihapus.');
    }

    protected function form(CaseModel $case, Scenario $scenario): View
    {
        return view('admin.scenarios.form', [
            'case' => $case,
            'scenario' => $scenario,
            'siblingScenarios' => $case->scenarios()->orderBy('sort_order')->get(['id', 'step_code', 'title']),
            'indicators' => Indicator::where('is_active', true)
                ->where('code', '!=', SimulationScorer::INFORMATION_CODE)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    protected function scenarioData(ScenarioRequest $request): array
    {
        return $request->safe()->only([
            'step_code', 'scenario_type', 'is_start', 'is_ending', 'decision_no',
            'title', 'story_text', 'customer_dialogue', 'new_problem_text',
            'max_investigations', 'sort_order',
        ]);
    }

    protected function enforceSingleStart(CaseModel $case, Scenario $scenario): void
    {
        if ($scenario->is_start) {
            $case->scenarios()->where('id', '!=', $scenario->id)->update(['is_start' => false]);
        }
    }

    /**
     * Sinkronkan opsi, item investigasi, dan hint: update yang punya id,
     * buat baru yang tidak punya id (dan punya isi), hapus yang tidak lagi dikirim.
     * Baris kosong (tanpa field inti terisi) diabaikan, tidak dibuat sebagai baris kosong di DB.
     */
    protected function syncChildren(Scenario $scenario, ScenarioRequest $request): void
    {
        // Opsi dihapus semua jika skenario ini ending (ending tidak boleh punya opsi).
        $incomingOptions = $scenario->is_ending ? [] : array_filter(
            $request->validated('options') ?? [],
            fn ($o) => filled($o['label'] ?? null) && filled($o['option_text'] ?? null)
        );

        $keepIds = [];
        foreach (array_values($incomingOptions) as $i => $data) {
            if (! empty($data['id'])) {
                $option = $scenario->options()->findOrFail($data['id']);
                $option->update($this->optionData($data, $i));
            } else {
                $option = $scenario->options()->create($this->optionData($data, $i));
            }
            $keepIds[] = $option->id;

            $scores = array_filter($data['scores'] ?? [], fn ($v) => $v !== null && $v !== '');
            $option->indicatorScores()->whereNotIn('indicator_id', array_keys($scores))->delete();
            foreach ($scores as $indicatorId => $score) {
                $option->indicatorScores()->updateOrCreate(
                    ['indicator_id' => $indicatorId],
                    ['score' => (int) $score]
                );
            }
        }
        $scenario->options()->whereNotIn('id', $keepIds ?: [0])->delete();

        // Item investigasi
        $incomingItems = array_filter(
            $request->validated('investigation_items') ?? [],
            fn ($i) => filled($i['title'] ?? null) && filled($i['content'] ?? null)
        );
        $keepItemIds = [];
        foreach (array_values($incomingItems) as $i => $data) {
            if (! empty($data['id'])) {
                $item = $scenario->investigationItems()->findOrFail($data['id']);
                $item->update($this->itemData($data, $i));
            } else {
                $item = $scenario->investigationItems()->create($this->itemData($data, $i));
            }
            $keepItemIds[] = $item->id;
        }
        $scenario->investigationItems()->whereNotIn('id', $keepItemIds ?: [0])->delete();

        // Hint
        $incomingHints = array_filter(
            $request->validated('hints') ?? [],
            fn ($h) => filled($h['hint_text'] ?? null) && filled($h['level'] ?? null)
        );
        $keepHintIds = [];
        foreach (array_values($incomingHints) as $data) {
            if (! empty($data['id'])) {
                $hint = $scenario->hints()->findOrFail($data['id']);
                $hint->update(['level' => $data['level'], 'hint_text' => $data['hint_text']]);
            } else {
                $hint = $scenario->hints()->updateOrCreate(
                    ['level' => $data['level']],
                    ['hint_text' => $data['hint_text']]
                );
            }
            $keepHintIds[] = $hint->id;
        }
        $scenario->hints()->whereNotIn('id', $keepHintIds ?: [0])->delete();
    }

    protected function optionData(array $data, int $index): array
    {
        return [
            'label' => $data['label'],
            'option_text' => $data['option_text'],
            'consequence_text' => $data['consequence_text'] ?? '',
            'consequence_type' => $data['consequence_type'] ?? 'neutral',
            'outcome_tag' => $data['outcome_tag'] ?: null,
            'next_scenario_id' => $data['next_scenario_id'] ?: null,
            'sort_order' => $data['sort_order'] ?? $index,
        ];
    }

    protected function itemData(array $data, int $index): array
    {
        return [
            'title' => $data['title'],
            'type' => $data['type'] ?: 'sop',
            'content' => $data['content'],
            'is_relevant' => (bool) ($data['is_relevant'] ?? false),
            'sort_order' => $data['sort_order'] ?? $index,
        ];
    }
}
```

**Poin krusial yang WAJIB dipertahankan saat implementasi** (celah yang mudah terlewat):
- **Jangan** pakai `updateOrCreate(['id' => $data['id'] ?? null], [...])` untuk opsi/item investigasi. Kalau `id` null, Eloquent akan mencoba insert dengan `id => null` tercampur ke attribute array (karena `updateOrCreate` menggabungkan kondisi pencarian ke data saat membuat baris baru) — ini berisiko error atau perilaku aneh tergantung driver DB. **Selalu** percabangan eksplisit `if (!empty($data['id'])) update else create`, seperti kode di atas.
- `$scenario->options()->findOrFail($data['id'])` otomatis ter-scope ke `scenario_id` lewat relasi `hasMany`, jadi admin tidak bisa mengirim `id` opsi milik skenario lain untuk "mencuri" update ke skenario ini — ini proteksi IDOR implisit yang penting, jangan diganti jadi `ScenarioOption::findOrFail($data['id'])` (query global tanpa scope).
- `whereNotIn('id', $keepIds ?: [0])` — trik `?: [0]` mencegah `whereNotIn('id', [])` yang di beberapa versi/driver Laravel bisa match semua baris (perilaku SQL `NOT IN ()` kosong itu ambigu). Dengan `[0]` sebagai fallback, query tetap aman menghapus SEMUA baris lama saat admin mengosongkan seluruh repeater.
- `enforceSingleStart` dipanggil **setelah** `$scenario` tersimpan (sudah punya `id`), supaya `where('id', '!=', $scenario->id)` punya nilai yang benar.

### 4.4 Views

#### `resources/views/admin/cases/index.blade.php` (ubah)

Tambahkan tombol di atas tabel (pola identik `admin/classrooms/index.blade.php`):

```blade
<div class="flex justify-end">
    <a href="{{ route('admin.cases.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-bsds-teal hover:bg-bsds-navy text-white text-sm font-semibold transition">+ Tambah Kasus</a>
</div>
```

Ganti baris catatan footer:
```blade
<p class="text-xs text-bsds-gray">Isi skenario (opsi, konsekuensi, hint, skor) dikelola lewat seeder. Panel ini mengubah metadata kasus dan menampilkan validasi graf.</p>
```
menjadi:
```blade
<p class="text-xs text-bsds-gray">Klik "Ubah" pada sebuah kasus untuk mengelola skenario, opsi, item investigasi, hint, dan skor indikatornya.</p>
```

#### `resources/views/admin/cases/form.blade.php` (restrukturisasi)

Ubah bagian `<form>` metadata supaya adaptif create/edit:

```blade
@php
    $field = 'mt-1 block w-full rounded-xl border-bsds-border text-sm focus:border-bsds-teal focus:ring-bsds-teal';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="space-y-3">
            <h2 class="font-heading font-semibold text-xl text-bsds-navy leading-tight">
                {{ $case->exists ? 'Ubah Kasus: '.$case->code : 'Tambah Kasus Baru' }}
            </h2>
            <x-admin-tabs />
        </div>
    </x-slot>

    <div class="py-8 bg-bsds-bg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

            @if ($sessionCount > 0)
                <div class="p-3 bg-bsds-orange/10 border border-bsds-orange/40 rounded-lg text-sm text-bsds-ink">Kasus ini sudah memiliki {{ $sessionCount }} sesi. Hindari mengubah isi yang memengaruhi data penelitian yang sedang dikumpulkan.</div>
            @endif

            <form method="POST" action="{{ $case->exists ? route('admin.cases.update', $case) : route('admin.cases.store') }}" class="bg-white rounded-2xl border border-bsds-border p-6 space-y-5">
                @csrf
                @if ($case->exists) @method('PUT') @endif

                @if (! $case->exists)
                    <label class="block text-sm font-semibold text-bsds-navy">Kode Kasus
                        <input type="text" name="code" value="{{ old('code') }}" required maxlength="50" placeholder="CASE-05" class="{{ $field }} font-mono uppercase">
                    </label>
                    <p class="text-xs text-bsds-gray -mt-3">Huruf besar, angka, strip, atau underscore. Tidak dapat diubah setelah dibuat.</p>
                @else
                    <p class="text-xs text-bsds-gray">Kode: <span class="font-mono font-semibold text-bsds-ink">{{ $case->code }}</span> (tidak dapat diubah)</p>
                @endif

                <label class="block text-sm font-semibold text-bsds-navy">Judul
                    <input type="text" name="title" value="{{ old('title', $case->title) }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Deskripsi
                    <textarea name="description" rows="3" required maxlength="2000" class="{{ $field }}">{{ old('description', $case->description) }}</textarea>
                </label>
                <label class="block text-sm font-semibold text-bsds-navy">Briefing
                    <textarea name="briefing_text" rows="5" maxlength="5000" class="{{ $field }}">{{ old('briefing_text', $case->briefing_text) }}</textarea>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="block text-sm font-semibold text-bsds-navy">Peran
                        <input type="text" name="role_title" value="{{ old('role_title', $case->role_title) }}" maxlength="100" class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Level
                        <select name="level" class="{{ $field }}">
                            @foreach (['foundation' => 'Foundation', 'developing' => 'Developing', 'challenge' => 'Challenge'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('level', $case->level) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Estimasi (mnt)
                        <input type="number" name="estimated_time" min="1" max="240" value="{{ old('estimated_time', $case->estimated_time) }}" required class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Urutan
                        <input type="number" name="sort_order" min="0" max="1000" value="{{ old('sort_order', $case->sort_order) }}" class="{{ $field }}">
                    </label>
                </div>

                @if ($case->exists)
                    <label class="flex items-center gap-2 text-sm text-bsds-ink">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $case->is_active)) class="rounded border-bsds-border text-bsds-teal focus:ring-bsds-teal"> Aktif (tampil di katalog siswa)
                    </label>
                @endif

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.cases.index') }}" class="min-h-[44px] inline-flex items-center px-4 text-sm text-bsds-gray hover:underline">Batal</a>
                    <button class="min-h-[44px] px-6 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-sm transition">Simpan</button>
                </div>
            </form>

            @if ($case->exists)
                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-3">
                    <h3 class="font-heading font-semibold text-bsds-navy">Validasi graf</h3>
                    @forelse ($graphErrors as $error)
                        <p class="text-sm text-bsds-error">&bull; {{ $error }}</p>
                    @empty
                        <p class="text-sm text-bsds-success font-semibold">Graf skenario valid (tanpa jalan buntu, yatim, atau siklus).</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-2xl border border-bsds-border p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-heading font-semibold text-bsds-navy">Skenario ({{ $scenarios->count() }})</h3>
                        <a href="{{ route('admin.cases.scenarios.create', $case) }}" class="text-xs font-semibold text-bsds-teal hover:underline">+ Tambah Skenario</a>
                    </div>
                    @foreach ($scenarios as $s)
                        <div class="border border-bsds-border rounded-xl p-4">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="px-2 py-0.5 rounded bg-bsds-navy text-white text-xs font-bold">{{ $s->step_code }}</span>
                                <span class="font-medium text-bsds-ink">{{ $s->title }}</span>
                                <span class="text-xs text-bsds-gray">{{ $s->scenario_type }}{{ $s->is_start ? ' • awal' : '' }}{{ $s->is_ending ? ' • ending' : '' }}</span>
                                <span class="text-xs text-bsds-gray ml-auto">{{ $s->investigationItems->count() }} info &bull; {{ $s->hints->count() }} hint</span>
                                <a href="{{ route('admin.cases.scenarios.edit', [$case, $s]) }}" class="text-xs font-semibold text-bsds-teal hover:underline">Ubah</a>
                            </div>
                            @if ($s->options->isNotEmpty())
                                <ul class="mt-2 text-xs text-bsds-gray space-y-1">
                                    @foreach ($s->options as $o)
                                        <li><span class="font-semibold text-bsds-ink">{{ $o->label }}</span> ({{ $o->consequence_type }}) &rarr; {{ $o->nextScenario?->step_code ?? '?' }} &mdash; {{ \Illuminate\Support\Str::limit($o->option_text, 80) }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                    @if ($scenarios->isEmpty())
                        <p class="text-sm text-bsds-gray text-center py-4">Belum ada skenario. Tambahkan minimal satu skenario awal (is_start) dan satu skenario akhir (is_ending) agar kasus ini bisa diaktifkan.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
```

Perubahan dari versi lama: hapus `@method('PUT')` yang di-hardcode, bungkus field `code`, section "Validasi graf" & "Skenario" dengan `@if ($case->exists)`, bungkus checkbox `is_active` dengan kondisi sama, tambah tombol "+ Tambah Skenario" dan link "Ubah" per baris skenario.

#### `resources/views/admin/scenarios/form.blade.php` (baru)

Ini view paling kompleks di fitur ini — form skenario + 3 repeater Alpine (Opsi, Item Investigasi, Hint). Tulis persis seperti berikut (sudah didesain mengikuti gaya Alpine yang dipakai `simulations/show.blade.php`):

```blade
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

    <div class="py-8 bg-bsds-bg"
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
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('admin._messages')

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
                        <input type="text" name="title" value="{{ old('title', $scenario->title) }}" required maxlength="255" class="{{ $field }}">
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Cerita / Situasi
                        <textarea name="story_text" rows="4" required maxlength="3000" class="{{ $field }}">{{ old('story_text', $scenario->story_text) }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Dialog Pelanggan (opsional)
                        <textarea name="customer_dialogue" rows="2" maxlength="1000" class="{{ $field }}">{{ old('customer_dialogue', $scenario->customer_dialogue) }}</textarea>
                    </label>
                    <label class="block text-sm font-semibold text-bsds-navy">Teks "New Situation" (opsional, tampil saat siswa baru masuk ke skenario ini)
                        <textarea name="new_problem_text" rows="2" maxlength="1000" class="{{ $field }}">{{ old('new_problem_text', $scenario->new_problem_text) }}</textarea>
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
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scenarioForm', (cfg) => ({
                scenarioId: cfg.scenarioId,
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
```

**Detail implementasi yang mudah salah, perhatikan:**
- `x-model="opt.scores['{{ $indicator->id }}']"` — kunci string literal dari PHP di-interpolasi ke JS, hasilnya misalnya `x-model="opt.scores['3']"`. Ini valid Alpine/JS. JANGAN ubah jadi `opt.scores[{{ $indicator->id }}]` tanpa kutip di konteks ini kalau nanti ingin konsisten dengan key string dari `@js()` — PHP `pluck('score','indicator_id')` di-encode `@js()` jadi objek JS dengan key **string** (`{"3": 2}`), dan mengakses `obj['3']` maupun `obj[3]` sama-sama valid di JS (JS selalu meng-coerce ke string untuk key object biasa) — jadi sebenarnya kedua bentuk aman, tapi pakai kutip di atas supaya eksplisit dan tidak ambigu dibaca.
- `<select :name="...scores][{{ $indicator->id }}]`" x-model="...">` — `:name` dari Alpine TIDAK dibutuhkan untuk reaktivitas (Alpine pakai `x-model`), tapi WAJIB ada supaya browser menyertakan field ini saat form native di-submit (Alpine tidak intercept submit, form tetap POST biasa).
- Checkbox `is_relevant`/`is_start`/`is_ending`: saat **tidak dicentang**, browser **tidak mengirim field sama sekali** (bukan `false`). Form Request sudah menangani ini lewat `$this->boolean(...)` di `prepareForValidation()` — pastikan pola ini dipakai persis seperti di `ScenarioRequest` dan untuk tiap baris `investigation_items.*.is_relevant`, controller `itemData()` sudah menangani lewat `(bool) ($data['is_relevant'] ?? false)`.
- `x-cloak` pada section Opsi: pastikan `[x-cloak] { display: none !important; }` sudah ada secara global (cek `resources/css/app.css` — proyek ini sudah pakai `x-cloak` di `simulations/show.blade.php` jadi kemungkinan besar sudah ada; verifikasi sebelum menganggap selesai).

---

## 5. Validasi & Guard Rails — Checklist Keamanan Data

Sebelum fitur ini dianggap selesai, pastikan SEMUA baris berikut benar (tulis test untuk tiap satu, lihat Section 6):

- [ ] Non-admin (student/researcher) mendapat 403 di semua route `admin.cases.create/store` dan `admin.cases.scenarios.*`.
- [ ] Guest (belum login) di-redirect ke login di semua route tersebut.
- [ ] Membuat kasus baru menghasilkan `is_active = false` meskipun tidak ada input yang eksplisit memaksanya (pastikan tidak ada cara mem-bypass dari request body, misal spoofing field `is_active=1` di POST ke `cases.store` — form `store()` TIDAK membaca `is_active` dari request sama sekali, jadi ini otomatis aman).
- [ ] Field `code` tidak bisa diubah lewat `PUT cases/{case}` meskipun field `code` disisipkan manual ke request body (karena `CaseRequest::rules()` tidak mendefinisikan `code` saat PUT, `validated()` tidak akan memuatnya).
- [ ] `step_code` duplikat dalam kasus yang sama ditolak validasi.
- [ ] `step_code` yang sama **boleh** dipakai di kasus BERBEDA (tidak ada unique global).
- [ ] Opsi dengan `next_scenario_id` menunjuk ke skenario milik kasus lain ditolak validasi.
- [ ] Menyimpan skenario dengan `is_start=true` otomatis meng-nonaktifkan `is_start` skenario lain di kasus yang sama.
- [ ] Menyimpan skenario dengan `is_ending=true` menghapus semua opsi yang sebelumnya ada di skenario itu.
- [ ] Mengedit skenario milik kasus A lewat URL `admin/cases/{case-B}/scenarios/{scenario-milik-A}/edit` menghasilkan 404.
- [ ] Mengirim `options[0][id]` milik opsi skenario LAIN saat update skenario ini tidak bisa "mencuri" baris itu — `findOrFail` ter-scope lewat relasi, harus menghasilkan 404 (pastikan ini benar-benar ter-cover test, ini celah IDOR paling halus di fitur ini).
- [ ] Menghapus skenario yang punya `decision_logs` (pernah dipakai sesi nyata) gagal dengan pesan error, bukan 500 atau silent success.
- [ ] Menghapus skenario yang jadi `next_scenario_id` opsi lain berhasil, dan opsi itu `next_scenario_id`-nya jadi `null` (verifikasi lewat assertDatabaseHas), lalu muncul di "Validasi graf" sebagai error.
- [ ] Skor indikator untuk kode `INF` TIDAK PERNAH muncul sebagai field yang bisa diisi admin di form (ambil daftar indikator yang dikirim ke view dan pastikan `INF` tidak ada di dalamnya).
- [ ] Setelah admin membuat kasus baru LENGKAP dari nol (1 skenario awal + minimal 1 opsi per skenario non-ending + minimal 1 skenario ending, semua opsi non-ending py `next_scenario_id` valid) dan tidak ada error graf, mengaktifkan kasus (`is_active=1` di form edit) BERHASIL.
- [ ] Kasus baru yang skenarionya BELUM lengkap (misal belum ada ending) GAGAL diaktifkan dengan pesan error graf yang sama seperti sekarang (`AdminCaseController::update()` sudah menangani ini, jangan diubah).

---

## 6. Rencana Testing Otomatis

Buat file baru `tests/Feature/AdminScenarioBuilderTest.php`. Pola setup ikuti `AdminTest.php` (seed `RoleAndUserSeeder` + buat admin). TIDAK perlu seed `Case01Seeder` untuk sebagian besar test (justru test ini membuktikan kasus bisa dibuat TANPA seeder) — kecuali test yang butuh skenario dengan `decisionLogs` (gunakan `Case01Seeder` + jalankan 1 keputusan lewat alur siswa biasa untuk test guard "tidak bisa hapus skenario berdecisionLogs").

Daftar method test minimal (boleh ditambah, jangan dikurangi):

```php
test_only_admin_can_access_scenario_builder_routes()
test_admin_can_create_case_without_code_mutation_later()
test_case_code_cannot_be_changed_via_update()
test_admin_can_build_a_fully_playable_case_from_scratch()
    // Buat case -> buat scenario S1 (is_start) -> buat scenario END (is_ending)
    // -> edit S1, tambah 1 opsi menuju END, isi skor indikator
    // -> assert graphErrors kosong -> aktifkan case -> assertOk
    // -> login sebagai student -> mulai simulasi case ini -> assertSee title S1
    // -> submit keputusan opsi itu -> assertRedirect ke consequence -> session selesai (karena next = ending)
test_duplicate_step_code_within_same_case_is_rejected()
test_same_step_code_allowed_across_different_cases()
test_option_next_scenario_from_another_case_is_rejected()
test_saving_scenario_as_start_unsets_other_start_flags()
test_marking_scenario_as_ending_clears_its_options()
test_editing_scenario_scoped_to_wrong_case_returns_404()
test_option_id_from_another_scenario_cannot_be_hijacked_via_update()
test_cannot_delete_scenario_with_decision_logs()
test_deleting_scenario_nulls_next_scenario_id_on_referencing_options()
test_inf_indicator_is_never_offered_as_a_scoreable_option_score()
test_case_cannot_be_activated_while_graph_invalid_still_works_for_newly_created_case()
```

Setelah menulis semua test di atas, jalankan:
```bash
php artisan test
```
Target: SEMUA test lama (54 yang sudah ada) + semua test baru PASS. Jangan lanjut ke verifikasi manual kalau ada yang merah.

---

## 7. Rencana Verifikasi Manual di Browser

Gunakan akun **Admin** lewat quick-login di `/login` (sama seperti sesi-sesi sebelumnya). Langkah:

1. Login sebagai Admin → buka **Admin → Kasus** → pastikan tombol "+ Tambah Kasus" muncul.
2. Klik "+ Tambah Kasus" → isi `CASE-TEST`, judul "Uji Coba Builder", deskripsi, level, estimasi 10 menit → Simpan → harus redirect ke halaman edit kasus baru, dengan pesan "Kasus berhasil dibuat...".
3. Di halaman edit, pastikan: tidak ada checkbox "Aktif", bagian "Validasi graf" dan "Skenario" muncul (kosong), tombol "+ Tambah Skenario" ada.
4. Klik "+ Tambah Skenario" → isi `step_code=S1`, judul, cerita, centang "Skenario awal" → Simpan → harus redirect ke halaman edit skenario S1 itu sendiri (bukan balik ke case).
5. Di halaman edit S1 (masih kosong opsi) → klik "+ Tambah Opsi" → isi label `A`, teks opsi, teks konsekuensi, **tujuan masih kosong karena END belum ada** → beri skor 1-2 indikator → Simpan (boleh tanpa tujuan dulu, nanti dilengkapi).
6. Balik ke halaman edit kasus → "+ Tambah Skenario" lagi → buat `step_code=END`, judul, cerita, centang "Skenario akhir (ending)" → Simpan.
7. Balik ke S1 (klik "Ubah" dari daftar skenario) → di opsi A, pilih dropdown "Tujuan" = `END — ...` → Simpan.
8. Di halaman edit kasus, periksa "Validasi graf" → harus "Graf skenario valid".
9. Centang "Aktif" di form metadata kasus → Simpan → harus berhasil tanpa error.
10. Logout, login sebagai **Siswa** (quick-login) → buka **Simulasi** → `CASE-TEST` harus muncul di katalog → klik Start Case → briefing muncul → Mulai Misi → Situasi S1 muncul dengan opsi A yang tadi dibuat → pilih opsi A → Confirm Decision → harus masuk ke halaman konsekuensi lalu (karena tujuannya ending) langsung ke halaman hasil/journey.
11. Bersihkan data uji: hapus `CASE-TEST` dan skenarionya dari DB dev lewat tinker (atau tambahkan tombol hapus kasus kalau sempat — **opsional, di luar lingkup wajib**, lihat Section 9) supaya katalog siswa tidak kotor oleh data percobaan.

Jalankan `php artisan test` SEKALI LAGI setelah seluruh verifikasi manual selesai, untuk memastikan tidak ada side effect dari testing manual yang mengotori test suite (seharusnya tidak, karena test pakai `RefreshDatabase`, tapi tetap konfirmasi).

---

## 8. Urutan Eksekusi yang Disarankan

Kerjakan berurutan, jangan lompat — tiap langkah dibangun di atas langkah sebelumnya:

1. `ScenarioRequest` + ubah `CaseRequest` (murni validasi dulu, belum ada yang memakainya — aman, tidak mungkin merusak apa pun yang sudah jalan).
2. `ScenarioController` (CRUD + `syncChildren`) — method privat `optionData`/`itemData` ditulis dan diuji logikanya dulu secara terpisah kalau perlu, sebelum disambungkan ke route.
3. Routes baru di `web.php`.
4. `AdminCaseController::create()` + `store()`.
5. View `admin/cases/form.blade.php` (restrukturisasi existing) dan `admin/cases/index.blade.php` (tombol + catatan).
6. View baru `admin/scenarios/form.blade.php`.
7. `php artisan test` — pastikan 54 test LAMA masih hijau (perubahan di atas seharusnya 100% additive, tidak menyentuh alur siswa sama sekali).
8. Tulis seluruh test baru di Section 6, jalankan, perbaiki sampai hijau semua.
9. Verifikasi manual penuh di Section 7.
10. Laporkan ke user: ringkasan apa yang dibangun, hasil test, hasil verifikasi manual — **ikuti format laporan yang sama seperti 5 item REMAINING_WORK.md sebelumnya** (poin perubahan, lalu hasil verifikasi, baru tanya lanjut/tidak).

---

## 9. Di Luar Lingkup — Potensi Pengembangan Lanjutan

Dicatat supaya tidak lupa, TAPI JANGAN dikerjakan kecuali user minta eksplisit:

- **Hapus kasus** dari Admin (saat ini hanya ada buat+ubah, tidak ada `cases.destroy`). Butuh guard serupa skenario (blokir jika sudah ada `simulation_sessions`).
- **Duplikasi kasus** (clone CASE-01 jadi CASE-05 sebagai titik awal, lalu diedit) — akan sangat mempercepat pembuatan kasus baru yang mirip pola existing.
- **Validasi kelengkapan baris repeater** (kalau admin isi sebagian field opsi tapi tidak semua, saat ini baris itu akan di-skip total oleh `syncChildren` tanpa peringatan eksplisit ke admin bahwa "opsi ini tidak tersimpan karena tidak lengkap" — pertimbangkan menambah pesan status setelah simpan, misal "2 opsi tidak tersimpan karena belum lengkap").
- **Preview langsung** skenario yang sedang diedit (render seperti tampilan siswa di `simulations/show.blade.php`) tanpa harus benar-benar punya sesi aktif.
- **Export/Import kasus sebagai JSON** untuk backup atau transfer antar environment.
- **Reorder skenario** lewat drag-and-drop (saat ini `sort_order` hanya memengaruhi urutan TAMPILAN di panel admin, bukan urutan simulasi — urutan simulasi murni dari graf `is_start` + `next_scenario_id`, jadi ini murni kosmetik admin, prioritas rendah).
- **Migrasi `Case01Seeder`–`Case04Seeder` ke builder ini** supaya 4 kasus existing bisa diedit lewat UI juga, bukan cuma kasus baru. (Catatan: 4 kasus lama tetap bisa diedit metadata-nya lewat Admin sekarang, dan bisa ditambah skenario baru lewat builder baru ini juga — builder tidak eksklusif untuk kasus baru, dia bekerja untuk SEMUA kasus termasuk yang dibuat seeder. Yang di luar lingkup hanyalah "mengganti isi skenario lama yang sudah ada" lewat form, bukan "menambah skenario baru ke kasus lama" yang sebenarnya sudah otomatis bisa begitu fitur ini selesai.)

---

## 10. Definition of Done

Fitur ini dianggap SELESAI kalau semua ini benar:

- [ ] Semua item checklist Section 5 terbukti benar lewat test otomatis (bukan cuma diklaim).
- [ ] `php artisan test` hijau total (54 test lama + seluruh test baru Section 6).
- [ ] Skenario manual Section 7 berhasil end-to-end: admin membuat kasus baru dari form kosong sampai bisa dimainkan penuh oleh siswa, tanpa menyentuh kode atau seeder sama sekali.
- [ ] Tidak ada regresi di 4 kasus existing (Case01–04) — jalankan ulang `test_case_graph_is_valid` dan seluruh `SimulationTest.php` untuk memastikan builder baru tidak mengubah apa pun di alur lama.
- [ ] Style UI konsisten BSDS (navy/teal/orange, `rounded-2xl`, `font-heading`/Poppins untuk judul) — tidak ada komponen visual baru di luar palet yang sudah ada.
