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
 * Case 02 - Pelanggan Marah melalui Telepon (4 titik keputusan + cabang remedial).
 * Fokus: komunikasi, pengendalian situasi, pemilihan respons.
 * Skor indikator per opsi (0-2) adalah PLACEHOLDER sampai indikator Bab III final.
 *
 * Alur:
 *   S1 --B--> S2 ; S1 --A/C--> S1-R --> S2
 *   S2 --B/C--> S3 ; S2 --A--> S2-H --> S3
 *   S3 --A/B--> S4 ; S3 --C--> END-BAD
 *   S4 --B--> END-GOOD ; S4 --A--> END-FAIR ; S4 --C--> END-BAD
 */
class Case02Seeder extends Seeder
{
    public function run(): void
    {
        $this->call(IndicatorSeeder::class);

        DB::transaction(function () {
            $indicators = Indicator::pluck('id', 'code');

            $case = CaseModel::updateOrCreate(
                ['code' => 'CASE-02'],
                [
                    'title' => 'Difficult Telephone Customer: Pelanggan Marah melalui Telepon',
                    'description' => 'Tangani telepon dari pelanggan yang marah karena pesanan terlambat, kendalikan emosi percakapan, dan temukan solusi yang adil tanpa melanggar kebijakan perusahaan.',
                    'level' => 'developing',
                    'category' => 'pelanggan_tidak_puas',
                    'role_title' => 'Customer Service Officer (Call Center)',
                    'briefing_text' => "Selamat datang kembali di PT Nusantara Business Service.\n\nHari ini Anda bertugas di meja telepon. Seorang pelanggan menelepon dalam keadaan emosi karena pesanannya belum sampai. Berbeda dengan keluhan tatap muka, Anda tidak dapat menunjukkan ekspresi atau bahasa tubuh — hanya nada suara dan pilihan kata yang Anda miliki.\n\nPerhatikan cara Anda merespons emosi pelanggan, informasi yang tersedia tentang status pengiriman, dan kebijakan kompensasi perusahaan. Keputusan Anda dapat meredakan atau justru memperburuk situasi.",
                    'estimated_time' => 15,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            );

            // Idempotent: hapus skenario lama (opsi, investigasi, hint ikut terhapus via cascade).
            $case->scenarios()->delete();

            $make = fn (array $attrs) => Scenario::create($attrs + ['case_id' => $case->id]);

            $s1 = $make([
                'step_code' => 'S1', 'scenario_type' => 'main', 'is_start' => true, 'decision_no' => 1, 'sort_order' => 1,
                'title' => 'Telepon Masuk dengan Nada Marah',
                'story_text' => 'Telepon berdering. Seorang pelanggan berbicara dengan nada tinggi sejak detik pertama. Pesanannya dijanjikan sampai kemarin, tetapi belum juga diterima.',
                'customer_dialogue' => 'Ini sudah 3 hari saya tunggu! Kalian bilang kemarin pasti sampai! Kalau tidak ada kejelasan sekarang, saya laporkan ini di media sosial!',
            ]);
            $s1r = $make([
                'step_code' => 'S1-R', 'scenario_type' => 'remedial', 'decision_no' => 1, 'sort_order' => 2,
                'title' => 'Pelanggan Merasa Diabaikan',
                'story_text' => 'Pelanggan merasa dipotong pembicaraannya dan semakin tersulut emosinya.',
                'customer_dialogue' => 'Saya belum selesai bicara! Kenapa malah mau dialihkan atau dijelaskan prosedur dulu?',
                'new_problem_text' => 'Anda menyadari nada bicara pelanggan semakin tinggi. Tentukan cara Anda melanjutkan percakapan.',
            ]);
            $s2 = $make([
                'step_code' => 'S2', 'scenario_type' => 'main', 'decision_no' => 2, 'max_investigations' => 2, 'sort_order' => 3,
                'title' => 'Cari Tahu Sebelum Bertindak',
                'story_text' => 'Pelanggan mulai sedikit lebih tenang. Anda meminta nomor pesanan untuk memeriksa status pengiriman sebelum menjanjikan apa pun.',
                'customer_dialogue' => 'Jadi kapan pastinya sampai? Saya butuh kepastian, bukan permintaan maaf saja.',
                'new_problem_text' => 'Keterlambatan disebabkan cuaca ekstrim, bukan kesalahan toko — namun pelanggan tetap mendesak kepastian dan kompensasi.',
            ]);
            $s2h = $make([
                'step_code' => 'S2-H', 'scenario_type' => 'remedial', 'decision_no' => 2, 'sort_order' => 4,
                'title' => 'Janji yang Belum Disetujui',
                'story_text' => 'Anda sudah menjanjikan refund penuh kepada pelanggan. Beberapa menit kemudian, bagian keuangan menghubungi Anda karena refund sebesar itu memerlukan persetujuan supervisor terlebih dahulu.',
                'customer_dialogue' => 'Refund ini belum disetujui dan akan bermasalah di laporan keuangan. Kenapa langsung dijanjikan ke pelanggan?',
                'new_problem_text' => 'Keputusan yang menenangkan pelanggan ternyata menimbulkan masalah administrasi baru.',
            ]);
            $s3 = $make([
                'step_code' => 'S3', 'scenario_type' => 'main', 'decision_no' => 3, 'sort_order' => 5,
                'title' => 'Pelanggan Mengancam Posting di Media Sosial',
                'story_text' => 'Anda telah menjelaskan penyebab keterlambatan dan menawarkan kompensasi sesuai kebijakan. Namun pelanggan belum puas.',
                'customer_dialogue' => 'Voucher itu tidak menyelesaikan masalah saya! Saya butuh barangnya, bukan kompensasi. Kalau tidak ada solusi, saya posting ini sekarang juga.',
            ]);
            $s4 = $make([
                'step_code' => 'S4', 'scenario_type' => 'main', 'decision_no' => 4, 'sort_order' => 6,
                'title' => 'Supervisor Meminta Laporan',
                'story_text' => 'Panggilan ini tercatat sebagai komplain berisiko eskalasi media sosial. Supervisor shift meminta laporan singkat dari Anda.',
                'customer_dialogue' => 'Bagaimana kronologinya dan mengapa Anda mengambil keputusan tersebut?',
            ]);
            $good = $make([
                'step_code' => 'END-GOOD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 7,
                'title' => 'Eskalasi Berhasil Dicegah',
                'story_text' => 'Supervisor menilai Anda menangani panggilan ini dengan baik: mendengarkan emosi pelanggan, memeriksa penyebab sebenarnya, dan menawarkan solusi sesuai kebijakan disertai update berkala. Pelanggan batal memposting keluhan di media sosial.',
                'customer_dialogue' => 'Terima kasih sudah menjelaskan dengan jelas dan mau terus mengabari saya.',
            ]);
            $fair = $make([
                'step_code' => 'END-FAIR', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 8,
                'title' => 'Selesai, Tetapi Masih Ada Catatan',
                'story_text' => 'Panggilan berakhir dan pelanggan tidak jadi mengeskalasi keluhannya, namun supervisor mencatat bahwa Anda lebih banyak menuruti permintaan pelanggan daripada mengikuti prosedur komunikasi eskalasi.',
                'customer_dialogue' => 'Ya sudah, yang penting urusan saya selesai.',
            ]);
            $bad = $make([
                'step_code' => 'END-BAD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 9,
                'title' => 'Keluhan Berujung ke Media Sosial',
                'story_text' => 'Pelanggan tetap kecewa dan membagikan pengalamannya di media sosial sebelum masalah selesai. Ini bagian dari simulasi: gunakan Decision Journey untuk melihat titik mana yang dapat dilakukan berbeda.',
                'customer_dialogue' => 'Saya sudah coba baik-baik, tapi tidak ada kepastian. Biar orang lain tahu juga.',
            ]);

            // Item investigasi
            $item = fn (Scenario $s, string $type, string $title, string $content, bool $relevant, int $order) => InvestigationItem::create([
                'scenario_id' => $s->id, 'type' => $type, 'title' => $title, 'content' => $content,
                'is_relevant' => $relevant, 'sort_order' => $order,
            ]);

            $item($s2, 'tracking', 'Lacak Pengiriman', "Status: Tertahan di gudang transit sejak kemarin pagi akibat cuaca ekstrim (banjir di jalur distribusi).\nEstimasi tiba: 1-2 hari kerja setelah jalur dibuka kembali.", true, 1);
            $item($s2, 'sop', 'SOP Keterlambatan Pengiriman', "Keterlambatan akibat force majeure (cuaca/bencana) berhak atas kompensasi voucher, bukan refund penuh.\nRefund penuh hanya untuk keterlambatan di atas 5 hari kerja atau kesalahan dari pihak perusahaan, dan memerlukan persetujuan supervisor.", true, 2);
            $item($s2, 'history', 'Riwayat Komplain Pelanggan', 'Pelanggan ini baru pertama kali mengajukan komplain. Riwayat pembelian sebelumnya baik dan tidak ada catatan masalah.', true, 3);
            $item($s2, 'courier', 'Hubungi Kurir', 'Kurir belum dapat dihubungi karena sedang berada di lokasi terdampak banjir.', false, 4);

            // Hint bertahap (tidak memberi jawaban langsung)
            $hint = fn (Scenario $s, int $level, string $text) => Hint::create(['scenario_id' => $s->id, 'level' => $level, 'hint_text' => $text]);
            $hint($s1, 1, 'Pelanggan sedang emosi. Apa yang biasanya lebih dulu dibutuhkan sebelum ia bisa mendengarkan penjelasan Anda?');
            $hint($s1, 2, 'Memotong pembicaraan saat seseorang sedang marah biasanya membuat suasana seperti apa?');
            $hint($s1r, 1, 'Bagaimana cara mengakui perasaan pelanggan tanpa harus setuju pada semua tuntutannya?');
            $hint($s2, 1, 'Sebelum menjanjikan solusi, informasi apa yang perlu Anda pastikan dulu?');
            $hint($s2, 2, 'Apakah janji yang Anda berikan sudah sesuai dengan kewenangan dan kebijakan yang berlaku?');
            $hint($s2h, 1, 'Bagaimana sebaiknya Anda merespons saat menyadari sendiri sudah membuat keputusan di luar wewenang?');
            $hint($s3, 1, 'Pelanggan mengancam akan memposting keluhannya. Apa yang bisa meyakinkan tanpa melanggar kebijakan?');
            $hint($s3, 2, 'Selain kompensasi, apa lagi yang bisa membuat pelanggan merasa diperhatikan selama menunggu?');
            $hint($s4, 1, 'Jelaskan keputusan Anda berdasarkan emosi pelanggan, penyebab masalah, dan kebijakan yang berlaku.');

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
            $opt($s1, 'A', $s1r, 'Memotong pembicaraan untuk langsung menjelaskan kebijakan pengiriman.',
                'Pelanggan merasa tidak didengarkan dan semakin tersulut emosinya.', 'negative', ['IDN' => 0, 'ACT' => 0], 'potong_bicara');
            $opt($s1, 'B', $s2, 'Membiarkan pelanggan menyampaikan keluhannya sampai selesai, menunjukkan empati, lalu meminta nomor pesanan untuk diperiksa.',
                'Nada bicara pelanggan mulai mereda karena merasa didengarkan. Ia bersedia memberikan nomor pesanannya.', 'positive', ['IDN' => 2, 'ACT' => 2], 'dengar_empati');
            $opt($s1, 'C', $s1r, 'Menawarkan untuk menelepon balik nanti karena sedang menangani panggilan lain.',
                'Pelanggan merasa diabaikan dan semakin marah karena permintaannya ditunda.', 'negative', ['IDN' => 1, 'ACT' => 0], 'tunda_telepon');

            // S1-R - Keputusan 1 (remedial)
            $opt($s1r, 'A', $s2, 'Tetap mencoba menjelaskan kebijakan pengiriman.',
                'Pelanggan akhirnya diam mendengarkan, tetapi nada suaranya masih tegang sepanjang sisa percakapan.', 'negative', ['ADP' => 0, 'ACT' => 1]);
            $opt($s1r, 'B', $s2, 'Meminta maaf atas kesan sebelumnya dan mempersilakan pelanggan melanjutkan keluhannya.',
                'Pelanggan merasa dihargai dan mulai menjelaskan masalahnya dengan lebih tenang.', 'positive', ['ADP' => 2, 'ACT' => 2]);
            $opt($s1r, 'C', $s2, 'Mentransfer panggilan ke bagian lain tanpa penjelasan.',
                'Pelanggan harus mengulang ceritanya dari awal dan merasa semakin kesal dengan proses yang berbelit.', 'negative', ['ADP' => 0, 'ACT' => 0]);

            // S2 - Keputusan 2
            $opt($s2, 'A', $s2h, 'Langsung menjanjikan refund penuh hari ini agar pelanggan tenang.',
                'Pelanggan tenang sesaat. Namun beberapa menit kemudian, bagian keuangan menghubungi Anda karena refund tersebut belum disetujui supervisor.', 'negative', ['ALT' => 0, 'CON' => 0, 'ACT' => 0], 'janji_berlebih');
            $opt($s2, 'B', $s3, 'Menjelaskan penyebab keterlambatan, estimasi waktu baru, dan menawarkan kompensasi voucher sesuai SOP.',
                'Pelanggan memahami penyebabnya, tetapi masih merasa kompensasi tersebut belum menyelesaikan masalah utamanya.', 'positive', ['ALT' => 2, 'CON' => 2, 'ACT' => 2], 'jelaskan_sop');
            $opt($s2, 'C', $s3, 'Mengatakan keterlambatan ini di luar kendali perusahaan dan pelanggan harus menunggu saja.',
                'Pelanggan merasa keluhannya tidak dianggap serius meskipun penyebabnya memang di luar kendali perusahaan.', 'negative', ['ALT' => 0, 'CON' => 1, 'ACT' => 0], 'lepas_tangan');

            // S2-H - Keputusan 2 (remedial)
            $opt($s2h, 'A', $s3, 'Mengatakan Anda hanya ingin membuat pelanggan tenang secepatnya.',
                'Bagian keuangan mencatat hal ini sebagai pelanggaran prosedur yang perlu ditindaklanjuti.', 'negative', ['ADP' => 0, 'CON' => 0]);
            $opt($s2h, 'B', $s3, 'Mengakui kesalahan prosedur dan segera berkoordinasi dengan supervisor untuk solusi yang sesuai kebijakan.',
                'Bagian keuangan menghargai sikap Anda dan membantu mencarikan solusi yang tetap sesuai kebijakan.', 'positive', ['ADP' => 2, 'CON' => 2]);
            $opt($s2h, 'C', $s3, 'Membiarkan masalah ini dan tetap melanjutkan panggilan berikutnya.',
                'Masalah administrasi dibiarkan menggantung dan berpotensi menjadi temuan audit di kemudian hari.', 'negative', ['ADP' => 0, 'CON' => 0]);

            // S3 - Keputusan 3
            $opt($s3, 'A', $s4, 'Mengabaikan ancaman pelanggan dan hanya mengulang kebijakan yang sama.',
                'Pelanggan merasa semakin tidak didengar. Supervisor dipanggil untuk menengahi panggilan.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'ulang_kebijakan');
            $opt($s3, 'B', $s4, 'Menawarkan untuk memberi update berkala melalui pesan hingga barang tiba, dan mencatat keluhan sebagai prioritas eskalasi.',
                'Pelanggan merasa lebih tenang karena akan terus mendapat kabar dan merasa keluhannya diprioritaskan.', 'positive', ['ALT' => 2, 'ADP' => 2, 'ACT' => 2], 'update_berkala');
            $opt($s3, 'C', $bad, 'Berjanji akan "mengusahakan" barang sampai besok padahal tidak ada kepastian dari gudang.',
                'Pelanggan mempercayai janji tersebut. Keesokan harinya barang tetap belum tiba dan pelanggan langsung memposting kekecewaannya di media sosial.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'janji_kosong');

            // S4 - Keputusan 4
            $opt($s4, 'A', $fair, 'Saya hanya mengikuti keinginan pelanggan agar panggilan cepat selesai.',
                'Supervisor mencatat bahwa keputusan Anda belum menunjukkan pertimbangan prosedur eskalasi secara utuh.', 'neutral', ['IDN' => 0, 'CON' => 0, 'ACT' => 1]);
            $opt($s4, 'B', $good, 'Saya mendengarkan keluhan pelanggan, memeriksa penyebab keterlambatan, menjelaskan dengan empati, dan memberi solusi serta update berkala sesuai kebijakan.',
                'Supervisor menyetujui penanganan Anda karena didasarkan pada pemahaman emosi pelanggan, data yang akurat, dan kebijakan yang berlaku.', 'positive', ['IDN' => 2, 'CON' => 2, 'ACT' => 2]);
            $opt($s4, 'C', $bad, 'Itu bukan tanggung jawab saya, murni masalah dari pihak kurir.',
                'Supervisor menilai Anda menghindari tanggung jawab komunikasi, padahal keluhan ini berisiko tinggi terhadap citra perusahaan.', 'negative', ['IDN' => 0, 'CON' => 0, 'ACT' => 0]);
        });
    }
}
