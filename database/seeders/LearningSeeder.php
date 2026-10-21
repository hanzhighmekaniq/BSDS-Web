<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/**
 * Contoh materi & quiz. Isi final dikelola admin lewat panel.
 */
class LearningSeeder extends Seeder
{
    public function run(): void
    {
        $material = Material::updateOrCreate(
            ['title' => 'Dasar Pelayanan Pelanggan'],
            [
                'summary' => 'Pengertian pelayanan, sikap dasar petugas, dan langkah menangani keluhan.',
                'content' => "Pelayanan pelanggan adalah upaya membantu pelanggan memenuhi kebutuhannya dengan cara yang sopan, cepat, dan tepat.\n\nSikap dasar petugas pelayanan:\n1. Ramah dan sopan.\n2. Mendengarkan dengan sungguh-sungguh.\n3. Menguasai informasi produk dan SOP.\n4. Bertanggung jawab atas janji yang diberikan.\n\nLangkah menangani keluhan:\n1. Dengarkan keluhan sampai selesai tanpa memotong.\n2. Sampaikan permintaan maaf dan empati.\n3. Cari informasi pendukung (SOP, data transaksi, stok).\n4. Tawarkan beberapa alternatif solusi.\n5. Lakukan tindakan yang disepakati dan pastikan pelanggan puas.",
                'sort_order' => 1,
                'is_published' => true,
            ]
        );

        $quiz = Quiz::updateOrCreate(
            ['title' => 'Quiz Dasar Pelayanan Pelanggan'],
            ['description' => 'Uji pemahaman materi dasar pelayanan.', 'material_id' => $material->id, 'is_published' => true]
        );

        $quiz->questions()->delete();

        foreach ([
            ['Langkah pertama saat pelanggan menyampaikan keluhan adalah...', ['A' => 'Langsung memberi solusi', 'B' => 'Mendengarkan keluhan sampai selesai', 'C' => 'Memanggil supervisor', 'D' => 'Menjelaskan aturan perusahaan'], 'B'],
            ['Sebelum menentukan solusi, petugas sebaiknya...', ['A' => 'Mencari informasi pendukung seperti SOP dan data transaksi', 'B' => 'Menuruti semua permintaan pelanggan', 'C' => 'Menolak permintaan pelanggan', 'D' => 'Menunda penanganan'], 'A'],
            ['Sikap yang TIDAK sesuai bagi petugas pelayanan adalah...', ['A' => 'Ramah dan sopan', 'B' => 'Menguasai SOP', 'C' => 'Memberi janji yang tidak bisa ditepati', 'D' => 'Mendengarkan dengan baik'], 'C'],
        ] as $i => [$question, $options, $answer]) {
            $quiz->questions()->create(['question' => $question, 'options' => $options, 'answer' => $answer, 'sort_order' => $i]);
        }
    }
}
