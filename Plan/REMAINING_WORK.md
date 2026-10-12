# BSDS — Sisa Pekerjaan dari GETSTART.md

Status per 2026-10-12. Semua alur inti (landing, dashboard, simulation library, situasi → keputusan → konsekuensi → masalah baru, decision journey, what-if, reflection, progress, panduan, Case 01–04) **sudah selesai dan terverifikasi jalan**. Dokumen ini mendata 5 hal dari blueprint `Plan/GETSTART.md` yang **belum** dibangun atau belum sesuai, supaya bisa dikerjakan di sesi lain tanpa perlu audit ulang dari nol.

Urutan di bawah = urutan prioritas yang disarankan (ringan → berat).

---

## 1. Halaman Case Briefing / "Case Introduction"

**Blueprint:** `Plan/GETSTART.md` section D (sekitar baris 112–127) dan section 13 "CASE INTRODUCTION" (sekitar baris 887–906). Sebelum masuk Situasi 1, siswa harus melihat halaman ringkas berisi:
- Judul kasus
- 🎯 MISSION (ringkasan misi)
- 👤 YOUR ROLE (role_title)
- ⏱ ESTIMATED TIME
- ⚠️ REMEMBER: "Your decision may affect what happens next."
- Tombol `[ START MISSION → ]`

**Kondisi sekarang:** Klik "Start Case" di `resources/views/simulations/index.blade.php` langsung POST ke `simulations.start` (`app/Http/Controllers/SimulationController.php`), yang membuat session lalu redirect **langsung** ke `simulations.show` (Situasi 1). Tidak ada halaman briefing sama sekali.

**Temuan penting:** kolom `cases.briefing_text` **sudah ada dan sudah diisi** oleh seeder (lihat `database/seeders/Case01Seeder.php` baris ~41, juga Case02/03/04Seeder). Tapi field ini **tidak pernah dirender** di view manapun — grep `briefing_text` di `resources/views/*` nihil. Jadi datanya tinggal dipakai, tidak perlu migrasi/seeder baru.

**Yang perlu dikerjakan:**
- Buat view baru `resources/views/simulations/briefing.blade.php` (pakai `<x-app-layout>`, ikuti pola desain BSDS: card putih, `rounded-2xl`, warna navy/teal/orange, heading `font-heading`).
- Tambah route baru, misal `GET /simulations/cases/{case}/briefing` → `name('simulations.briefing')`, middleware `['auth','role:student']`.
- Ubah tombol "Start Case" di `simulations/index.blade.php` agar mengarah ke halaman briefing dulu (bukan langsung POST start), dan baru dari halaman briefing ada tombol "Mulai Misi →" yang baru melakukan POST ke `simulations.start`.
- Perhatikan: form "Start Case" sekarang mengirim pilihan mode (`practice`/`assessment`) sekaligus submit. Pastikan pilihan mode tetap terbawa ke halaman briefing (misal lewat query string `?mode=practice`) sebelum POST actual start dieksekusi di halaman briefing.
- Tampilkan: `$case->title`, `$case->briefing_text` (gunakan `nl2br(e(...))` atau `{!! nl2br(e($case->briefing_text)) !!}` karena teksnya multi-paragraf dengan `\n`), `$case->role_title`, `$case->estimated_time`, dan baris pengingat "Keputusan Anda dapat memengaruhi situasi berikutnya."

**Acceptance criteria:** Dari halaman daftar simulasi, klik Start Case → tampil halaman briefing kasus terkait → klik "Mulai Misi" → baru masuk ke Situasi 1. Berlaku untuk keempat case (CASE-01 s/d CASE-04).

---

## 2. Halaman "Identitas Siswa" saat registrasi mandiri

**Blueprint:** `Plan/GETSTART.md` section C "PAGE 02 — Identitas Siswa" dan section 9 "WELCOME TO BSDS" — form Nama / Kelas / No. Absen / Kode Peserta sebelum masuk ke sistem.

**Kondisi sekarang:** `resources/views/auth/register.blade.php` hanya mengumpulkan Name/Email/Password/Confirm Password (bawaan Laravel Breeze). Data `StudentProfile` (`classroom_id`, `nis`, `absence_no` — lihat `app/Models/StudentProfile.php`) **hanya bisa diisi lewat panel admin** (`app/Http/Controllers/Admin/UserController.php`, baik form manual maupun impor CSV). Siswa yang mendaftar sendiri lewat tombol "Daftar" di landing page tidak pernah mengisi kelas/NIS/no absen — profilnya kosong selamanya kecuali admin yang melengkapi manual.

**Keputusan desain yang perlu diambil dulu (bahas di sesi pengerjaan):**
- Opsi A: tambahkan field Kelas (dropdown dari tabel `classrooms`), No. Absen, dan NIS/Kode Peserta ke form register, lalu di `RegisteredUserController@store` buat `StudentProfile` sekaligus dengan `User`.
- Opsi B: matikan registrasi publik sepenuhnya (karena alur sebenarnya di sekolah adalah admin yang mengimpor siswa via NIS) dan arahkan tombol "Daftar" ke pesan "hubungi admin/guru untuk akun".

Blueprint condong ke Opsi A (dia menggambarkan form identitas siswa sebagai bagian dari flow resmi), tapi secara realita implementasi saat ini (admin-import jadi sumber kebenaran NIS) lebih cocok dengan Opsi B. **Tanyakan ke user mana yang dipilih sebelum mulai coding.**

