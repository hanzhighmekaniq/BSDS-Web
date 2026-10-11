<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\InvestigationItem;
use App\Models\Scenario;
use App\Models\ScenarioOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Case01Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create Case
            $case = CaseModel::updateOrCreate(
                ['code' => 'CASE-01'],
                [
                    'title' => 'Customer Complaint: Salah Barang & Stok Terbatas',
                    'description' => 'Simulasi penanganan keluhan pelanggan akibat kesalahan pengiriman barang dari gudang, di tengah kondisi stok barang pengganti yang sangat terbatas.',
                    'difficulty' => 'medium',
                    'estimated_time' => 15,
                    'is_active' => true,
                ]
            );

            // Clean up existing scenarios under this case for idempotency
            $case->scenarios()->delete();

            // 2. Create Scenarios
            // S1: Initial Scenario
            $s1 = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S1',
                'title' => 'Komplain Barang Salah Kirim',
                'story_text' => 'Pelanggan mendatangi meja customer service dengan raut wajah kesal membawa kotak paket pengiriman kurir instan yang baru saja diterimanya.',
                'customer_dialogue' => 'Mbak/Mas, tolong dong! Saya pesan jaket warna hitam ukuran L untuk acara besok pagi, tapi yang dikirim malah warna merah ukuran M! Ini gimana sih quality control-nya?!',
                'is_ending' => false,
            ]);

            // S2-A: Escalation Scenario (Customer demands supervisor)
            $s2a = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S2-A',
                'title' => 'Pelanggan Menuntut Supervisor',
                'story_text' => 'Akibat merasa diabaikan, pelanggan menggebrak meja layanan dan menarik perhatian pelanggan lain di sekeliling toko.',
                'customer_dialogue' => 'Kalian tidak profesional dan tidak menghargai waktu orang! Panggil supervisor atau store manager kamu sekarang juga!',
                'is_ending' => false,
            ]);

            // S2-B: Investigation Scenario (Stock is limited)
            $s2b = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S2-B',
                'title' => 'Stok Ternyata Terbatas',
                'story_text' => 'Setelah keluhan diverifikasi melalui invoice, Anda memeriksa sistem persediaan gudang dan mendapati stok jaket hitam ukuran L telah habis total.',
                'customer_dialogue' => 'Bagaimana solusinya? Saya harus memakai jaket ini besok pagi untuk seragam acara perusahaan!',
                'is_ending' => false,
            ]);

            // S3-WIN: Good Ending
            $s3Win = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S3-WIN',
                'title' => 'Ending: Resolusi Win-Win Solution',
                'story_text' => 'Pelanggan sangat mengapresiasi solusi proaktif dan kompensasi voucher yang diberikan. Krisis berhasil diubah menjadi pengalaman pelanggan yang positif.',
                'customer_dialogue' => 'Terima kasih banyak atas inisiatif dan bantuannya yang cekatan. Saya hargai tanggung jawab toko ini!',
                'is_ending' => true,
            ]);

            // S3-REFUND: Refund Ending
            $s3Refund = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S3-REFUND',
                'title' => 'Ending: Resolusi Refund (Pelanggan Netral/Kecewa)',
                'story_text' => 'Pengembalian dana 100% diproses secara administratif. Masalah selesai dari sisi toko, namun pelanggan kecewa karena tetap tidak memiliki barang untuk acara besok.',
                'customer_dialogue' => 'Ya sudahlah, saya terima uangnya kembali. Walau saya bingung harus cari ke mana lagi malam-malam begini.',
                'is_ending' => true,
            ]);

            // S3-BAD: Catastrophic Ending
            $s3Bad = Scenario::create([
                'case_id' => $case->id,
                'step_code' => 'S3-BAD',
                'title' => 'Ending: Kegagalan Pelayanan (Eskalasi Ekstrem)',
                'story_text' => 'Pelanggan meninggalkan toko dengan amarah besar, memposting video komplain ke media sosial, dan memberikan ulasan bintang 1 pada Google Review.',
                'customer_dialogue' => 'Pelayanan toko ini luar biasa buruk! Jangan harap saya dan rekan kantor saya mau berbelanja di sini lagi!',
                'is_ending' => true,
            ]);

            // 3. Investigation Items
            // For S1
            InvestigationItem::create([
                'scenario_id' => $s1->id,
                'title' => 'SOP Penanganan Keluhan Pelanggan',
                'type' => 'sop',
                'content' => "1. Terapkan prinsip LAST (Listen, Apologize, Solve, Thank).\n2. Dengarkan tanpa menyela penjelasan pelanggan.\n3. Validasi nomor invoice dan fisik barang secara teliti.\n4. Jangan biarkan pelanggan menunggu tanpa kejelasan informasi.",
            ]);

            InvestigationItem::create([
                'scenario_id' => $s1->id,
                'title' => 'Cek Struk Pembelian & Invoice',
                'type' => 'receipt',
                'content' => "Invoice #INV-BSDS-0982\nNama: Bpk. Hendra\nPesanan: Jaket Windbreaker Black (Size L) - Rp 450.000\nFisik Diterima: Jaket Windbreaker Crimson Red (Size M)\nCatatan Gudang: Staf magang salah memasukkan barcode saat packaging shift malam.",
            ]);

            // For S2-A
            InvestigationItem::create([
                'scenario_id' => $s2a->id,
                'title' => 'Status & Jadwal Supervisor Toko',
                'type' => 'supervisor',
                'content' => "Supervisor sedang rapat koordinasi daring dengan Head Office hingga 30 menit ke depan.\nInstruksi Standing Order: Staf frontline berwenang memberikan diskon pengganti hingga 20% atau opsi kurir kilat tanpa menunggu persetujuan tertulis.",
            ]);

            // For S2-B
            InvestigationItem::create([
                'scenario_id' => $s2b->id,
                'title' => 'Cek Sistem Stok & Alternatif Produk',
                'type' => 'stock',
                'content' => "Status Database Stok:\n- Black Size L: 0 pcs (Habis di gudang pusat & toko).\n- Navy Blue Size L: Tersedia 3 pcs di etalase (Model, bahan, & harga identik).\n- Black Size L Display: Ada 1 pcs di manekin depan (Kondisi 98% baru & bersih).\n- Cabang Mall Barat: Tersedia 1 pcs Black Size L (Bisa dikirim via kurir instan 90 menit).",
            ]);

            // 4. Scenario Options & Branching Links
            // Options for S1
            ScenarioOption::create([
                'scenario_id' => $s1->id,
                'next_scenario_id' => $s2a->id,
                'label' => 'A',
                'option_text' => 'Minta pelanggan menunggu di sofa tunggu karena Anda sedang sibuk menyelesaikan rekonsiliasi laporan shift.',
                'consequence_text' => 'Pelanggan merasa disepelekan dan tersulut emosi karena merasa laporannya diabaikan. Pelanggan menuntut bertemu supervisor.',
                'score_weights' => ['empathy' => 15, 'efficiency' => 20, 'decision' => 10],
            ]);

            ScenarioOption::create([
                'scenario_id' => $s1->id,
                'next_scenario_id' => $s2b->id,
                'label' => 'B',
                'option_text' => 'Dengarkan keluhan dengan empati penuh, sampaikan permohonan maaf, lalu periksa invoice dan fisik jaket yang salah kirim.',
                'consequence_text' => 'Pelanggan mulai tenang karena merasa didengarkan. Masalah terverifikasi bahwa gudang salah mengirimkan produk.',
                'score_weights' => ['empathy' => 90, 'efficiency' => 85, 'decision' => 90],
            ]);

            // Options for S2-A
            ScenarioOption::create([
                'scenario_id' => $s2a->id,
                'next_scenario_id' => $s2b->id,
                'label' => 'A',
                'option_text' => 'Sampaikan maaf tulus atas keterlambatan respon, tawarkan air minum, jelaskan supervisor sedang rapat penting, dan tawarkan untuk langsung mencari solusi penggantian barang sekarang.',
                'consequence_text' => 'Emosi pelanggan mereda setelah diperlakukan manusiawi. Pelanggan bersedia menunggu Anda mengecek ketersediaan barang.',
                'score_weights' => ['empathy' => 80, 'efficiency' => 75, 'decision' => 85],
            ]);

            ScenarioOption::create([
                'scenario_id' => $s2a->id,
                'next_scenario_id' => $s3Bad->id,
                'label' => 'B',
                'option_text' => 'Menyuruh pelanggan menunggu di luar toko sampai rapat supervisor selesai karena itu bukan wewenang Anda.',
                'consequence_text' => 'Pelanggan memviralkan rekaman video kejadian ke media sosial dan menuntut toko secara terbuka.',
                'score_weights' => ['empathy' => 0, 'efficiency' => 10, 'decision' => 0],
            ]);

            // Options for S2-B
            ScenarioOption::create([
                'scenario_id' => $s2b->id,
                'next_scenario_id' => $s3Win->id,
                'label' => 'A',
                'option_text' => 'Tawarkan varian warna Navy Blue Size L yang ready stock dengan kompensasi voucher 20%, atau opsi kirim instan warna Hitam dari cabang Mall Barat dalam 90 menit.',
                'consequence_text' => 'Pelanggan sangat puas dengan penawaran proaktif dan memilih pengiriman instan ke rumahnya. Solusi win-win tercapai.',
                'score_weights' => ['empathy' => 95, 'efficiency' => 90, 'decision' => 95],
            ]);

            ScenarioOption::create([
                'scenario_id' => $s2b->id,
                'next_scenario_id' => $s3Refund->id,
                'label' => 'B',
                'option_text' => 'Jelaskan bahwa stok warna hitam habis total dan langsung proses pengembalian dana (refund) 100%.',
                'consequence_text' => 'Pelanggan menerima uang kembali namun kecewa karena kebutuhannya untuk acara besok pagi tetap tidak terpenuhi.',
                'score_weights' => ['empathy' => 60, 'efficiency' => 70, 'decision' => 60],
            ]);
        });
    }
}
