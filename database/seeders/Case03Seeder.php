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
 * Case 03 - Kesalahan Administrasi Pelayanan (4 titik keputusan + cabang remedial).
 * Fokus: analisis kesalahan, prioritas, penyelesaian masalah.
 * Skor indikator per opsi (0-2) adalah PLACEHOLDER sampai indikator Bab III final.
 *
 * Alur:
 *   S1 --B--> S2 ; S1 --A/C--> S1-R --> S2
 *   S2 --B/C--> S3 ; S2 --A--> S2-H --> S3
 *   S3 --A/B/C--> S4
 *   S4 --B--> END-GOOD ; S4 --A--> END-FAIR ; S4 --C--> END-BAD
 */
class Case03Seeder extends Seeder
{
    public function run(): void
    {
        $this->call(IndicatorSeeder::class);

        DB::transaction(function () {
            $indicators = Indicator::pluck('id', 'code');

            $case = CaseModel::updateOrCreate(
                ['code' => 'CASE-03'],
                [
                    'title' => 'Administrative Service Error: Kesalahan Administrasi Pelayanan',
                    'description' => 'Temukan akar masalah dari kesalahan tagihan ganda, tentukan prioritas penanganan, dan selesaikan dampaknya sebelum berkembang menjadi masalah yang lebih besar.',
                    'level' => 'developing',
                    'role_title' => 'Staf Administrasi Pelayanan',
                    'briefing_text' => "Selamat datang kembali di PT Nusantara Business Service.\n\nHari ini Anda bertugas di bagian administrasi pelayanan. Saat melakukan audit rutin, Anda menemukan ada kesalahan sistem yang berpotensi memengaruhi lebih dari satu pelanggan.\n\nBerbeda dari kasus sebelumnya, di sini pelanggan belum tentu langsung komplain — keputusan Anda di awal akan menentukan apakah masalah ini selesai dengan tenang atau justru membesar. Perhatikan log sistem, SOP penanganan kesalahan, dan dampaknya terhadap pelanggan lain.",
                    'estimated_time' => 15,
                    'is_active' => true,
                    'sort_order' => 3,
                ]
            );

            // Idempotent: hapus skenario lama (opsi, investigasi, hint ikut terhapus via cascade).
            $case->scenarios()->delete();

            $make = fn (array $attrs) => Scenario::create($attrs + ['case_id' => $case->id]);

            $s1 = $make([
                'step_code' => 'S1', 'scenario_type' => 'main', 'is_start' => true, 'decision_no' => 1, 'sort_order' => 1,
                'title' => 'Temuan Saat Audit Rutin',
                'story_text' => 'Saat memeriksa laporan transaksi harian, Anda menemukan bahwa seorang pelanggan dikenakan tagihan dua kali (double charge) untuk transaksi yang sama. Pelanggan tersebut belum menyadari dan belum mengajukan komplain apa pun.',
            ]);
            $s1r = $make([
                'step_code' => 'S1-R', 'scenario_type' => 'remedial', 'decision_no' => 1, 'sort_order' => 2,
                'title' => 'Pelanggan Menyadari Sendiri',
                'story_text' => 'Beberapa hari kemudian, pelanggan tersebut melihat mutasi rekeningnya dan menyadari ada dua tagihan untuk pembelian yang sama. Ia menghubungi Anda dengan nada kecewa karena merasa tidak ada pemberitahuan sebelumnya.',
                'customer_dialogue' => 'Saya baru sadar ada dua tagihan di rekening saya untuk pembelian yang sama! Kenapa tidak ada pemberitahuan dari pihak toko?',
                'new_problem_text' => 'Pelanggan merasa masalah ini sengaja ditutupi. Tentukan cara Anda menjelaskan situasinya.',
            ]);
            $s2 = $make([
                'step_code' => 'S2', 'scenario_type' => 'main', 'decision_no' => 2, 'max_investigations' => 2, 'sort_order' => 3,
                'title' => 'Mencari Akar Masalah',
                'story_text' => 'Sebelum memproses pengembalian dana, Anda perlu memastikan penyebab kesalahan ini agar tidak terjadi lagi pada pelanggan lain.',
                'customer_dialogue' => 'Saya juga dengar dari teman saya katanya dia kena masalah serupa minggu ini. Apa cuma saya yang kena?',
                'new_problem_text' => 'Log sistem menunjukkan indikasi bahwa kesalahan ini mungkin dialami lebih dari satu pelanggan.',
            ]);
            $s2h = $make([
                'step_code' => 'S2-H', 'scenario_type' => 'remedial', 'decision_no' => 2, 'sort_order' => 4,
                'title' => 'Komplain Mulai Berdatangan',
                'story_text' => 'Beberapa hari kemudian, komplain serupa mulai berdatangan dari pelanggan lain yang juga mengalami tagihan ganda pada periode yang sama. Supervisor mempertanyakan penanganan Anda sebelumnya.',
                'customer_dialogue' => 'Kenapa baru sekarang kita tahu ada banyak pelanggan yang kena masalah yang sama?',
                'new_problem_text' => 'Masalah yang semula tampak kecil kini berkembang menjadi keluhan dari banyak pelanggan sekaligus.',
            ]);
            $s3 = $make([
                'step_code' => 'S3', 'scenario_type' => 'main', 'decision_no' => 3, 'sort_order' => 5,
                'title' => 'Menentukan Prioritas Penanganan',
                'story_text' => 'Anda kini perlu menangani pelanggan pertama sekaligus mempertimbangkan pelanggan lain yang berpotensi terdampak masalah yang sama.',
                'customer_dialogue' => 'Jadi uang saya kapan kembali? Dan apakah toko akan mengecek semua pelanggan yang kena masalah ini juga?',
            ]);
            $s4 = $make([
                'step_code' => 'S4', 'scenario_type' => 'main', 'decision_no' => 4, 'sort_order' => 6,
                'title' => 'Peninjauan oleh Manajemen',
                'story_text' => 'Manajemen meminta laporan pertanggungjawaban atas kasus ini sekaligus rencana agar kesalahan serupa tidak terulang.',
                'customer_dialogue' => 'Bagaimana kronologi penemuan masalah ini dan apa rencana Anda agar tidak terulang?',
            ]);
            $good = $make([
                'step_code' => 'END-GOOD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 7,
                'title' => 'Masalah Tertangani Tuntas',
                'story_text' => 'Manajemen menilai Anda menangani kasus ini dengan baik: akar masalah dianalisis dari log sistem, penanganan diprioritaskan berdasarkan dampak dan urgensi, dan audit pencegahan diusulkan agar tidak terulang. Kepercayaan pelanggan tetap terjaga.',
                'customer_dialogue' => 'Terima kasih sudah transparan dan menjelaskan penyebabnya dengan jelas.',
            ]);
            $fair = $make([
                'step_code' => 'END-FAIR', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 8,
                'title' => 'Selesai Sebagian',
                'story_text' => 'Pengembalian dana untuk pelanggan ini selesai, namun manajemen mencatat bahwa potensi dampak pada pelanggan lain belum ditindaklanjuti secara menyeluruh.',
                'customer_dialogue' => 'Uang saya sudah kembali. Semoga yang lain juga segera ditangani.',
            ]);
            $bad = $make([
                'step_code' => 'END-BAD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 9,
                'title' => 'Masalah Membesar',
                'story_text' => 'Komplain dari banyak pelanggan terus bertambah dan menjadi sorotan karena dianggap lambat ditangani. Ini bagian dari simulasi: gunakan Decision Journey untuk melihat titik mana yang dapat dilakukan berbeda.',
                'customer_dialogue' => 'Sudah berminggu-minggu dan masalah ini masih belum selesai untuk semua orang.',
            ]);

            // Item investigasi
            $item = fn (Scenario $s, string $type, string $title, string $content, bool $relevant, int $order) => InvestigationItem::create([
                'scenario_id' => $s->id, 'type' => $type, 'title' => $title, 'content' => $content,
                'is_relevant' => $relevant, 'sort_order' => $order,
            ]);

            $item($s2, 'log', 'Log Sistem Pembayaran', "Ditemukan kesalahan duplikasi transaksi pada rentang waktu promo kemarin (09.00-11.00) akibat bug saat lonjakan trafik.\nTercatat beberapa transaksi lain pada rentang waktu yang sama berpotensi mengalami hal serupa.", true, 1);
            $item($s2, 'sop', 'SOP Penanganan Kesalahan Tagihan', "Kesalahan tagihan ganda wajib dikembalikan penuh dalam 3 hari kerja.\nJika terindikasi sistemik (berdampak pada lebih dari satu pelanggan), wajib dieskalasikan ke tim IT dan dilakukan audit transaksi pada periode terkait.", true, 2);
            $item($s2, 'history', 'Data Riwayat Transaksi Pelanggan', 'Pelanggan melakukan pembelian pada jam yang sama dengan periode bug sistem saat promo berlangsung.', true, 3);
            $item($s2, 'it', 'Hubungi Tim IT', 'Tim IT sedang menangani isu prioritas lain dan meminta laporan tertulis dikirim terlebih dahulu.', false, 4);

            // Hint bertahap (tidak memberi jawaban langsung)
            $hint = fn (Scenario $s, int $level, string $text) => Hint::create(['scenario_id' => $s->id, 'level' => $level, 'hint_text' => $text]);
            $hint($s1, 1, 'Jika Anda menemukan kesalahan sebelum pelanggan menyadarinya, langkah apa yang menunjukkan tanggung jawab?');
            $hint($s1, 2, 'Apa risikonya jika kesalahan ini dibiarkan sampai pelanggan sendiri yang menemukan?');
            $hint($s1r, 1, 'Bagaimana cara menjelaskan kesalahan sistem tanpa membuat pelanggan merasa ditutup-tutupi?');
            $hint($s2, 1, 'Sebelum memproses pengembalian dana, informasi apa yang membantu Anda memastikan penyebab aslinya?');
            $hint($s2, 2, 'Jika kesalahan ini ternyata memengaruhi lebih dari satu pelanggan, apakah cukup menangani satu pelanggan saja?');
            $hint($s2h, 1, 'Bagaimana sebaiknya Anda merespons ketika menyadari penanganan sebelumnya belum menyentuh akar masalah?');
            $hint($s3, 1, 'Dengan banyak pelanggan yang berpotensi terdampak, bagaimana Anda menentukan siapa yang ditangani lebih dulu?');
            $hint($s3, 2, 'Selain mengembalikan dana, apa yang bisa meyakinkan pelanggan bahwa masalah ini ditangani secara menyeluruh?');
            $hint($s4, 1, 'Jelaskan keputusan Anda berdasarkan analisis penyebab, prioritas penanganan, dan pencegahan ke depan.');

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
            $opt($s1, 'A', $s1r, 'Menunggu sampai pelanggan mengajukan komplain, baru ditindaklanjuti.',
                'Beberapa hari berlalu tanpa tindakan. Pelanggan akhirnya menyadari sendiri dan menghubungi dengan nada kecewa.', 'negative', ['IDN' => 0, 'ACT' => 0], 'tunggu_komplain');
            $opt($s1, 'B', $s2, 'Segera melaporkan temuan ke supervisor dan menyiapkan rencana pengembalian dana sebelum pelanggan menyadarinya.',
                'Supervisor mengapresiasi inisiatif Anda. Proses pengembalian dana dapat segera disiapkan.', 'positive', ['IDN' => 2, 'ACT' => 2], 'lapor_proaktif');
            $opt($s1, 'C', $s1r, 'Memperbaiki data di sistem tanpa memberi tahu siapa pun agar masalah tidak terlihat.',
                'Data terlihat sudah benar di sistem, tetapi pelanggan tetap melihat dua tagihan di rekeningnya dan akhirnya menghubungi Anda.', 'negative', ['IDN' => 1, 'ACT' => 0], 'tutupi_masalah');

            // S1-R - Keputusan 1 (remedial)
            $opt($s1r, 'A', $s2, 'Mengatakan masalah sedang diproses tanpa memberikan detail lebih lanjut.',
                'Pelanggan merasa kurang mendapat kejelasan dan menjadi semakin curiga.', 'negative', ['ADP' => 0, 'ACT' => 0]);
            $opt($s1r, 'B', $s2, 'Meminta maaf, menjelaskan bahwa ini temuan kesalahan sistem, dan segera memproses pengembalian dana.',
                'Pelanggan merasa lega karena mendapat penjelasan yang jujur dan jelas.', 'positive', ['ADP' => 2, 'ACT' => 2]);
            $opt($s1r, 'C', $s2, 'Menyalahkan sistem bank atau payment gateway sebagai pihak yang bermasalah.',
                'Pelanggan merasa Anda hanya mencari alasan dan lepas tangan dari tanggung jawab.', 'negative', ['ADP' => 0, 'ACT' => 0]);

            // S2 - Keputusan 2
            $opt($s2, 'A', $s2h, 'Memproses pengembalian dana untuk pelanggan ini saja dan menutup kasus tanpa mengecek pelanggan lain.',
                'Pelanggan ini puas. Namun beberapa hari kemudian, komplain serupa mulai berdatangan dari pelanggan lain yang mengalami masalah yang sama.', 'negative', ['ALT' => 0, 'CON' => 0, 'ACT' => 1], 'tutup_sebagian');
            $opt($s2, 'B', $s3, 'Memproses pengembalian dana pelanggan ini dan melaporkan potensi dampak ke pelanggan lain berdasarkan temuan log sistem.',
                'Pengembalian dana diproses dan tim terkait mulai memeriksa pelanggan lain yang berpotensi terdampak.', 'positive', ['ALT' => 2, 'CON' => 2, 'ACT' => 2], 'tangani_sistemik');
            $opt($s2, 'C', $s3, 'Menunda pengembalian dana sampai semua pelanggan lain diperiksa satu per satu.',
                'Pemeriksaan menyeluruh membutuhkan waktu lama, sementara pelanggan ini masih menunggu tanpa kepastian.', 'neutral', ['ALT' => 1, 'CON' => 1, 'ACT' => 0], 'tunda_semua');

            // S2-H - Keputusan 2 (remedial)
            $opt($s2h, 'A', $s3, 'Mengatakan Anda tidak tahu bahwa masalah ini berdampak sistemik.',
                'Supervisor mempertanyakan mengapa kemungkinan dampak yang lebih luas tidak diperiksa sejak awal.', 'negative', ['ADP' => 0, 'CON' => 0]);
            $opt($s2h, 'B', $s3, 'Mengakui bahwa akar masalah seharusnya diperiksa lebih awal, dan segera mengusulkan audit menyeluruh.',
                'Supervisor menghargai sikap tanggung jawab Anda dan menyetujui audit menyeluruh segera dijalankan.', 'positive', ['ADP' => 2, 'CON' => 2]);
            $opt($s2h, 'C', $s3, 'Menyalahkan tim IT yang dianggap lambat memperbaiki sistem.',
                'Supervisor menilai Anda menghindari tanggung jawab atas penanganan awal yang kurang menyeluruh.', 'negative', ['ADP' => 0, 'CON' => 0]);

            // S3 - Keputusan 3
            $opt($s3, 'A', $s4, 'Memprioritaskan hanya pelanggan yang paling vokal atau komplain lebih dulu.',
                'Beberapa pelanggan yang tidak komplain secara langsung merasa diabaikan meskipun mengalami masalah yang sama.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'prioritas_vokal');
            $opt($s3, 'B', $s4, 'Menetapkan prioritas penanganan berdasarkan urgensi dan dampak, serta memberi kepastian waktu kepada pelanggan ini.',
                'Penanganan menjadi lebih terorganisir dan pelanggan merasa mendapat kepastian yang jelas.', 'positive', ['ALT' => 2, 'ADP' => 2, 'ACT' => 2], 'prioritas_dampak');
            $opt($s3, 'C', $s4, 'Memproses semua kasus secara acak tanpa mempertimbangkan urgensi maupun dampaknya.',
                'Beberapa pelanggan dengan dampak besar justru ditangani belakangan, menimbulkan kekecewaan tambahan.', 'neutral', ['ALT' => 1, 'ADP' => 0, 'ACT' => 1], 'proses_acak');

            // S4 - Keputusan 4
            $opt($s4, 'A', $fair, 'Saya hanya memproses pengembalian dana yang diminta pelanggan.',
                'Manajemen mencatat bahwa penanganan Anda belum mencakup analisis akar masalah secara menyeluruh.', 'neutral', ['IDN' => 0, 'CON' => 1, 'ACT' => 1]);
            $opt($s4, 'B', $good, 'Saya menganalisis akar masalah dari log sistem, memprioritaskan penanganan berdasarkan dampak dan urgensi, serta mengusulkan audit untuk mencegah hal ini terulang.',
                'Manajemen menyetujui penanganan Anda karena didasarkan pada analisis yang jelas dan rencana pencegahan ke depan.', 'positive', ['IDN' => 2, 'CON' => 2, 'ACT' => 2]);
            $opt($s4, 'C', $bad, 'Itu bukan kesalahan saya, sistemnya yang bermasalah.',
                'Manajemen menilai Anda menghindari tanggung jawab, padahal kasus ini berdampak pada banyak pelanggan.', 'negative', ['IDN' => 0, 'CON' => 0, 'ACT' => 0]);
        });
    }
}
