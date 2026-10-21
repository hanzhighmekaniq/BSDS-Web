<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Business Service Decision Simulator &bull; Media Pembelajaran Pengambilan Keputusan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="overflow-x-hidden font-sans antialiased text-bsds-ink bg-bsds-bg selection:bg-bsds-teal/20 selection:text-bsds-navy">

    <!-- Header -->
    <header x-data="{ menuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 10)"
        x-bind:class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b border-bsds-border/80' :
            'bg-white border-b border-bsds-border'"
        class="sticky top-0 z-50 transition-all duration-300">
        <div class="flex items-center justify-between gap-3 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 h-18">
            <!-- Brand -->
            <a href="/" class="flex items-center min-w-0 gap-3 group">
                <div class="p-2 transition-colors rounded-xl bg-bsds-navy/5 group-hover:bg-bsds-teal/10 shrink-0">
                    <x-application-logo
                        class="w-auto h-8 transition-colors text-bsds-navy group-hover:text-bsds-teal" />
                </div>
                <span
                    class="text-sm font-bold leading-tight tracking-tight sm:text-lg font-heading text-bsds-navy">Business
                    Service Decision Simulator</span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="items-center hidden gap-8 text-sm font-medium lg:flex text-bsds-gray">
                <a href="#fitur" class="transition-colors hover:text-bsds-navy">Fitur Unggulan</a>
                <a href="#alur" class="transition-colors hover:text-bsds-navy">Alur Simulasi</a>
                <a href="#faq" class="transition-colors hover:text-bsds-navy">FAQ</a>
                <a href="{{ route('guide') }}"
                    class="inline-flex items-center gap-1 font-semibold transition-colors hover:text-bsds-teal text-bsds-teal/90">
                    <x-heroicon-o-book-open class="w-4 h-4" />
                    Panduan
                </a>
            </nav>

            @auth
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('dashboard') }}"
                        class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                        <x-heroicon-o-play class="w-4 h-4 text-bsds-orange" />
                        <span>Buka Dashboard</span>
                    </a>
                    <!-- Menu fitur: hanya untuk pengguna yang sudah masuk -->
                    <button type="button" @click="menuOpen = !menuOpen"
                        class="p-2 rounded-xl text-bsds-navy hover:bg-bsds-bg focus:outline-none"
                        aria-label="Buka menu fitur">
                        <x-heroicon-o-bars-3 x-show="!menuOpen" class="w-6 h-6" />
                        <x-heroicon-o-x-mark x-show="menuOpen" class="w-6 h-6" x-cloak />
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('login') }}"
                        class="px-3 py-2 text-sm font-semibold transition-colors text-bsds-navy hover:text-bsds-teal">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                            <span>Daftar</span>
                            <x-heroicon-o-arrow-right class="hidden w-4 h-4 sm:block" />
                        </a>
                    @endif
                </div>
            @endauth
        </div>

        @auth
            <!-- Feature Drawer -->
            <div x-show="menuOpen" x-transition x-cloak @click.outside="menuOpen = false"
                class="px-4 pt-3 pb-6 bg-white border-b border-bsds-border">
                <nav class="grid max-w-7xl gap-1 mx-auto text-sm font-medium sm:grid-cols-3 text-bsds-ink">
                    @php
                        $menu = auth()->user()->hasRole('student')
                            ? [
                                ['dashboard', 'Dashboard', 'home'],
                                ['materials.index', 'Materi', 'academic-cap'],
                                ['quizzes.index', 'Quiz', 'clipboard-document-check'],
                                ['simulations.index', 'Simulasi', 'play'],
                                ['progress', 'Progress', 'chart-bar'],
                                ['guide', 'Panduan', 'question-mark-circle'],
                            ]
                            : [['dashboard', 'Dashboard', 'home'], ['guide', 'Panduan', 'question-mark-circle']];
                    @endphp
                    @foreach ($menu as [$route, $label, $icon])
                        @if (Route::has($route))
                            <a href="{{ route($route) }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-bsds-bg">
                                <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5 text-bsds-teal" />
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        @endauth
    </header>

    <main class="relative">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[680px] pointer-events-none overflow-hidden -z-10">
            <div class="absolute rounded-full top-10 left-10 w-96 h-96 bg-bsds-teal/10 blur-3xl"></div>
            <div class="absolute rounded-full top-36 right-10 w-96 h-96 bg-bsds-orange/10 blur-3xl"></div>
            <div class="absolute rounded-full top-80 left-1/3 w-80 h-80 bg-bsds-navy/5 blur-3xl"></div>
        </div>

        <!-- HERO -->
        <section class="px-4 pt-12 pb-16 mx-auto max-w-7xl sm:px-6 lg:px-8 sm:pt-16 lg:pb-24">
            <div class="max-w-3xl mx-auto text-center">
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-bsds-border shadow-xs text-xs font-semibold text-bsds-teal">
                    <span class="relative flex w-2 h-2">
                        <span
                            class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-bsds-teal"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-bsds-teal"></span>
                    </span>
                    <span>Platform Simulasi Pengambilan Keputusan Bisnis</span>
                </div>

                <h1
                    class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-bsds-navy tracking-tight leading-[1.15] mt-6">
                    Belajar Mengambil Keputusan dalam <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-bsds-teal to-bsds-navy">Pelayanan
                        Bisnis</span>
                </h1>

                <p class="max-w-2xl mx-auto mt-5 leading-relaxed text-bsds-gray sm:text-lg">
                    Hadapi berbagai situasi pelayanan bisnis, pilih tindakan yang tepat, dan pelajari dampaknya melalui
                    simulasi.
                </p>

                <div class="flex justify-center mt-8">
                    <a href="{{ Auth::check() ? route('simulations.index') : (Route::has('register') ? route('register') : route('login')) }}"
                        class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-2.5 px-8 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-base shadow-md hover:shadow-lg transition group">
                        <x-heroicon-o-play
                            class="w-5 h-5 transition-transform text-bsds-orange group-hover:scale-110" />
                        <span>Mulai Simulasi Sekarang</span>
                        <x-heroicon-o-arrow-right
                            class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

                <div
                    class="flex flex-col items-center justify-center gap-3 mt-8 text-sm font-medium sm:flex-row sm:gap-6 text-bsds-gray">
                    <span class="inline-flex items-center gap-2">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-bsds-teal" />
                        Belajar dengan mudah
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-bsds-orange" />
                        Asah kemampuan pengambilan keputusan
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-5 h-5 text-bsds-navy" />
                        Belajar dari setiap pilihan
                    </span>
                </div>
            </div>
        </section>

        <!-- FITUR UNGGULAN -->
        <section id="fitur" class="px-4 py-20 mx-auto lg:py-28 max-w-7xl sm:px-6 lg:px-8 scroll-mt-20">
            <div class="max-w-3xl mx-auto mb-16 text-center">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                    <x-heroicon-o-sparkles class="w-4 h-4 text-bsds-teal" />
                    Fitur Unggulan
                </span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                    Dirancang untuk Melatih Cara Berpikir dalam Pelayanan
                </h2>
                <p class="mt-3 text-base text-bsds-gray sm:text-lg">
                    Pelajari materi, uji pemahaman lewat quiz, lalu praktikkan dalam simulasi situasi pelayanan
                    bisnis.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Materi Pembelajaran', 'Pelajari konsep dasar pelayanan bisnis dan pengambilan keputusan sebelum masuk ke simulasi.', 'book-open'],
                    ['Quiz Pemahaman', 'Uji pemahaman materi dengan soal pilihan ganda dan lihat skor kamu langsung.', 'clipboard-document-check'],
                    ['Simulasi Bercabang', 'Setiap pilihan membuka alur berbeda, sehingga kamu bisa melihat dampak dari setiap keputusan.', 'arrows-pointing-out'],
                    ['Investigasi Bukti & SOP', 'Periksa SOP, riwayat transaksi, dan data pendukung agar keputusan berdasarkan fakta.', 'magnifying-glass'],
                    ['Petunjuk Bertahap', 'Gunakan petunjuk bertingkat yang membimbing pola pikir tanpa langsung memberi jawaban.', 'light-bulb'],
                    ['Penilaian 4 Indikator', 'Dapatkan umpan balik per indikator untuk mengetahui kekuatan dan bagian yang perlu dilatih.', 'chart-bar'],
                ] as [$title, $desc, $icon])
                    <div
                        class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg">
                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-7 h-7 mb-4 text-bsds-teal" />
                        <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">{{ $title }}</h3>
                        <p class="flex-1 text-sm leading-relaxed text-bsds-gray">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ALUR SIMULASI -->
        <section id="alur" class="py-20 bg-white border-y border-bsds-border scroll-mt-20">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto mb-16 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                        <x-heroicon-o-play class="w-3.5 h-3.5 text-bsds-teal" />
                        Alur Simulasi
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                        Bagaimana Simulasi Dijalankan?
                    </h2>
                    <p class="mt-3 text-base text-bsds-gray sm:text-lg">
                        4 langkah sederhana untuk berlatih menangani situasi pelayanan bisnis.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    @foreach ([
                        ['01', 'bg-bsds-navy', 'Baca Situasi', 'Pahami peran, latar belakang masalah, dan kebutuhan pelanggan.'],
                        ['02', 'bg-bsds-teal', 'Cari Informasi', 'Periksa SOP, data pesanan, dan batas kewenangan posisi kamu.'],
                        ['03', 'bg-bsds-orange', 'Pilih Tindakan', 'Tentukan tindakan terbaik dan lihat dampaknya pada situasi berikutnya.'],
                        ['04', 'bg-emerald-600', 'Refleksi & Nilai', 'Tinjau kembali jejak keputusan dan terima penilaian berdasarkan 4 indikator.'],
                    ] as [$no, $bg, $title, $desc])
                        <div class="flex flex-col p-6 border bg-bsds-bg rounded-2xl border-bsds-border">
                            <span
                                class="flex items-center justify-center w-10 h-10 mb-4 text-sm font-bold text-white rounded-xl font-heading {{ $bg }}">{{ $no }}</span>
                            <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">{{ $title }}</h3>
                            <p class="text-sm leading-relaxed text-bsds-gray">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="max-w-4xl px-4 py-20 mx-auto lg:py-28 sm:px-6 lg:px-8 scroll-mt-20"
            x-data="{ active: null }">
            <div class="text-center mb-14">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                    <x-heroicon-o-question-mark-circle class="w-4 h-4 text-bsds-teal" />
                    FAQ
                </span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                    Pertanyaan yang Sering Diajukan
                </h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['Siapa yang cocok menggunakan simulator ini?', 'Simulator ini dirancang untuk siswa dan mahasiswa bidang bisnis yang ingin mengasah keterampilan pengambilan keputusan dalam situasi pelayanan pelanggan.'],
                    ['Bagaimana cara mulai menggunakan?', 'Daftar akun terlebih dahulu, lalu masuk. Setelah itu kamu bisa memilih kelas, membaca materi, mengerjakan quiz, dan memulai simulasi.'],
                    ['Bagaimana jika saya memilih tindakan yang keliru?', 'Sistem akan mengarahkan kamu ke alur lanjutan sesuai dampak keputusan tersebut, sehingga kamu bisa belajar cara memulihkan situasi.'],
                    ['Apakah progres simulasi tersimpan jika keluar di tengah jalan?', 'Ya, progres tersimpan otomatis. Saat masuk kembali, kamu bisa melanjutkan dari situasi terakhir.'],
                ] as $i => [$q, $a])
                    <div class="overflow-hidden bg-white border rounded-2xl border-bsds-border">
                        <button type="button" @click="active = active === {{ $i }} ? null : {{ $i }}"
                            class="flex items-center justify-between w-full gap-4 px-6 py-5 text-base font-semibold text-left transition-colors font-heading text-bsds-navy hover:text-bsds-teal">
                            <span>{{ $q }}</span>
                            <span class="transition-transform duration-200 shrink-0"
                                x-bind:class="active === {{ $i }} ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                                <x-heroicon-o-chevron-down class="w-5 h-5" />
                            </span>
                        </button>
                        <div x-show="active === {{ $i }}" x-transition x-cloak
                            class="px-6 pt-4 pb-5 text-sm leading-relaxed border-t text-bsds-gray border-bsds-border/60">
                            {{ $a }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="text-sm bg-white border-t border-bsds-border text-bsds-gray">
        <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 mb-10 md:grid-cols-4">
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center gap-2">
                        <x-application-logo class="w-auto h-8 text-bsds-navy" />
                        <span class="text-lg font-bold font-heading text-bsds-navy">Business Service Decision
                            Simulator</span>
                    </div>
                    <p class="max-w-sm text-xs leading-relaxed sm:text-sm text-bsds-gray">
                        Media pembelajaran berbasis web untuk melatih keterampilan pengambilan keputusan dalam situasi
                        pelayanan bisnis.
                    </p>
                </div>

                <div>
                    <h4 class="mb-3 text-xs font-bold tracking-wider uppercase font-heading text-bsds-navy">Navigasi
                    </h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="#fitur" class="transition-colors hover:text-bsds-teal">Fitur Unggulan</a></li>
                        <li><a href="#alur" class="transition-colors hover:text-bsds-teal">Alur Simulasi</a></li>
                        <li><a href="#faq" class="transition-colors hover:text-bsds-teal">FAQ</a></li>
                        <li><a href="{{ route('guide') }}" class="transition-colors hover:text-bsds-teal">Panduan</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-3 text-xs font-bold tracking-wider uppercase font-heading text-bsds-navy">Akun</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        @auth
                            <li><a href="{{ route('dashboard') }}"
                                    class="transition-colors hover:text-bsds-teal">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition-colors hover:text-bsds-teal">Masuk</a>
                            </li>
                            @if (Route::has('register'))
                                <li><a href="{{ route('register') }}"
                                        class="transition-colors hover:text-bsds-teal">Daftar Akun</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="pt-8 text-xs text-center border-t border-bsds-border text-bsds-gray">
                &copy; {{ date('Y') }} Business Service Decision Simulator. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>
</body>

</html>
