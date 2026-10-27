# Business Service Decision Simulator — Penjelasan Aplikasi

> Disusun dari aplikasi yang sudah jadi (bukan draf skripsi). Bahan pegangan sempro.

## 1. Ringkasan

Media belajar berbasis web untuk melatih **pengambilan keputusan dalam pelayanan bisnis**. Siswa membaca materi, mengerjakan quiz, lalu menghadapi situasi pelayanan, memilih tindakan, dan melihat dampaknya. Setiap pilihan membawa ke alur berbeda dan dinilai per indikator.

**Teknologi:** Laravel 13 (PHP 8.3), MySQL, Blade + Alpine.js, Tailwind CSS, spatie/laravel-permission (role), maatwebsite/excel (export).

## 2. Alur Pengguna

1. Buka halaman utama, lalu **Daftar** (nama, email, password) atau **Masuk**.
2. Pilih **kelas** di halaman Profil. Dashboard mengingatkan selama kelas belum dipilih.
3. Baca **Materi**, lalu kerjakan **Quiz**.
4. Pilih **Simulasi**: kategori, kasus, lalu mode (Practice atau Assessment).
5. Jalani simulasi dalam urutan berikut:
   - Briefing.
   - Situasi.
   - Investigasi bukti.
   - Pilih tindakan + tingkat keyakinan.
   - Konsekuensi.
   - Lanjut ke situasi berikutnya, sampai ending.
6. Isi **Refleksi**, lalu lihat **Feedback** dan **Decision Journey**.
7. Pantau capaian di **Progress**.

## 3. Role dan Hak Akses

| Role | Bisa apa |
|---|---|
| **Pengunjung** | Lihat halaman utama, daftar akun, masuk |
| **Siswa** | Dashboard, pilih kelas, Materi, Quiz, Simulasi, Progress, Panduan, ubah profil/password |
| **Peneliti** | Analitik penelitian: daftar sesi semua siswa, filter (kelas, kasus, mode, status), detail sesi, **export Excel** |
| **Admin** | Semua akses peneliti, ditambah kelola **pengguna** (tambah/ubah/hapus, import CSV), **kelas**, **materi**, **quiz**, **kasus & skenario** (buat, duplikat, import/export JSON, validasi alur), **indikator & teks feedback** |

Akun yang mendaftar sendiri otomatis menjadi **Siswa**. Role Peneliti dan Admin dibuat oleh admin.

## 4. Fitur dan Fungsinya

| Fitur | Fungsi |
|---|---|
| **Materi** | Bacaan konsep pelayanan sebelum simulasi. Diisi admin. |
| **Quiz** | Pilihan ganda A–D. Skor 0–100 dihitung di server. Ada pembahasan jawaban benar/salah. Boleh diulang, halaman Quiz menampilkan skor terbaik. |
| **Kategori simulasi** | 6 jenis situasi: pelanggan tidak puas, prioritas pelanggan, kesalahan komunikasi, penanganan keluhan, pemenuhan permintaan, kesalahan pelayanan. |
| **Practice Mode** | Mode latihan: ada **petunjuk bertahap** dan perbandingan **"what-if"** (opsi lain yang bisa dipilih). |
| **Assessment Mode** | Mode evaluasi: tanpa petunjuk. |
| **Briefing** | Penjelasan peran dan konteks perusahaan sebelum mulai. |
| **Investigasi bukti** | Siswa membuka SOP, data transaksi, stok, dll. sebelum memutuskan. Jumlahnya **dibatasi**, jadi siswa harus memilih yang relevan. |
| **Alur bercabang** | Setiap opsi mengarah ke situasi berbeda (utama, cabang, remedial, ending). Keputusan kurang tepat membuka situasi pemulihan. |
| **Tingkat keyakinan** | Saat memilih, siswa menyatakan seberapa yakin (5 tingkat). |
| **Konsekuensi** | Dampak langsung dari pilihan ditampilkan setelah memilih. |
| **Refleksi** | 4 pertanyaan: alasan keputusan, keputusan tersulit, konsekuensi tak terduga, tindakan ke depan. Wajib diisi sebelum melihat feedback. |
| **Feedback** | Skor dikelompokkan jadi **Analyze / Decide / Adapt**, plus rekomendasi latihan. |
| **Decision Journey** | Jejak seluruh keputusan yang diambil, skor per indikator, dan statistik (jumlah keputusan, keputusan negatif, investigasi, petunjuk). |
| **Progress** | Rekap kasus selesai dan rata-rata skor per indikator. |
| **Lanjutkan sesi** | Progres tersimpan otomatis dan bisa dilanjutkan. Satu sesi aktif per kasus. |

## 5. Cara Penilaian Simulasi

**6 indikator:**

| Kode | Indikator |
|---|---|
| IDN | Identifikasi masalah |
| INF | Pengumpulan informasi |
| ALT | Pertimbangan alternatif |
| ACT | Pemilihan tindakan |
| CON | Pertimbangan konsekuensi |
| ADP | Adaptasi terhadap masalah baru |