**Jika Opsi A dipilih, yang perlu dikerjakan:**
- Tambah field di `auth/register.blade.php`: select Kelas (`<x-text-input>`/`<select>` dari `Classroom::where('is_active', true)->get()`), input No. Absen, input NIS/Kode Peserta.
- Validasi di `RegisteredUserController@store`: `nis` wajib unik (lihat constraint `student_profiles.nis` unique di migration `database/migrations/2026_10_12_090000_extend_branching_simulator_tables.php`).
- Setelah `User::create(...)` dan `assignRole('student')`, buat juga `StudentProfile::create([...])`.
- Perhatikan pola validasi serupa sudah ada di `app/Http/Requests/Admin/` — cek `UserRequest` kalau ada, untuk konsistensi aturan.

**Acceptance criteria:** Siswa baru yang mendaftar sendiri dari landing page langsung punya `StudentProfile` terisi (kelas, no. absen, NIS), terlihat benar di panel Admin → Pengguna.

---

## 3. Informasi (SOP/Stok/dst.) sebagai modal, bukan inline

**Blueprint:** section 20 "MODAL INFORMATION" — saat siswa klik item informasi, muncul sebagai popup/modal di tengah layar.

**Kondisi sekarang:** `resources/views/simulations/show.blade.php` (sekitar baris 129–154) menampilkan info sebagai accordion yang expand inline di sidebar kanan (`x-show` Alpine), bukan modal. Fungsinya sudah benar (siswa tetap harus klik untuk membuka info, batas maksimal investigasi tetap berlaku), ini murni beda pilihan tampilan.

**Catatan:** Ini **prioritas paling rendah / opsional** — bukan bug, fungsinya sudah lengkap. Komponen `resources/views/components/modal.blade.php` sudah ada di project (dipakai di halaman Delete Account profile) dan bisa dipakai ulang kalau user memang mau tampilan modal.

**Jika dikerjakan:** ganti blok investigasi di `show.blade.php` memakai `<x-modal>` yang sudah ada, trigger dengan `$dispatch('open-modal', 'info-{{ $item->id }}')` mengikuti pola yang sudah dipakai di `resources/views/profile/partials/delete-user-form.blade.php`.

---

## 4. Halaman Feedback tersendiri (3 kartu Analyze/Decide/Adapt)

**Blueprint:** section 27 "FEEDBACK PAGE" — setelah reflection, ada halaman khusus dengan 3 kartu:
- 🔎 ANALYZE — "Anda mampu mengidentifikasi masalah utama."
- ⚖️ DECIDE — "Anda mampu memilih tindakan berdasarkan informasi yang tersedia."
- 🔄 ADAPT — "Anda mampu menyesuaikan tindakan ketika situasi berubah."
- Lalu rekomendasi latihan.

**Kondisi sekarang:** Tidak ada route/view terpisah bernama feedback. Feedback yang ada sekarang adalah blok "Profil Performa" di `resources/views/simulations/journey.blade.php` (baris ~57–77) — menampilkan ke-6 indikator Bab III (Identifikasi masalah, Pengumpulan informasi, Pertimbangan alternatif, Pemilihan tindakan, Pertimbangan konsekuensi, Adaptasi) satu per satu dengan bar progress, **bukan** diringkas jadi 3 kartu Analyze/Decide/Adapt.

**Catatan penting sebelum mengerjakan:** Blueprint sendiri mengakui di section P bahwa "kategori di atas adalah contoh desain antarmuka" dan indikator final harus disesuaikan dengan instrumen penelitian Bab III skripsi penggunanya. Proyek ini **sudah memilih** memakai 6 indikator Bab III yang lebih rinci (lihat `database/seeders/IndicatorSeeder.php`) ketimbang 3 kategori generik Analyze/Decide/Adapt dari mockup awal blueprint. Jadi ini kemungkinan **perubahan desain yang disengaja**, bukan murni kelalaian — konfirmasi ke user apakah mereka masih mau halaman feedback terpisah dengan 3 kartu itu, atau sudah cukup dengan Profil Performa yang granular di journey page.

**Jika tetap dikerjakan:** buat route baru `simulations.feedback` + view `resources/views/simulations/feedback.blade.php`, tampil setelah submit reflection (ubah redirect di `SimulationController@storeReflection`), isi 3 kartu dikonstruksi dari rata-rata skor indikator yang dikelompokkan (misal: Analyze = IDN+INF, Decide = ALT+ACT, Adapt = CON+ADP) — perlu keputusan pengelompokan ini didiskusikan dulu.

---

## 5. Micro-interaction "Decision Locked"

**Blueprint:** section 34 "MICRO-INTERACTION" — begitu keputusan dikonfirmasi, teks tombol berubah dari "Confirm Decision" menjadi "Decision Locked" sesaat sebelum redirect ke halaman konsekuensi.

**Kondisi sekarang:** Tidak ditemukan di `resources/views/simulations/show.blade.php` — tombol submit langsung redirect tanpa perubahan state/teks.

**Yang perlu dikerjakan:** tambahkan Alpine state kecil, misal `x-text="submitting ? 'Decision Locked' : 'Confirm Decision →'"` dan `:disabled="submitting"` pada tombol submit, di-set `submitting = true` saat form di-submit (sebelum network request/redirect selesai). Murni polish UX, tidak menyentuh backend.

**Acceptance criteria:** Saat tombol "Confirm Decision" diklik (dengan opsi & confidence terisi), teks tombol berubah sesaat menjadi "Decision Locked" sebelum halaman konsekuensi tampil.

---

## Hal yang SENGAJA tidak masuk daftar ini

- Email verification — sudah dihapus total (keputusan final, bukan gap).
- Sidebar navigasi kiri — sudah diganti dari navbar atas sesuai permintaan user, bukan dari blueprint asli (blueprint minta navbar atas, tapi user eksplisit minta sidebar kiri).
- Halaman admin & research — di luar cakupan blueprint GETSTART.md (fitur tambahan untuk kebutuhan guru/peneliti), tidak perlu dicocokkan ke blueprint siswa.
