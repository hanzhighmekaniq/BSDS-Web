Markdown
# PROJECT_PLAN.md: Business Service Decision Simulator (BSDS)

## 1. Ringkasan & Tujuan Projek
**BSDS (Business Service Decision Simulator)** adalah sistem aplikasi web simulasi interaktif berbasis skenario bercabang (*branching scenario*) yang dirancang sebagai media pembelajaran dan instrumen penelitian di bidang pelayanan bisnis (*customer service*).

Berbeda dari sistem evaluasi konvensional bertipe benar/salah, sistem ini mensimulasikan dinamika dunia kerja riil:
- Pengguna dihadapkan pada skenario keluhan pelanggan dan dokumen operasional pendukung (SOP, resi, data stok).
- Setiap tindakan pengguna mengarah pada konsekuensi langsung dan memicu situasi baru yang berbeda.
- Pada akhir simulasi, sistem menyusun *Decision Journey* (peta alur keputusan), evaluasi kesadaran konsekuensi (*consequence awareness*), serta instrumen refleksi diri.

---

## 2. Arsitektur Teknis
- **Backend Framework**: Laravel 11 / 12 (PHP 8.2+)
- **Starter Kit & Autentikasi**: Laravel Breeze (Blade, Tailwind CSS, Alpine.js)
- **Role & Access Control**: `spatie/laravel-permission`
- **Data Export & Reporting**: `maatwebsite/excel`
- **UI & Icons**: Tailwind CSS (Kustomisasi palet warna BSDS) + Blade Heroicons
- **Database Engine**: MySQL / MariaDB (InnoDB, Foreign Key Strict Constraints)

---

## 3. Matriks Multi-Role & Hak Akses
1. **Siswa / Responden (`student`)**:
   - Melihat daftar studi kasus yang aktif.
   - Menjalankan sesi simulasi secara linier/bercabang tanpa manipulasi alur URL.
   - Mengakses panel investigasi (SOP, resi, telepon supervisor).
   - Mengisi form refleksi akhir dan meninjau *Decision Journey*.
2. **Peneliti / Pengajar (`researcher`)**:
   - Mengakses panel analitik hasil simulasi.
   - Memantau durasi, variasi rute percabangan yang dipilih, dan jawaban refleksi siswa.
   - Mengekspor data mentah ke format `.xlsx` / `.csv` untuk analisis statistik penelitian (SPSS/SmartPLS).
3. **Administrator (`admin`)**:
   - Manajemen akun dan penugasan role.
   - Konfigurasi parameter master studi kasus dan skenario.

---

## 4. Skema Basis Data & Branching Engine

[cases] 1───< [scenarios] 1───< [scenario_options]
│                   │ (next_scenario_id)
│                   └───> [scenarios]
├───< [investigation_items]

[users] 1───< [simulation_sessions] 1───< [decision_logs]
│
└───1 [reflections]


### Rincian Tabel:
* **`cases`**: `id`, `code` (string, unique), `title`, `description`, `difficulty`, `estimated_time`, `is_active`, `timestamps`.
* **`scenarios`**: `id`, `case_id`, `step_code`, `title`, `story_text`, `customer_dialogue`, `is_ending` (boolean), `timestamps`.
* **`scenario_options`**: `id`, `scenario_id`, `next_scenario_id` (nullable), `label` (A/B/C), `option_text`, `consequence_text`, `score_weights` (json), `timestamps`.
* **`investigation_items`**: `id`, `scenario_id`, `title`, `type` (sop/receipt/stock/supervisor), `content`, `timestamps`.
* **`simulation_sessions`**: `id`, `user_id`, `case_id`, `current_scenario_id`, `status` (enum: in_progress, completed), `started_at`, `completed_at`, `timestamps`.
* **`decision_logs`**: `id`, `session_id`, `scenario_id`, `option_id`, `confidence_level`, `investigations_viewed` (json), `timestamps`.
* **`reflections`**: `id`, `session_id`, `q1_reason`, `q2_unexpected_consequence`, `q3_future_action`, `timestamps`.

---

## 5. Protokol Keamanan Sistem (Security Measures)

Untuk menjaga integritas data penelitian dan kestabilan sistem:

1. **Session Hijacking & State Manipulation Prevention**:
   - Verifikasi kepemilikan sesi di Controller (`abort_if($session->user_id !== auth()->id(), 403)`).
   - Pengguna tidak dapat melompati skenario secara manual via pengubahan parameter ID di URL. Alur skenario berikutnya diverifikasi berdasarkan `scenario_options.next_scenario_id` dari opsi yang valid di skenario aktif.
2. **Replay & Double-Submission Attack Guard**:
   - Proteksi form menggunakan token CSRF bawaan Laravel (`@csrf`).
   - Kunci status sesi (`status = completed`) saat pengguna mencapai tahap akhir, mencegah pengiriman ulang log keputusan.
