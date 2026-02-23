<?php

namespace Database\Seeders;

use App\Models\DailyQuote;
use App\Models\User;
use Illuminate\Database\Seeder;

class DailyQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $createdBy = $admin?->id ?? 1;

        $quotes = [
            // Motivasi
            [
                'title' => 'Semangat Pagi',
                'content' => 'Kesuksesan adalah hasil dari kerja keras, ketekunan, dan pembelajaran dari kegagalan.',
            ],
            [
                'title' => 'Jangan Menyerah',
                'content' => 'Ketika kamu merasa ingin menyerah, ingatlah mengapa kamu memulai.',
            ],
            [
                'title' => 'Langkah Kecil',
                'content' => 'Perjalanan seribu mil dimulai dengan satu langkah kecil.',
            ],
            [
                'title' => 'Percaya Diri',
                'content' => 'Percayalah pada dirimu sendiri dan semua yang kamu miliki. Ketahuilah bahwa ada sesuatu di dalam dirimu yang lebih besar dari hambatan apapun.',
            ],
            [
                'title' => 'Kesempatan',
                'content' => 'Kesempatan tidak datang dua kali. Manfaatkan setiap kesempatan yang ada dengan sebaik-baiknya.',
            ],
            
            // Inspirasi
            [
                'title' => 'Mimpi Besar',
                'content' => 'Bermimpilah setinggi langit. Jika engkau jatuh, engkau akan jatuh di antara bintang-bintang.',
            ],
            [
                'title' => 'Perubahan',
                'content' => 'Jadilah perubahan yang ingin kamu lihat di dunia.',
            ],
            [
                'title' => 'Masa Depan',
                'content' => 'Masa depan milik mereka yang percaya pada keindahan mimpi mereka.',
            ],
            [
                'title' => 'Kegagalan',
                'content' => 'Kegagalan adalah kesempatan untuk memulai lagi dengan lebih cerdas.',
            ],
            [
                'title' => 'Belajar',
                'content' => 'Pendidikan adalah senjata paling ampuh yang bisa kamu gunakan untuk mengubah dunia.',
            ],
            
            // Kerja
            [
                'title' => 'Kerja Keras',
                'content' => 'Tidak ada yang bisa menggantikan kerja keras. Keberuntungan adalah hasil dari persiapan bertemu kesempatan.',
            ],
            [
                'title' => 'Tim Solid',
                'content' => 'Sendirian kita bisa melakukan sedikit, bersama kita bisa melakukan banyak.',
            ],
            [
                'title' => 'Profesional',
                'content' => 'Profesionalisme adalah mengetahui bagaimana melakukannya, kapan melakukannya, dan melakukannya.',
            ],
            [
                'title' => 'Dedikasi',
                'content' => 'Dedikasi dan komitmen adalah kunci untuk mencapai kesuksesan dalam pekerjaan.',
            ],
            [
                'title' => 'Tanggung Jawab',
                'content' => 'Tanggung jawab adalah harga dari kesuksesan. Lakukan tugasmu dengan sepenuh hati.',
            ],
            
            // Keselamatan Kerja
            [
                'title' => 'Keselamatan Utama',
                'content' => 'Keselamatan bukan kebetulan, keselamatan adalah pilihan. Bekerjalah dengan aman, pulang dengan selamat.',
            ],
            [
                'title' => 'Hati-hati',
                'content' => 'Satu detik kelalaian bisa mengakibatkan kecelakaan seumur hidup. Selalu berhati-hati dalam bekerja.',
            ],
            [
                'title' => 'Prosedur Keselamatan',
                'content' => 'Ikuti prosedur keselamatan kerja. Keluarga menunggu kepulanganmu dengan selamat.',
            ],
            [
                'title' => 'Alat Pelindung',
                'content' => 'Gunakan alat pelindung diri dengan benar. Keselamatanmu adalah tanggung jawabmu.',
            ],
            [
                'title' => 'Waspada',
                'content' => 'Tetap waspada dan fokus saat bekerja. Keselamatan adalah prioritas utama.',
            ],
            
            // Tambahan
            [
                'title' => 'Hari Baru',
                'content' => 'Setiap hari adalah kesempatan baru untuk menjadi lebih baik dari kemarin.',
            ],
            [
                'title' => 'Bersyukur',
                'content' => 'Bersyukurlah atas apa yang kamu miliki saat ini, sambil bekerja untuk apa yang kamu inginkan.',
            ],
            [
                'title' => 'Sikap Positif',
                'content' => 'Sikap positif akan membawa hasil positif. Mulailah hari dengan senyuman.',
            ],
            [
                'title' => 'Konsisten',
                'content' => 'Konsistensi adalah kunci kesuksesan. Lakukan yang terbaik setiap hari.',
            ],
            [
                'title' => 'Integritas',
                'content' => 'Integritas adalah melakukan hal yang benar, bahkan ketika tidak ada yang melihat.',
            ],
        ];

        foreach ($quotes as $quote) {
            DailyQuote::create([
                'title' => $quote['title'],
                'content' => $quote['content'],
                'created_by' => $createdBy,
            ]);
        }

        $this->command->info('✅ Created ' . count($quotes) . ' daily quotes');
    }
}
