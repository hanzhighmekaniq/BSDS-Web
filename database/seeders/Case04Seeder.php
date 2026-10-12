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
 * Case 04 - Tiga Masalah Datang Bersamaan (4 titik keputusan + cabang remedial).
 * Fokus: menentukan prioritas, mempertimbangkan urgensi, konsekuensi keputusan.
 * Skor indikator per opsi (0-2) adalah PLACEHOLDER sampai indikator Bab III final.
 *
 * Alur:
 *   S1 --B--> S2 ; S1 --A/C--> S1-R --> S2
 *   S2 --B/C--> S3 ; S2 --A--> S2-H --> S3
 *   S3 --A/B--> S4 ; S3 --C--> END-BAD
 *   S4 --B--> END-GOOD ; S4 --A--> END-FAIR ; S4 --C--> END-BAD
 */
class Case04Seeder extends Seeder
{
    public function run(): void
    {
        $this->call(IndicatorSeeder::class);

        DB::transaction(function () {
            $indicators = Indicator::pluck('id', 'code');

            $case = CaseModel::updateOrCreate(
                ['code' => 'CASE-04'],
                [
                    'title' => 'Multiple Customer Problems: Tiga Masalah Datang Bersamaan',
                    'description' => 'Tiga masalah pelanggan muncul dalam waktu bersamaan saat jam sibuk. Tentukan prioritas penanganan berdasarkan urgensi dan dampaknya tanpa mengabaikan pihak mana pun.',
                    'level' => 'challenge',
                    'role_title' => 'Customer Service Officer',
                    'briefing_text' => "Selamat datang kembali di PT Nusantara Business Service.\n\nIni adalah kasus paling menantang. Pada jam sibuk, tiga hal terjadi dalam waktu yang hampir bersamaan: pelanggan walk-in yang menunggu di depan meja, telepon dari pelanggan lain yang sudah lama mengantre, dan email komplain dari klien korporat (VIP) yang berisiko tinggi terhadap perusahaan.\n\nAnda tidak dapat menangani ketiganya sekaligus. Keputusan Anda tentang siapa yang didahulukan — dan bagaimana pihak lain tetap merasa diperhatikan — akan menentukan hasil akhirnya.",
                    'estimated_time' => 20,
                    'is_active' => true,
                    'sort_order' => 4,
                ]
            );

            // Idempotent: hapus skenario lama (opsi, investigasi, hint ikut terhapus via cascade).
            $case->scenarios()->delete();

            $make = fn (array $attrs) => Scenario::create($attrs + ['case_id' => $case->id]);

            $s1 = $make([
                'step_code' => 'S1', 'scenario_type' => 'main', 'is_start' => true, 'decision_no' => 1, 'sort_order' => 1,
                'title' => 'Tiga Hal Terjadi Bersamaan',
                'story_text' => 'Jam sibuk. Seorang pelanggan walk-in berdiri di depan meja Anda mengeluhkan produk yang rusak. Di saat yang sama, telepon berdering — pelanggan lain sudah menunggu di line selama 5 menit. Supervisor juga baru saja mengirim pesan: ada email komplain dari klien korporat (VIP) yang harus direspons dalam 15 menit karena berisiko eskalasi.',
                'customer_dialogue' => 'Saya sudah berdiri di sini dari tadi, bisa dibantu sekarang?',
            ]);
            $s1r = $make([
                'step_code' => 'S1-R', 'scenario_type' => 'remedial', 'decision_no' => 1, 'sort_order' => 2,
                'title' => 'Supervisor Mengingatkan',
                'story_text' => 'Sementara Anda sibuk dengan satu pihak, waktu terus berjalan. Supervisor kembali menghubungi Anda karena email VIP belum direspons dan telepon pelanggan lain masih menunggu.',
                'customer_dialogue' => 'Email dari klien korporat itu urgent, kenapa belum direspons? Telepon satunya juga belum diangkat.',
                'new_problem_text' => 'Dua pihak lain mulai merasa diabaikan. Tentukan cara Anda melanjutkan.',
            ]);
            $s2 = $make([
                'step_code' => 'S2', 'scenario_type' => 'main', 'decision_no' => 2, 'max_investigations' => 2, 'sort_order' => 3,
                'title' => 'Menyusun Respons untuk Tiga Pihak',
                'story_text' => 'Anda membuka email dari klien VIP. Sebelum merespons, Anda perlu memahami konteks lengkapnya agar tidak salah mengambil keputusan dengan sumber daya yang terbatas.',
                'customer_dialogue' => 'Ini pesanan ketiga kami bulan ini yang bermasalah. Kami mempertimbangkan untuk menghentikan kontrak jika tidak ada perbaikan nyata.',
                'new_problem_text' => 'Klien VIP ternyata memiliki riwayat komplain berulang — risikonya lebih besar dari yang terlihat di awal.',
            ]);
            $s2h = $make([
                'step_code' => 'S2-H', 'scenario_type' => 'remedial', 'decision_no' => 2, 'sort_order' => 4,
                'title' => 'Dua Pihak Lain Mulai Komplain',
                'story_text' => 'Karena perhatian Anda terpusat sepenuhnya pada klien VIP, pelanggan walk-in dan penelepon yang sempat diabaikan kini ikut mengeluh.',
                'customer_dialogue' => 'Dari tadi saya ditinggal terus, ini bagaimana?',
                'new_problem_text' => 'Situasi menjadi lebih runyam karena beberapa pihak merasa tidak diperhatikan sekaligus.',
            ]);
            $s3 = $make([
                'step_code' => 'S3', 'scenario_type' => 'main', 'decision_no' => 3, 'sort_order' => 5,
                'title' => 'Klien VIP Meminta Kepastian',
                'story_text' => 'Klien VIP kini menghubungi melalui telepon, meminta solusi konkret setelah sebelumnya hanya menerima permintaan maaf.',
                'customer_dialogue' => 'Jadi apa solusi konkretnya? Kami butuh kepastian, bukan permintaan maaf lagi.',
            ]);
            $s4 = $make([
                'step_code' => 'S4', 'scenario_type' => 'main', 'decision_no' => 4, 'sort_order' => 6,
                'title' => 'Supervisor Meninjau Keseluruhan Kejadian',
                'story_text' => 'Setelah situasi mereda, supervisor meminta Anda menjelaskan bagaimana Anda mengambil keputusan saat tiga masalah datang bersamaan.',
                'customer_dialogue' => 'Tiga masalah datang bersamaan tadi — bagaimana Anda memutuskan mana yang didahulukan, dan apa hasilnya?',
            ]);
            $good = $make([
                'step_code' => 'END-GOOD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 7,
                'title' => 'Ketiga Pihak Tertangani dengan Baik',
                'story_text' => 'Supervisor menilai Anda berhasil menilai urgensi dan dampak dari ketiga masalah, memberi respons awal ke semua pihak, lalu menyelesaikan berdasarkan prioritas yang tepat. Klien VIP mempertahankan kontraknya dan pelanggan lain tetap merasa diperhatikan.',
                'customer_dialogue' => 'Terima kasih sudah tetap memperhatikan kami meskipun sedang sangat sibuk.',
            ]);
            $fair = $make([
                'step_code' => 'END-FAIR', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 8,
                'title' => 'Selesai dengan Beberapa Catatan',
                'story_text' => 'Ketiga masalah akhirnya selesai, namun dengan keterlambatan yang membuat beberapa pihak sempat kecewa sebelum akhirnya tertangani.',
                'customer_dialogue' => 'Semua akhirnya selesai juga, walau agak lama.',
            ]);
            $bad = $make([
                'step_code' => 'END-BAD', 'scenario_type' => 'ending', 'is_ending' => true, 'sort_order' => 9,
                'title' => 'Kontrak Klien VIP Terancam',
                'story_text' => 'Klien VIP mengancam akan menghentikan kontraknya dan komplain dari pihak lain menumpuk tanpa penyelesaian yang jelas. Ini bagian dari simulasi: gunakan Decision Journey untuk melihat titik mana yang dapat dilakukan berbeda.',
                'customer_dialogue' => 'Kami akan mempertimbangkan ulang kerja sama ini jika pelayanan seperti ini terus berulang.',
            ]);

            // Item investigasi
            $item = fn (Scenario $s, string $type, string $title, string $content, bool $relevant, int $order) => InvestigationItem::create([
                'scenario_id' => $s->id, 'type' => $type, 'title' => $title, 'content' => $content,
                'is_relevant' => $relevant, 'sort_order' => $order,
            ]);

            $item($s2, 'contract', 'Riwayat Kontrak Klien VIP', "Klien ini sudah mengajukan komplain 3 kali dalam 2 bulan terakhir.\nNilai kontrak tahunan klien ini termasuk salah satu yang terbesar di divisi ini.", true, 1);
            $item($s2, 'sop', 'SOP Eskalasi Komplain Korporat', 'Komplain dari klien korporat dengan riwayat berulang wajib direspons dalam 15 menit dan melibatkan supervisor untuk solusi lanjutan.', true, 2);
            $item($s2, 'queue', 'Status Antrian Layanan', 'Pelanggan walk-in sudah menunggu lebih dari 10 menit. Pelanggan telepon sudah on-hold lebih dari 7 menit.', true, 3);
            $item($s2, 'colleague', 'Hubungi Rekan Shift', 'Rekan shift sedang menangani transaksi lain dan hanya bisa membantu sebentar.', false, 4);

            // Hint bertahap (tidak memberi jawaban langsung)
            $hint = fn (Scenario $s, int $level, string $text) => Hint::create(['scenario_id' => $s->id, 'level' => $level, 'hint_text' => $text]);
            $hint($s1, 1, 'Pertimbangkan bukan hanya siapa yang paling terlihat, tetapi mana yang risikonya paling besar jika tidak segera ditangani.');
            $hint($s1, 2, 'Apakah ada cara untuk menangani satu pihak tanpa sepenuhnya mengabaikan dua pihak lainnya?');
            $hint($s1r, 1, 'Bagaimana cara memberi perhatian singkat ke semua pihak sebelum fokus menyelesaikan satu per satu?');
            $hint($s2, 1, 'Informasi apa yang membantu Anda menilai seberapa besar dampak jika klien ini tidak segera ditangani?');
            $hint($s2, 2, 'Apakah memberi respons penuh ke satu pihak berarti pihak lain harus sepenuhnya diabaikan?');
            $hint($s2h, 1, 'Bagaimana cara mengakui keterlambatan tanpa membuat pelanggan merasa semakin tidak dianggap?');
            $hint($s3, 1, 'Klien VIP meminta solusi konkret. Solusi seperti apa yang realistis sesuai kewenangan Anda?');
            $hint($s3, 2, 'Apakah keputusan besar seperti diskon atau kompensasi perlu melibatkan atasan?');
            $hint($s4, 1, 'Jelaskan keputusan Anda berdasarkan penilaian urgensi, dampak, dan cara Anda membagi perhatian ke semua pihak.');

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
            $opt($s1, 'A', $s1r, 'Menangani pelanggan walk-in terlebih dahulu karena sudah ada di depan mata.',
                'Pelanggan walk-in merasa diperhatikan, tetapi email dari klien VIP dan telepon yang menunggu menjadi tertunda tanpa kabar.', 'negative', ['IDN' => 0, 'ACT' => 0], 'prioritas_terlihat');
            $opt($s1, 'B', $s2, 'Membalas email supervisor mengenai klien VIP terlebih dahulu karena risikonya paling tinggi, sambil meminta rekan membantu pelanggan walk-in sejenak.',
                'Klien VIP merasa direspons tepat waktu. Pelanggan walk-in tetap terbantu karena dialihkan sementara ke rekan kerja.', 'positive', ['IDN' => 2, 'ACT' => 2], 'prioritas_risiko');
            $opt($s1, 'C', $s1r, 'Mengangkat telepon terlebih dahulu karena sudah menunggu paling lama.',
                'Pelanggan telepon merasa dihargai, tetapi email klien VIP yang berisiko tinggi menjadi tertunda tanpa respons.', 'negative', ['IDN' => 1, 'ACT' => 0], 'prioritas_durasi');

            // S1-R - Keputusan 1 (remedial)
            $opt($s1r, 'A', $s2, 'Tetap menyelesaikan pihak yang sedang Anda tangani sampai selesai sebelum memikirkan yang lain.',
                'Dua pihak lain menunggu semakin lama tanpa kepastian, dan risiko eskalasi dari klien VIP semakin besar.', 'negative', ['ADP' => 0, 'ACT' => 0]);
            $opt($s1r, 'B', $s2, 'Segera membalas email VIP secara singkat untuk menahan eskalasi, melanjutkan pihak yang sedang ditangani, dan meminta rekan mengangkat telepon.',
                'Ketiga pihak mendapat perhatian awal yang cukup untuk tetap tenang sementara Anda menyelesaikannya satu per satu.', 'positive', ['ADP' => 2, 'ACT' => 2]);
            $opt($s1r, 'C', $s2, 'Mengabaikan pesan supervisor dan tetap fokus hanya pada satu pihak yang sedang ditangani.',
                'Supervisor mencatat bahwa peringatannya tidak ditindaklanjuti, dan dua pihak lain semakin kecewa.', 'negative', ['ADP' => 0, 'ACT' => 0]);

            // S2 - Keputusan 2
            $opt($s2, 'A', $s2h, 'Memberi perhatian penuh ke klien VIP saja karena nilainya besar, dan menunda dua pihak lain tanpa kabar.',
                'Klien VIP merasa diprioritaskan. Namun pelanggan walk-in dan penelepon yang diabaikan mulai ikut mengeluh.', 'negative', ['ALT' => 0, 'CON' => 0, 'ACT' => 0], 'fokus_vip_saja');
            $opt($s2, 'B', $s3, 'Memberi respons awal yang singkat ke ketiga pihak terlebih dahulu, lalu menyelesaikannya satu per satu berdasarkan urgensi dan dampak.',
                'Ketiga pihak merasa diperhatikan meskipun belum sepenuhnya selesai. Anda dapat melanjutkan penyelesaian dengan lebih tenang.', 'positive', ['ALT' => 2, 'CON' => 2, 'ACT' => 2], 'respons_merata');
            $opt($s2, 'C', $s3, 'Meminta ketiga pihak menunggu sampai Anda selesai dengan yang sedang ditangani, tanpa memberi estimasi waktu.',
                'Ketiga pihak sama-sama menunggu tanpa kepastian, dan kekecewaan mulai terasa dari semua sisi.', 'negative', ['ALT' => 0, 'CON' => 1, 'ACT' => 0], 'tunggu_tanpa_estimasi');

            // S2-H - Keputusan 2 (remedial)
            $opt($s2h, 'A', $s3, 'Meminta pelanggan yang mengeluh untuk menunggu lagi tanpa penjelasan lebih lanjut.',
                'Pelanggan merasa semakin tidak dianggap dan kekecewaannya meningkat.', 'negative', ['ADP' => 0, 'CON' => 0]);
            $opt($s2h, 'B', $s3, 'Meminta maaf, menjelaskan situasi darurat yang terjadi, dan segera memberi perhatian kepada mereka.',
                'Pelanggan memahami situasinya dan bersedia menunggu sedikit lebih lama dengan lebih tenang.', 'positive', ['ADP' => 2, 'CON' => 2]);
            $opt($s2h, 'C', $s3, 'Melimpahkan pelanggan tersebut ke rekan kerja lain tanpa koordinasi atau penjelasan konteks.',
                'Rekan kerja kebingungan menangani tanpa konteks, dan pelanggan harus mengulang keluhannya dari awal.', 'negative', ['ADP' => 0, 'CON' => 0]);

            // S3 - Keputusan 3
            $opt($s3, 'A', $s4, 'Menjanjikan diskon besar kepada klien VIP tanpa persetujuan atasan demi mempertahankan kontrak.',
                'Klien VIP tenang sesaat, tetapi keputusan ini berada di luar kewenangan Anda dan berisiko menjadi masalah baru.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'janji_diluar_wewenang');
            $opt($s3, 'B', $s4, 'Menjelaskan akar masalah, menawarkan solusi konkret sesuai kewenangan Anda, dan menjadwalkan tindak lanjut dengan atasan untuk kompensasi lebih lanjut.',
                'Klien VIP menghargai kejelasan dan kepastian tindak lanjut yang Anda tawarkan.', 'positive', ['ALT' => 2, 'ADP' => 2, 'ACT' => 2], 'solusi_terukur');
            $opt($s3, 'C', $bad, 'Mengatakan bahwa masalah ini akan "dipertimbangkan" tanpa memberikan solusi yang jelas.',
                'Klien VIP merasa tidak mendapat kepastian sama sekali dan mulai mempertimbangkan untuk menghentikan kontrak.', 'negative', ['ALT' => 0, 'ADP' => 0, 'ACT' => 0], 'jawaban_kabur');

            // S4 - Keputusan 4
            $opt($s4, 'A', $fair, 'Saya menangani pihak yang ada di depan saya terlebih dahulu.',
                'Supervisor mencatat bahwa keputusan Anda lebih didasarkan pada siapa yang terlihat, bukan pada urgensi atau dampaknya.', 'neutral', ['IDN' => 0, 'CON' => 1, 'ACT' => 1]);
            $opt($s4, 'B', $good, 'Saya menilai urgensi dan dampak dari masing-masing masalah, memberi respons awal ke semua pihak, lalu menyelesaikannya sesuai prioritas serta berkoordinasi dengan tim.',
                'Supervisor menyetujui cara Anda mengelola tiga masalah sekaligus karena didasarkan pada penilaian prioritas yang jelas dan kerja sama tim.', 'positive', ['IDN' => 2, 'CON' => 2, 'ACT' => 2]);
            $opt($s4, 'C', $bad, 'Saya hanya sempat menangani satu pihak, yang lain belum sempat saya tangani.',
                'Supervisor menilai penanganan Anda kurang matang dalam mengelola situasi dengan banyak tekanan sekaligus.', 'negative', ['IDN' => 0, 'CON' => 0, 'ACT' => 0]);
        });
    }
}
