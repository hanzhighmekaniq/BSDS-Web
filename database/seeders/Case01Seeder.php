<?php

namespace Database\Seeders;

use App\Models\CaseModel;
use App\Models\Hint;
use App\Models\Indicator;
use App\Models\InvestigationItem;
use App\Models\OptionIndicatorScore;
use App\Models\Scenario;
use App\Models\ScenarioOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Case 01 - Menangani Keluhan Pelanggan (4 titik keputusan + cabang remedial).
 * Skor indikator per opsi (0-2) adalah PLACEHOLDER sampai indikator Bab III final.
 *
 * Alur:
 *   S1 --B--> S2 ; S1 --A/C--> S1-R --> S2
 *   S2 --B/C--> S3 ; S2 --A--> S2-H --> S3
 *   S3 --A/B--> S4 ; S3 --C--> END-BAD
 *   S4 --B--> END-GOOD ; S4 --A--> END-FAIR ; S4 --C--> END-BAD
 */
class Case01Seeder extends Seeder
{
    public function run(): void
    {
        $this->call(IndicatorSeeder::class);

        DB::transaction(function () {
            $indicators = Indicator::pluck('id', 'code');

            $case = CaseModel::updateOrCreate(
                ['code' => 'CASE-01'],
                [
                    'title' => 'Customer Complaint: Menangani Keluhan Pelanggan',
                    'description' => 'Tangani keluhan pelanggan yang menerima produk salah, tentukan solusi di tengah stok terbatas, dan pertanggungjawabkan keputusan Anda kepada supervisor.',
                    'level' => 'foundation',
                    'category' => 'penanganan_keluhan',
                    'role_title' => 'Customer Service Officer',
                    'briefing_text' => "Selamat datang di PT Nusantara Business Service.\n\nHari ini Anda berperan sebagai Customer Service Officer. Tugas Anda adalah menangani kebutuhan pelanggan, mengidentifikasi masalah, mempertimbangkan berbagai alternatif tindakan, dan menentukan solusi yang sesuai dengan kondisi perusahaan.\n\nPerhatikan SOP pelayanan, kondisi pelanggan, ketersediaan produk, dan kepentingan perusahaan. Keputusan Anda dapat memengaruhi apa yang terjadi selanjutnya.",
                    'estimated_time' => 15,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );

            // Idempotent: hapus skenario lama (opsi, investigasi, hint ikut terhapus via cascade).
            $case->scenarios()->delete();

            $make = fn (array $attrs) => Scenario::create($attrs + ['case_id' => $case->id]);

            $s1 = $make([
                'step_code' => 'S1', 'scenario_type' => 'main', 'is_start' => true, 'decision_no' => 1, 'sort_order' => 1,
                'title' => 'Pelanggan Datang dengan Keluhan',
                'story_text' => 'Pukul 10.15. Ruang pelayanan cukup ramai (antrian 4 pelanggan). Seorang pelanggan datang membawa produk yang baru diterimanya dan mengatakan bahwa produk tersebut berbeda dengan yang dipesan. Bukti transaksi tersedia.',
                'customer_dialogue' => 'Saya sudah menunggu beberapa hari. Kenapa barang yang datang malah salah?',
            ]);
            $s1r = $make([
                'step_code' => 'S1-R', 'scenario_type' => 'remedial', 'decision_no' => 1, 'sort_order' => 2,
                'title' => 'Pelanggan Mulai Kehilangan Kepercayaan',
                'story_text' => 'Tindakan awal Anda membuat pelanggan semakin kecewa. Ia mengatakan sudah menunggu cukup lama dan merasa tidak mendapatkan pelayanan yang baik.',
                'customer_dialogue' => 'Dari tadi saya hanya diminta menunggu dan dioper-oper. Apa tidak ada yang mau menangani keluhan saya?',
                'new_problem_text' => 'Anda menyadari pelanggan mulai kehilangan kepercayaan. Tentukan tindakan Anda selanjutnya.',
            ]);
            $s2 = $make([
                'step_code' => 'S2', 'scenario_type' => 'main', 'decision_no' => 2, 'max_investigations' => 2, 'sort_order' => 3,
                'title' => 'Cari Tahu Sebelum Bertindak',
                'story_text' => 'Transaksi menunjukkan bahwa produk yang dikirim memang berbeda dengan pesanan. Anda perlu menentukan langkah berikutnya, tetapi belum tahu apakah produk yang benar masih tersedia. Anda dapat memeriksa maksimal 2 sumber informasi.',
                'customer_dialogue' => 'Kalau memang masih ada, saya ingin langsung ditukar sekarang.',
                'new_problem_text' => 'Kesalahan pesanan terkonfirmasi. Produk yang benar mungkin terbatas.',
            ]);
            $s2h = $make([
                'step_code' => 'S2-H', 'scenario_type' => 'remedial', 'decision_no' => 2, 'sort_order' => 4,
                'title' => 'Produk Ternyata Sudah Dipesan Orang Lain',
                'story_text' => 'Beberapa menit setelah produk diberikan, pelanggan lain datang untuk mengambil produk yang sebelumnya sudah dipesannya. Anda harus menjelaskan mengapa produk tersebut tidak tersedia.',
                'customer_dialogue' => 'Saya sudah memesan ini sejak kemarin dan sudah dikonfirmasi. Kenapa sekarang tidak ada?',
                'new_problem_text' => 'Keputusan yang menyelesaikan masalah pertama menimbulkan masalah baru.',
            ]);
            $s3 = $make([
                'step_code' => 'S3', 'scenario_type' => 'main', 'decision_no' => 3, 'sort_order' => 5,
                'title' => 'Pelanggan Menolak Solusi',
                'story_text' => 'Anda telah menjelaskan kondisi stok dan menawarkan alternatif penyelesaian. Namun pelanggan menolak alternatif tersebut.',
                'customer_dialogue' => 'Saya tidak mau alternatif. Saya mau produk yang saya pesan. Kalau tidak bisa, kembalikan uang saya.',
                'new_problem_text' => 'Pelanggan menolak solusi yang ditawarkan dan meminta pengembalian dana.',
            ]);
            $s4 = $make([
                'step_code' => 'S4', 'scenario_type' => 'main', 'decision_no' => 4, 'max_investigations' => 2, 'sort_order' => 6,
                'title' => 'Supervisor Bertanya',
                'story_text' => 'Supervisor datang menghampiri Anda dan pelanggan. Anda dapat memeriksa informasi tambahan terlebih dahulu sebelum menjelaskan keputusan Anda.',
                'customer_dialogue' => 'Apa yang terjadi dengan pelanggan ini dan mengapa Anda memilih solusi tersebut?',
            ]);
            $good = $make([
                'step_code' => 'END-GOOD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 7,
                'title' => 'Penyelesaian yang Tepat',
                'story_text' => 'Supervisor menilai penanganan Anda sudah sesuai prosedur: masalah teridentifikasi, informasi diperiksa, dan solusi ditawarkan dengan mempertimbangkan kondisi pelanggan serta perusahaan. Pelanggan menerima penyelesaian dan keluhan ditutup.',
                'customer_dialogue' => 'Terima kasih sudah membantu dengan jelas dan tidak membuat saya berputar-putar.',
            ]);
            $fair = $make([
                'step_code' => 'END-FAIR', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 8,
                'title' => 'Selesai, Tetapi Masih Ada Catatan',
                'story_text' => 'Masalah pelanggan selesai, namun supervisor mencatat bahwa keputusan Anda lebih banyak mengikuti permintaan pelanggan daripada pertimbangan prosedur dan kepentingan perusahaan.',
                'customer_dialogue' => 'Ya sudah, yang penting urusan saya selesai.',
            ]);
            $bad = $make([
                'step_code' => 'END-BAD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 9,
                'title' => 'Keluhan Tidak Terselesaikan',
                'story_text' => 'Keluhan pelanggan tidak terselesaikan dan ia meninggalkan toko dengan kecewa. Ini bagian dari simulasi: gunakan Decision Journey untuk melihat titik mana yang dapat dilakukan berbeda.',
                'customer_dialogue' => 'Pelayanan seperti ini membuat saya tidak ingin berbelanja di sini lagi.',
            ]);

            // Item investigasi
            $item = fn (Scenario $s, string $type, string $title, string $content, bool $relevant, int $order) => InvestigationItem::create([
                'scenario_id' => $s->id, 'type' => $type, 'title' => $title, 'content' => $content,
                'is_relevant' => $relevant, 'sort_order' => $order,
            ]);

            $item($s1, 'sop', 'SOP Penanganan Keluhan Pelanggan', "1. Dengarkan pelanggan tanpa menyela.\n2. Sampaikan permohonan maaf.\n3. Verifikasi bukti transaksi.\n4. Identifikasi masalah.\n5. Tawarkan solusi yang sesuai.\n6. Dokumentasikan penyelesaian.", true, 1);
            $item($s1, 'receipt', 'Bukti Transaksi Pelanggan', "Invoice #INV-BSDS-0982\nPesanan: Jaket Windbreaker Black (L)\nDikirim: Jaket Windbreaker Crimson Red (M)\nCatatan gudang: kesalahan barcode saat packaging.", true, 2);

            $item($s2, 'stock', 'Periksa Stok', "Produk yang seharusnya diterima pelanggan hanya tersisa 1 unit.\nStatus: sudah DIPESAN pelanggan lain (pengambilan hari ini pukul 14.00).", true, 1);
            $item($s2, 'sop', 'Lihat SOP Penggantian Barang', "Penggantian produk harus melalui verifikasi transaksi.\nProduk yang sudah dipesan pelanggan lain tidak boleh dialihkan tanpa persetujuan.\nJika stok tidak tersedia, tawarkan alternatif sesuai prosedur.", true, 2);
            $item($s2, 'transaction', 'Data Transaksi Pesanan', "Pesanan atas nama pelanggan: terverifikasi.\nKesalahan berasal dari pihak gudang perusahaan.", true, 3);
            $item($s2, 'supervisor', 'Hubungi Supervisor', 'Supervisor sedang menangani pelanggan lain dan meminta Anda memeriksa SOP terlebih dahulu.', false, 4);

            $item($s4, 'sop', 'SOP Pengembalian Dana', "Pengembalian dana dapat dilakukan jika kesalahan berasal dari perusahaan dan transaksi memenuhi ketentuan.\nPetugas wajib mendokumentasikan alasan penyelesaian.", true, 1);
            $item($s4, 'transaction', 'Data Transaksi Pelanggan', "Kesalahan berasal dari perusahaan (gudang).\nTransaksi memenuhi ketentuan pengembalian dana.", true, 2);
            $item($s4, 'supervisor', 'Hubungi Supervisor', 'Supervisor sudah berada di tempat dan menunggu penjelasan Anda.', false, 3);

            // Hint bertahap (tidak memberi jawaban langsung)
            $hint = fn (Scenario $s, int $level, string $text) => Hint::create(['scenario_id' => $s->id, 'level' => $level, 'hint_text' => $text]);
            $hint($s1, 1, 'Pertimbangkan informasi yang sudah Anda miliki dan kondisi pelanggan saat ini.');
            $hint($s1, 2, 'Apa yang paling dibutuhkan pelanggan yang sedang kecewa sebelum masalahnya dapat diselesaikan?');
            $hint($s1r, 1, 'Apakah tindakan Anda sebelumnya sudah menjawab kebutuhan pelanggan?');
            $hint($s2, 1, 'Informasi apa yang dapat membantu Anda memahami situasi lebih baik sebelum menjanjikan sesuatu?');
            $hint($s2, 2, 'Apakah solusi untuk pelanggan ini dapat menimbulkan masalah bagi pihak lain?');
            $hint($s2h, 1, 'Pikirkan dampak keputusan sebelumnya terhadap pelanggan yang lain.');
            $hint($s3, 1, 'Pelanggan menolak solusi. Apa lagi pilihan penyelesaian yang tersedia menurut prosedur?');
            $hint($s3, 2, 'Bagaimana Anda dapat melibatkan pelanggan dalam menentukan penyelesaian?');
            $hint($s4, 1, 'Jelaskan keputusan Anda berdasarkan masalah, informasi, dan prosedur.');

            // Opsi + skor indikator
            $opt = function (Scenario $s, string $label, ?Scenario $next, string $text, string $consequence, string $type, array $scores, ?string $tag = null) use ($indicators) {
                $o = ScenarioOption::create([
                    'scenario_id' => $s->id,
                    'next_scenario_id' => $next?->id,
                    'label' => $label,
                    'option_text' => $text,
                    'consequence_text' => $consequence,
                    'consequence_type' => $type,
                    'outcome_tag' => $tag,
                    'sort_order' => ord($label),
                ]);
                foreach ($scores as $code => $score) {
                    OptionIndicatorScore::create(['option_id' => $o->id, 'indicator_id' => $indicators[$code], 'score' => $score]);
                }

                return $o;
            };

            // S1 - Keputusan 1
            $opt($s1, 'A', $s1r, 'Meminta pelanggan menunggu karena Anda sedang menangani pelanggan lain.',
                'Pelanggan terlihat semakin kecewa. Ia merasa keluhannya diabaikan dan mulai kehilangan kepercayaan.', 'negative', ['IDN' => 0, 'ACT' => 0], 'abaikan_pelanggan');
            $opt($s1, 'B', $s2, 'Mendengarkan keluhan pelanggan, meminta maaf, kemudian memeriksa bukti transaksi.',
                'Pelanggan merasa lebih tenang karena keluhannya diperhatikan. Setelah memeriksa transaksi, Anda menemukan bahwa data pesanan dan produk yang dikirim memang berbeda.', 'positive', ['IDN' => 2, 'ACT' => 2], 'dengar_verifikasi');
            $opt($s1, 'C', $s1r, 'Mengarahkan pelanggan langsung ke bagian gudang karena masalah berkaitan dengan produk.',
                'Pelanggan merasa dioper dan harus mengulang ceritanya di tempat lain. Kepercayaannya menurun.', 'negative', ['IDN' => 1, 'ACT' => 0], 'alihkan_gudang');

            // S1-R - Keputusan 1 (remedial)
            $opt($s1r, 'A', $s2, 'Tetap meminta pelanggan menunggu.',
                'Pelanggan hampir pergi. Supervisor akhirnya membantu memverifikasi transaksi dan menemukan bahwa pesanan memang salah dikirim, tetapi pelanggan masih sangat kecewa.', 'negative', ['ADP' => 0, 'ACT' => 0]);
            $opt($s1r, 'B', $s2, 'Menghentikan pekerjaan sementara dan menangani keluhan pelanggan.',
                'Pelanggan kembali tenang. Anda memeriksa transaksi dan menemukan bahwa data pesanan dan produk yang dikirim memang berbeda.', 'positive', ['ADP' => 2, 'ACT' => 2]);
            $opt($s1r, 'C', $s2, 'Meminta pelanggan mengisi formulir keluhan sendiri.',
                'Pelanggan mengisi formulir dengan enggan. Masalah tercatat dan terverifikasi, tetapi pelanggan merasa harus berusaha sendiri.', 'neutral', ['ADP' => 1, 'ACT' => 1]);

            // S2 - Keputusan 2
            $opt($s2, 'A', $s2h, 'Langsung menjanjikan penukaran produk yang benar hari ini dan memberikannya kepada pelanggan.',
                'Pelanggan puas karena mendapat produk yang diinginkan. Tetapi beberapa menit kemudian pelanggan lain datang untuk mengambil produk yang sebelumnya sudah dipesannya.', 'negative', ['ALT' => 0, 'CON' => 0, 'ACT' => 0], 'hidden_consequence_stok');
            $opt($s2, 'B', $s3, 'Menjelaskan kondisi stok dan mencari alternatif penyelesaian sesuai prosedur.',
                'Anda menjelaskan kondisi stok dan menawarkan beberapa alternatif. Pelanggan mendengarkan, tetapi belum sepenuhnya menerima.', 'positive', ['ALT' => 2, 'CON' => 2, 'ACT' => 2], 'jelaskan_stok');
            $opt($s2, 'C', $s3, 'Menyimpan produk tersebut untuk pelanggan yang sudah memesannya dan meminta pelanggan komplain menunggu.',
                'Hak pelanggan pemesan terlindungi, tetapi pelanggan yang komplain merasa tidak ada solusi yang jelas untuknya.', 'neutral', ['ALT' => 1, 'CON' => 2, 'ACT' => 1], 'simpan_stok');

            // S2-H - Keputusan 2 (remedial)
            $opt($s2h, 'A', $s3, 'Mengatakan bahwa ini kesalahan sistem dan bukan tanggung jawab Anda.',
                'Pelanggan pemesan semakin kesal. Suasana pelayanan menjadi tegang dan perhatian pelanggan lain tertuju pada Anda.', 'negative', ['ADP' => 0, 'CON' => 0]);
            $opt($s2h, 'B', $s3, 'Meminta maaf, menjelaskan situasinya, dan menawarkan alternatif atau prioritas pengiriman ulang.',
                'Pelanggan pemesan menghargai kejujuran Anda dan bersedia mempertimbangkan alternatif yang ditawarkan.', 'positive', ['ADP' => 2, 'CON' => 2]);
            $opt($s2h, 'C', $s3, 'Meminta pelanggan pertama mengembalikan produk yang sudah diberikan.',
                'Pelanggan pertama merasa diperlakukan tidak adil, tetapi stok berhasil dikembalikan.', 'neutral', ['ADP' => 1, 'CON' => 1]);

            // S3 - Keputusan 3
            $opt($s3, 'A', $s4, 'Memaksa pelanggan menerima produk alternatif.',
                'Pelanggan marah dan meminta bertemu supervisor. Supervisor datang untuk meminta penjelasan.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'paksa_alternatif');
            $opt($s3, 'B', $s4, 'Menjelaskan pilihan penyelesaian yang tersedia dan meminta pelanggan menentukan alternatif yang paling sesuai.',
                'Pelanggan meminta pengembalian dana. Anda perlu memastikan apakah hal itu diperbolehkan. Supervisor datang dan menanyakan situasinya.', 'positive', ['ALT' => 2, 'ADP' => 2, 'ACT' => 2], 'tawarkan_pilihan');
            $opt($s3, 'C', $bad, 'Mengatakan bahwa perusahaan tidak dapat berbuat apa-apa.',
                'Pelanggan meninggalkan toko dengan kecewa. Keluhan tidak terselesaikan dan kepercayaan pelanggan hilang.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'menyerah');

            // S4 - Keputusan 4
            $opt($s4, 'A', $fair, 'Saya hanya mengikuti permintaan pelanggan.',
                'Supervisor mencatat bahwa keputusan Anda belum menunjukkan pertimbangan prosedur dan kepentingan perusahaan.', 'neutral', ['IDN' => 0, 'CON' => 0, 'ACT' => 1]);
            $opt($s4, 'B', $good, 'Saya mengidentifikasi kesalahan transaksi, memeriksa prosedur, mempertimbangkan kondisi pelanggan, kemudian menawarkan solusi yang sesuai.',
                'Supervisor menyetujui penyelesaian Anda karena didasarkan pada identifikasi masalah, prosedur, dan kondisi pelanggan.', 'positive', ['IDN' => 2, 'CON' => 2, 'ACT' => 2]);
            $opt($s4, 'C', $bad, 'Bagian lain yang melakukan kesalahan, jadi saya hanya meneruskan masalah.',
                'Supervisor menilai Anda menghindari tanggung jawab. Keluhan pelanggan tidak terselesaikan dengan baik.', 'negative', ['IDN' => 0, 'CON' => 0, 'ACT' => 0]);
        });
    }
}
