<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi - IndiKarya Monitoring</title>
    <meta name="description" content="Kebijakan Privasi IndiKarya Monitoring System">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        }
        .card-shadow {
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .section-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased">
    
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-800 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-lg">IndiKarya</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white text-sm font-medium transition">
                        Beranda
                    </a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                            Login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="gradient-header text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">Kebijakan Privasi</h1>
                    <p class="text-blue-100 mt-1">IndiKarya Monitoring System</p>
                </div>
            </div>
            <p class="text-blue-100 text-sm">Terakhir diperbarui: {{ now()->format('d F Y') }}</p>
        </div>
    </div>

    <!-- Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        
        <!-- Introduction -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                Selamat datang di <strong class="text-gray-900 dark:text-white">IndiKarya Monitoring System</strong>. Kami menghargai kepercayaan Anda dalam menggunakan aplikasi kami. 
                Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi data pribadi Anda 
                saat menggunakan aplikasi monitoring untuk karyawan cleaning service dan security.
            </p>
        </div>

        <!-- Data Collection -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">1. Data yang Kami Kumpulkan</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Kami mengumpulkan data berikut untuk keperluan operasional sistem:</p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Data Pribadi:</strong> Nama lengkap, NIP/ID Karyawan, email, dan nomor telepon</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Data Lokasi:</strong> Lokasi GPS saat melakukan check-in dan check-out</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Data Presensi:</strong> Waktu masuk, waktu pulang, status kehadiran, dan riwayat shift</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Data Aktivitas:</strong> Foto dokumentasi patroli, checkpoint, dan laporan shift</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Data Perangkat:</strong> Informasi perangkat yang digunakan untuk akses aplikasi</span>
                </li>
            </ul>
        </div>

        <!-- Data Usage -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <i data-lucide="settings" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">2. Penggunaan Data</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Data yang kami kumpulkan digunakan untuk:</p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Mencatat dan memantau kehadiran karyawan secara real-time</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Menghitung jam kerja, lembur, dan keterlambatan</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Melacak aktivitas patroli dan checkpoint security</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Menghasilkan laporan kinerja dan produktivitas</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Memastikan keselamatan dan keamanan area kerja</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"></i>
                    <span>Komunikasi internal terkait jadwal dan perubahan shift</span>
                </li>
            </ul>
        </div>

        <!-- Data Security -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <i data-lucide="server" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">3. Penyimpanan dan Keamanan Data</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Kami mengambil langkah-langkah keamanan yang ketat untuk melindungi data Anda:</p>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <i data-lucide="lock" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Enkripsi Data</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Semua data sensitif dienkripsi saat disimpan dan ditransmisikan</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <i data-lucide="key" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Autentikasi</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sistem autentikasi berlapis dengan token keamanan</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <i data-lucide="cloud" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Server Aman</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Data disimpan di server dengan standar keamanan tinggi</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                    <i data-lucide="clock" class="w-5 h-5 text-gray-500 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Retensi Terbatas</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Data disimpan sesuai kebutuhan operasional dan regulasi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Sharing -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-orange-600 dark:text-orange-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">4. Pembagian Data</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Kami <strong class="text-gray-900 dark:text-white">tidak menjual</strong> data pribadi Anda kepada pihak ketiga. Data hanya dibagikan dalam kondisi berikut:</p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0"></i>
                    <span>Kepada atasan langsung dan tim HR untuk keperluan administrasi</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0"></i>
                    <span>Kepada klien/project terkait untuk laporan kinerja (tanpa data pribadi sensitif)</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0"></i>
                    <span>Jika diwajibkan oleh hukum atau perintah pengadilan</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-orange-500 mt-0.5 flex-shrink-0"></i>
                    <span>Dengan persetujuan eksplisit dari karyawan yang bersangkutan</span>
                </li>
            </ul>
        </div>

        <!-- User Rights -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                    <i data-lucide="hand" class="w-5 h-5 text-teal-600 dark:text-teal-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Hak Pengguna</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Sebagai pengguna, Anda memiliki hak untuk:</p>
            <div class="grid md:grid-cols-2 gap-3">
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Mengakses data pribadi Anda</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Meminta koreksi data yang tidak akurat</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Meminta penghapusan data (dengan batasan)</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Mengajukan keberatan atas penggunaan data</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Mendapatkan salinan data Anda</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <i data-lucide="check-circle" class="w-5 h-5 text-teal-500"></i>
                    <span class="text-gray-700 dark:text-gray-300">Mengajukan keluhan terkait privasi</span>
                </div>
            </div>
        </div>

        <!-- Location Data -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">6. Data Lokasi</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Aplikasi ini mengumpulkan data lokasi GPS untuk:</p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300 mb-4">
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"></i>
                    <span>Memverifikasi keberadaan karyawan di lokasi kerja saat check-in/check-out</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"></i>
                    <span>Melacak rute patroli security</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"></i>
                    <span>Memastikan checkpoint dibersihkan di lokasi yang benar</span>
                </li>
            </ul>
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-yellow-800 dark:text-yellow-200">Penting</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">Data lokasi hanya dikumpulkan selama jam kerja dan saat menggunakan fitur terkait. Kami tidak melacak lokasi di luar jam kerja.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Data -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <i data-lucide="camera" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. Data Foto</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Foto yang diambil melalui aplikasi digunakan untuk:</p>
            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Presensi:</strong> Verifikasi identitas karyawan saat check-in/check-out</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Patroli:</strong> Dokumentasi kondisi area selama patroli security</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Checkpoint:</strong> Bukti pembersihan area oleh cleaning service</span>
                </li>
                <li class="flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0"></i>
                    <span><strong class="text-gray-900 dark:text-white">Laporan Shift:</strong> Dokumentasi kondisi akhir shift</span>
                </li>
            </ul>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Semua foto disimpan dengan aman dan hanya dapat diakses oleh pihak berwenang.</p>
        </div>

        <!-- Contact -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 md:p-8 card-shadow mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <i data-lucide="mail" class="w-5 h-5 text-gray-600 dark:text-gray-400"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">8. Hubungi Kami</h2>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Jika Anda memiliki pertanyaan atau kekhawatiran tentang kebijakan privasi ini, silakan hubungi:</p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="mailto:privacy@indikarya.com" class="flex items-center gap-2 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                    <span>privacy@indikarya.com</span>
                </a>
                <a href="tel:+62123456789" class="flex items-center gap-2 px-4 py-3 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                    <i data-lucide="phone" class="w-5 h-5"></i>
                    <span>+62 123 456 789</span>
                </a>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-800 flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">IndiKarya</span>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                    <a href="/" class="hover:text-gray-900 dark:hover:text-white transition">Beranda</a>
                    <a href="/privacy-policy" class="hover:text-gray-900 dark:hover:text-white transition">Kebijakan Privasi</a>
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="hover:text-gray-900 dark:hover:text-white transition">Login</a>
                    @endif
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>© {{ now()->year }} IndiKarya. Hak Cipta Dilindungi.</p>
                <p class="mt-1">Kebijakan Privasi ini dapat diperbarui sewaktu-waktu.</p>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    
</body>
</html>