3. **Data Sanitization & Injection Defense**:
   - Seluruh jawaban refleksi divalidasi dengan Form Request (`strip_tags`, batas minimum/maksimum karakter).
   - Penggunaan Eloquent ORM mencegah celah SQL Injection.
4. **Access Route Protection**:
   - Pembatasan rute analitik dan ekspor data menggunakan middleware `role:researcher|admin`.

---

## 6. Roadmap Pelaksanaan Bertahap (Execution Phases)

### Fase 1: Fondasi Basis Data & Autentikasi (Selesai/On-Going)
- Inisialisasi Laravel 11/12, Breeze, Spatie Permission.
- Konfigurasi migrasi branching engine dan relasi Eloquent Model.
- Eksekusi seeder role dan akun demonstrasi.

### Fase 2: Logika Engine Percabangan & Seeder Kasus
- Pembuatan `Case01Seeder` (skenario komplain pelanggan, stok barang terbatas, opsi investigasi SOP).
- Pembangunan `SimulationController`:
  - `start()`: Inisialisasi sesi.
  - `show()`: Pemuatan skenario, dialog, dan item investigasi.
  - `submitDecision()`: Pencatatan log, transisi ke konsekuensi, dan pembaruan `current_scenario_id`.
  - `reflection()` & `storeReflection()`: Penutupan sesi simulasi.

### Fase 3: Antarmuka Pengguna (UI/UX)
- Implementasi palet warna BSDS (`#1B2B48`, `#1F8A8A`, `#F4A261`, `#F7F9FC`) di `tailwind.config.js`.
- Penyusunan layout Blade responsif (Desktop & Mobile):
  - Layar simulasi terbelah (Kolom kiri: narasi & investigasi; Kolom kanan: opsi A/B/C dan skala keyakinan).
  - Tampilan *Decision Journey* berbasis bagan urutan node.

### Fase 4: Fitur Analitik & Ekspor Data Penelitian
- Pembuatan class `SimulationResultExport` menggunakan Maatwebsite Excel.
- Tampilan rekapitulasi untuk peneliti (daftar responden, jalur rute, skor kepatuhan, esai refleksi).
- Tombol ekspor `.xlsx` satu-klik untuk olah data skripsi.

### Fase 5: Pengujian (Testing) & Finalisasi
- Pengujian skenario ujung-ke-ujung (*end-to-end*): rute mitigasi tepat vs rute eskalasi masalah.
- Validasi export Excel dan pengujian batas sanitasi teks esai.

---

## 7. Pembaruan Skema & Status (12 Okt 2026)

Dokumen ini diselaraskan dengan `DESAIN WEB PENELITIAN PUTRI.pdf`. Detail lengkap ada di migration `2026_10_12_090000_extend_branching_simulator_tables.php`.

**Keputusan desain:** siswa login dengan akun; ada Practice & Assessment Mode; scope v1 = hint bertahap, what-if, skor indikator (rubrik), progress antar kasus. Indikator saat ini PLACEHOLDER (dari contoh PDF) dan harus diganti dengan indikator final Bab III. Warna utama Navy `#17324D`.

**Tabel tambahan:** `classrooms`, `student_profiles`, `hints`, `hint_usages`, `investigation_views` (pengganti JSON `investigations_viewed`), `indicators`, `option_indicator_scores` (pengganti `score_weights`), `session_indicator_results`, `feedback_templates`.

**Kolom tambahan penting:** `simulation_sessions.mode/attempt_no/duration_seconds/final_outcome`, `decision_logs.step_order/time_spent_seconds/consequence_type`, `scenarios.is_start/scenario_type/decision_no/max_investigations`, `scenario_options.consequence_type`, `reflections` 4 pertanyaan (q1-q4).

**Alur Case 01 (4 keputusan + cabang remedial):**
`S1 -B-> S2 ; S1 -A/C-> S1-R -> S2 ; S2 -B/C-> S3 ; S2 -A-> S2-H -> S3 ; S3 -A/B-> S4 ; S3 -C-> END-BAD ; S4 -B-> END-GOOD / -A-> END-FAIR / -C-> END-BAD`
Validasi graf: `php artisan bsds:validate-case`.

**Status fase:** Fase 1-4 selesai: engine, hint, investigasi, konsekuensi, journey, what-if, refleksi, profil performa; analitik peneliti (`/research`) + ekspor `.xlsx` 4 sheet (Responden, Keputusan, Indikator, Refleksi); panel admin (`/admin`: pengguna, impor siswa CSV/XLSX, kelas, metadata kasus + validasi graf, indikator + umpan balik). Belum dikerjakan: Case 02-04, editor isi skenario lewat UI (isi skenario dikelola lewat seeder), uji coba di browser, dan penggantian indikator placeholder dengan indikator final Bab III.
