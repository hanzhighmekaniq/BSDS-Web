<?php

namespace Database\Seeders;

use App\Models\FeedbackTemplate;
use App\Models\Indicator;
use Illuminate\Database\Seeder;

/**
 * Indikator PLACEHOLDER berdasarkan contoh "Profil Performa" pada desain PDF.
 * Ganti dengan indikator final Bab III (teori keterampilan pengambilan keputusan)
 * lewat panel admin atau dengan menyunting seeder ini.
 */
class IndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            'IDN' => [
                'name' => 'Identifikasi masalah',
                'description' => 'Kemampuan mengenali masalah utama dari situasi pelayanan.',
                'feedback' => [
                    'baik' => ['Anda mampu mengidentifikasi masalah utama dengan tepat sebelum bertindak.', 'Pertahankan kebiasaan merumuskan masalah utama terlebih dahulu.'],
                    'cukup' => ['Anda cukup mampu mengenali masalah, namun beberapa kali belum menyentuh akar masalahnya.', 'Latih diri bertanya: apa masalah utama pelanggan saat ini?'],
                    'perlu_latihan' => ['Anda cenderung bertindak sebelum memahami masalah utamanya.', 'Sebelum memilih tindakan, tuliskan dulu masalah utama dalam satu kalimat.'],
                ],
            ],
            'INF' => [
                'name' => 'Pengumpulan informasi',
                'description' => 'Kemampuan memilih dan menggunakan informasi yang relevan (SOP, transaksi, stok, supervisor).',
                'feedback' => [
                    'baik' => ['Anda memilih informasi yang relevan sebelum mengambil keputusan.', 'Teruskan kebiasaan memeriksa SOP dan data sebelum bertindak.'],
                    'cukup' => ['Anda sudah mencari informasi, tetapi belum selalu memilih yang paling relevan.', 'Tanyakan: informasi apa yang paling menentukan keputusan ini?'],
                    'perlu_latihan' => ['Anda jarang mencari informasi sebelum memutuskan, atau memilih informasi yang kurang relevan.', 'Biasakan memeriksa SOP dan data pendukung sebelum menawarkan solusi.'],
                ],
            ],
            'ALT' => [
                'name' => 'Pertimbangan alternatif',
                'description' => 'Kemampuan menimbang beberapa alternatif tindakan.',
                'feedback' => [
                    'baik' => ['Anda menimbang beberapa alternatif dan memilih yang sesuai kondisi.', 'Pertahankan kebiasaan menyiapkan lebih dari satu opsi solusi.'],
                    'cukup' => ['Anda mempertimbangkan alternatif, tetapi belum membandingkannya secara menyeluruh.', 'Bandingkan minimal dua alternatif sebelum memilih.'],
                    'perlu_latihan' => ['Anda cenderung langsung memilih satu solusi tanpa membandingkan alternatif.', 'Sebutkan setidaknya dua alternatif tindakan dan kelebihan/kekurangannya.'],
                ],
            ],
            'ACT' => [
                'name' => 'Pemilihan tindakan',
                'description' => 'Ketepatan tindakan yang dipilih terhadap situasi dan prosedur.',
                'feedback' => [
                    'baik' => ['Tindakan yang Anda pilih sesuai dengan situasi dan prosedur.', 'Pertahankan ketepatan dalam memilih tindakan.'],
                    'cukup' => ['Sebagian tindakan Anda sudah tepat, sebagian masih kurang sesuai prosedur.', 'Cocokkan tindakan dengan SOP sebelum dilakukan.'],
                    'perlu_latihan' => ['Beberapa tindakan Anda kurang sesuai dengan situasi atau prosedur.', 'Ulangi simulasi dan perhatikan SOP serta kondisi pelanggan.'],
                ],
            ],
            'CON' => [
                'name' => 'Pertimbangan konsekuensi',
                'description' => 'Kemampuan mengantisipasi dampak lanjutan dari keputusan.',
                'feedback' => [
                    'baik' => ['Anda mempertimbangkan dampak lanjutan dari keputusan Anda.', 'Teruskan kebiasaan memikirkan "apa yang terjadi setelah ini?".'],
                    'cukup' => ['Anda cukup memperhatikan dampak, namun kadang masih fokus pada masalah yang sedang dihadapi saja.', 'Tanyakan: apakah keputusan ini dapat menimbulkan masalah baru?'],
                    'perlu_latihan' => ['Anda cenderung menyelesaikan masalah saat ini tanpa memikirkan masalah berikutnya.', 'Sebelum memutuskan, pikirkan siapa lagi yang terdampak.'],
                ],
            ],
            'ADP' => [
                'name' => 'Adaptasi terhadap masalah baru',
                'description' => 'Kemampuan menyesuaikan tindakan saat situasi berubah.',
                'feedback' => [
                    'baik' => ['Anda mampu menyesuaikan tindakan ketika situasi berubah.', 'Pertahankan fleksibilitas Anda menghadapi situasi baru.'],
                    'cukup' => ['Anda cukup mampu beradaptasi, tetapi respons terhadap situasi baru belum selalu tepat.', 'Latih kembali skenario yang melibatkan perubahan situasi.'],
                    'perlu_latihan' => ['Anda masih kesulitan menyesuaikan tindakan ketika muncul masalah baru.', 'Latih skenario dengan beberapa masalah sekaligus.'],
                ],
            ],
        ];

        $order = 1;
        foreach ($indicators as $code => $data) {
            $indicator = Indicator::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'sort_order' => $order++,
                    'is_active' => true,
                ]
            );

            foreach ($data['feedback'] as $category => [$feedback, $recommendation]) {
                FeedbackTemplate::updateOrCreate(
                    ['indicator_id' => $indicator->id, 'category' => $category],
                    ['feedback_text' => $feedback, 'recommendation_text' => $recommendation]
                );
            }
        }
    }
}