**Perhitungan skor:**
- Setiap opsi jawaban punya **bobot skor per indikator**, ditentukan saat menyusun skenario.
- Skor indikator = total skor opsi yang dipilih ÷ total skor tertinggi yang mungkin di situasi yang dilalui.
- **INF** dihitung dari bukti yang dibuka: relevan +1, tidak relevan −1 (minimal 0 per situasi).

**Kategori hasil:**
- **Baik** jika ≥ 75%.
- **Cukup** jika ≥ 50%.
- **Perlu Latihan** jika < 50%.

**Kelompok feedback:**
- **Analyze** = IDN + INF.
- **Decide** = ALT + ACT.
- **Adapt** = CON + ADP.

## 6. Data yang Tercatat (untuk penelitian)

**Per sesi:**
- Siswa, kelas, kasus, mode.
- Percobaan ke-berapa.
- Waktu mulai/selesai, durasi.
- Hasil akhir.

**Per keputusan:**
- Situasi dan opsi yang dipilih.
- Jenis konsekuensi.
- Tingkat keyakinan.
- **Waktu berpikir (detik)**.

**Aktivitas lain:**
- Bukti yang dibuka.
- Petunjuk yang dipakai.
- Jawaban refleksi.
- Skor per indikator.
- Skor quiz dan jawabannya.

**Export Excel** (oleh peneliti/admin) berisi 4 sheet: Responden, Keputusan, Indikator, Refleksi.

## 7. Keamanan

- Akses halaman dibatasi per role.
- Siswa **hanya** bisa membuka sesi dan hasil quiz miliknya sendiri.
- Kunci jawaban quiz tidak dikirim ke browser. Penilaian dilakukan di server.
- Password disimpan dalam bentuk **hash**. Form dilindungi **CSRF**. Semua input divalidasi.
- Kasus yang alur skenarionya belum valid **tidak bisa diaktifkan**.
- Kasus yang sudah punya data sesi tidak bisa dihapus, supaya data penelitian tidak hilang.

## 8. Pertanyaan yang Mungkin Ditanya Dosen

**Kenapa berbasis web?**
Bisa dibuka dari HP atau laptop tanpa instal, dan data semua siswa terkumpul di satu server untuk dianalisis.

**Apa bedanya dengan kuis biasa?**
Di quiz hanya ada benar/salah. Di simulasi, pilihan mengubah alur cerita, ada konsekuensi, dan penilaiannya per indikator. Prosesnya juga direkam: bukti yang dibuka, waktu berpikir, tingkat keyakinan.

**Bagaimana skor dihitung? Objektif tidak?**
Bobot skor tiap opsi ditetapkan saat menyusun skenario dan dihitung otomatis dengan rumus yang sama untuk semua siswa (lihat bagian 5).

**Kalau siswa salah pilih?**
Siswa diarahkan ke situasi lanjutan atau remedial sesuai dampak pilihannya, jadi tetap belajar memulihkan situasi. Skor indikator terkait akan lebih rendah.

**Bedanya Practice dan Assessment?**
Practice untuk latihan: ada petunjuk bertahap dan perbandingan what-if. Assessment untuk evaluasi: tanpa bantuan.

**Siapa yang membuat skenario? Bisa ditambah?**
Admin, lewat panel. Kasus bisa dibuat baru, diduplikat, atau diimport/export JSON. Sistem memvalidasi alurnya sebelum kasus bisa diaktifkan.

**Kenapa daftar tanpa NIS dan nomor absen?**
Supaya pendaftaran cepat. Kelas dipilih setelah masuk. NIS dan nomor absen tetap bisa diisi admin bila diperlukan.

**Bisa dipakai banyak kelas?**
Bisa. Admin membuat kelas, siswa memilih kelasnya sendiri, dan peneliti bisa memfilter hasil per kelas.

**Data apa yang dipakai untuk penelitian?**
Lihat bagian 6. Semua data bisa diexport ke Excel.

**Bagaimana keamanan data siswa?**
Lihat bagian 7.

**Kenapa ada kategori "Segera hadir"?**
Kategorinya sudah disiapkan, tetapi kasusnya belum dibuat. Admin cukup membuat kasus baru dan memilih kategori tersebut.

**Di halaman utama tertulis 4 indikator, kenapa di sini 6?**
4 indikator itu inti keputusan (IDN, INF, ALT, ACT). Aplikasi menambahkan 2 indikator lanjutan (CON, ADP) untuk menilai dampak dan adaptasi. Indikator dan teks feedback-nya bisa diubah admin.

## 9. Batasan Saat Ini

- Isi **Panduan** belum final.
- 2 kategori (Kesalahan Komunikasi, Pemenuhan Permintaan) belum punya kasus.
- Isi materi masih teks biasa (belum ada gambar atau video).
- Quiz hanya pilihan ganda 4 opsi.
