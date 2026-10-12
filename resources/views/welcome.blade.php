<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Business Service Decision Simulator') }} &bull; Media Pembelajaran Interaktif</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="overflow-x-hidden font-sans antialiased text-bsds-ink bg-bsds-bg selection:bg-bsds-teal/20 selection:text-bsds-navy">

    <!-- Sticky Header with Backdrop Blur -->
    <header x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 10)"
        x-bind:class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b border-bsds-border/80' :
            'bg-white border-b border-bsds-border'"
        class="sticky top-0 z-50 transition-all duration-300">
        <div class="flex items-center justify-between px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 h-18">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="p-2 transition-colors rounded-xl bg-bsds-navy/5 group-hover:bg-bsds-teal/10">
                    <x-application-logo
                        class="w-auto h-8 transition-colors text-bsds-navy group-hover:text-bsds-teal" />
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold tracking-tight font-heading text-bsds-navy">BSDS</span>
                        <span
                            class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-bsds-teal/15 text-bsds-teal">
                            Simulator
                        </span>
                    </div>
                    <span class="hidden md:block text-[11px] text-bsds-gray -mt-0.5">Business Service Decision</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="items-center hidden gap-8 text-sm font-medium lg:flex text-bsds-gray">
                <a href="#fitur" class="transition-colors hover:text-bsds-navy">Fitur Unggulan</a>
                <a href="#alur" class="transition-colors hover:text-bsds-navy">Alur Simulasi</a>
                <a href="#skenario" class="transition-colors hover:text-bsds-navy">Skenario Kasus</a>
                <a href="#indikator" class="transition-colors hover:text-bsds-navy">Indikator</a>
                <a href="#faq" class="transition-colors hover:text-bsds-navy">FAQ</a>
                <a href="{{ route('guide') }}"
                    class="inline-flex items-center gap-1 font-semibold transition-colors hover:text-bsds-teal text-bsds-teal/90">
                    <x-heroicon-o-book-open class="w-4 h-4" />
                    Panduan
                </a>
            </nav>

            <!-- Auth Buttons -->
            <div class="items-center hidden gap-3 sm:flex">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                        <x-heroicon-o-play class="w-4 h-4 text-bsds-orange" />
                        <span>Buka Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="px-4 py-2 text-sm font-semibold transition-colors text-bsds-navy hover:text-bsds-teal">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                            <span>Daftar Akun</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Mobile Menu Hamburger Button -->
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="p-2 lg:hidden rounded-xl text-bsds-navy hover:bg-bsds-bg focus:outline-none"
                aria-label="Toggle navigation menu">
                <x-heroicon-o-bars-3 x-show="!mobileMenuOpen" class="w-6 h-6" />
                <x-heroicon-o-x-mark x-show="mobileMenuOpen" class="w-6 h-6" x-cloak />
            </button>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" x-collapse x-cloak
            class="px-4 pt-3 pb-6 space-y-4 bg-white border-b lg:hidden border-bsds-border">
            <nav class="flex flex-col space-y-2 text-sm font-medium text-bsds-gray">
                <a @click="mobileMenuOpen = false" href="#fitur"
                    class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Fitur Unggulan</a>
                <a @click="mobileMenuOpen = false" href="#alur"
                    class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Alur Simulasi</a>
                <a @click="mobileMenuOpen = false" href="#skenario"
                    class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Skenario Kasus</a>
                <a @click="mobileMenuOpen = false" href="#indikator"
                    class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Indikator Kompetensi</a>
                <a @click="mobileMenuOpen = false" href="#faq"
                    class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">FAQ</a>
                <a @click="mobileMenuOpen = false" href="{{ route('guide') }}"
                    class="px-3 py-2 font-semibold rounded-lg hover:bg-bsds-bg text-bsds-teal">Panduan Lengkap</a>
            </nav>
            <div class="flex flex-col gap-2 pt-4 border-t border-bsds-border">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="w-full text-center py-2.5 rounded-xl bg-bsds-navy text-white text-sm font-semibold">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="w-full text-center py-2.5 rounded-xl border border-bsds-border text-bsds-navy text-sm font-semibold hover:bg-bsds-bg">
                        Masuk
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="w-full text-center py-2.5 rounded-xl bg-bsds-navy text-white text-sm font-semibold">
                            Daftar Akun
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main class="relative">
        <!-- Background Ambient Glow & Patterns -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[680px] pointer-events-none overflow-hidden -z-10">
            <div class="absolute rounded-full top-10 left-10 w-96 h-96 bg-bsds-teal/10 blur-3xl"></div>
            <div class="absolute rounded-full top-36 right-10 w-96 h-96 bg-bsds-orange/10 blur-3xl"></div>
            <div class="absolute rounded-full top-80 left-1/3 w-80 h-80 bg-bsds-navy/5 blur-3xl"></div>
        </div>

        <!-- HERO SECTION -->
        <section class="px-4 pt-12 pb-16 mx-auto max-w-7xl sm:px-6 lg:px-8 sm:pt-16 lg:pb-24">
            <div class="max-w-3xl mx-auto text-center">
                <!-- Eyebrow Tag -->
                <div
                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-bsds-border shadow-xs text-xs font-semibold text-bsds-teal">
                    <span class="relative flex w-2 h-2">
                        <span
                            class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-bsds-teal"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-bsds-teal"></span>
                    </span>
                    <span>Platform Simulasi Pengambilan Keputusan Bisnis</span>
                </div>

                <!-- Main Headline -->
                <h1
                    class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-bsds-navy tracking-tight leading-[1.15] mt-6">
                    Latih Refleks Keputusan Layanan Bisnis <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-bsds-teal to-bsds-navy">Secara
                        Nyata</span>
                </h1>

                <!-- Slogan & Description -->
                <p class="mt-4 text-base font-semibold tracking-wide font-heading text-bsds-teal sm:text-lg">
                    Decide. Experience. Learn.
                </p>

                <p class="max-w-2xl mx-auto mt-3 leading-relaxed text-bsds-gray sm:text-lg">
                    Hadapi skenario komplain, dilema SOP, dan krisis operasional dalam simulasi bercabang adaptif.
                    Setiap pilihan memicu konsekuensi yang membentuk profil performa Anda.
                </p>

                <!-- Hero Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 mt-8">
                    <a href="{{ Auth::check() ? route('dashboard') : (Route::has('register') ? route('register') : route('login')) }}"
                        class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-2.5 px-8 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-base shadow-md hover:shadow-lg transition group">
                        <x-heroicon-o-play
                            class="w-5 h-5 transition-transform text-bsds-orange group-hover:scale-110" />
                        <span>Mulai Simulasi Sekarang</span>
                        <x-heroicon-o-arrow-right
                            class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" />
                    </a>

                    <a href="#alur"
                        class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-2 px-6 rounded-xl bg-white hover:bg-bsds-bg border border-bsds-border text-bsds-navy font-semibold text-base transition">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-bsds-gray" />
                        <span>Pelajari Alur Kerja</span>
                    </a>
                </div>

                <!-- Key Value Badges -->
                <div
                    class="flex flex-wrap items-center justify-center gap-4 mt-10 text-xs font-medium sm:gap-8 sm:text-sm text-bsds-gray">
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-bsds-success" />
                        <span>Lingkungan Aman Tanpa Risiko</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-4 h-4 text-bsds-teal" />
                        <span>Evaluasi 4 Pilar Kompetensi</span>
                    </div>
                    <div class="inline-flex items-center gap-2">
                        <x-heroicon-o-rectangle-stack class="w-4 h-4 text-bsds-orange" />
                        <span>Skenario Bercabang Dinamis</span>
                    </div>
                </div>
            </div>

            <!-- HERO INTERACTIVE MOCKUP / SIMULATION UI PREVIEW -->
            <div class="relative max-w-5xl mx-auto mt-14 lg:mt-18">
                <!-- Glow Behind Mockup -->
                <div
                    class="absolute -inset-1.5 bg-gradient-to-r from-bsds-navy/20 via-bsds-teal/20 to-bsds-orange/20 rounded-3xl blur-xl opacity-70">
                </div>

                <!-- Main App Card -->
                <div
                    class="relative overflow-hidden bg-white border shadow-2xl rounded-2xl sm:rounded-3xl border-bsds-border">
                    <!-- Top Window Bar -->
                    <div
                        class="bg-bsds-navy px-4 sm:px-6 py-3.5 flex items-center justify-between border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-red-400 rounded-full"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="hidden ml-3 font-mono text-xs text-white/70 sm:inline-block">BSDS Simulator
                                Workspace &bull; Skenario Interaktif</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-bsds-teal/20 text-teal-300 border border-teal-400/30">
                                Practice Mode
                            </span>
                            <span class="hidden font-mono text-xs text-white/60 sm:inline">ID: CASE-01-S2</span>
                        </div>
                    </div>

                    <!-- Mockup Content Grid -->
                    <div class="p-5 sm:p-8 bg-bsds-bg/60">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <!-- Left 2 Cols: Simulation Scenario Context & Choices -->
                            <div class="space-y-5 lg:col-span-2">
                                <!-- Scenario Header Card -->
                                <div class="p-5 bg-white border shadow-xs rounded-2xl border-bsds-border">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase bg-bsds-navy text-white">CASE-01</span>
                                            <span class="text-xs font-semibold text-bsds-gray">Titik Keputusan
                                                #02</span>
                                        </div>
                                        <span
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-bsds-orange">
                                            <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                            Est. 15 Mnt
                                        </span>
                                    </div>
                                    <h3 class="text-base font-bold font-heading sm:text-lg text-bsds-navy">
                                        Pelanggan VIP Mengeluhkan Barang Salah Kirim saat Promo
                                    </h3>
                                    <p class="mt-2 text-xs leading-relaxed sm:text-sm text-bsds-gray">
                                        Ibu Rani (Anggota Platinum) menerima varian produk yang keliru. Stok produk
                                        pengganti di sistem toko menunjukkan angka nol, sementara pelanggan meminta
                                        solusi dalam waktu 1 jam sebelum keberangkatan luar negeri.
                                    </p>

                                    <!-- Investigation Evidence Tabs -->
                                    <div
                                        class="flex flex-wrap items-center gap-2 pt-3 mt-4 border-t border-bsds-border/70">
                                        <span class="text-xs font-medium text-bsds-gray">Data Tersedia:</span>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-bsds-bg border border-bsds-border text-bsds-navy font-medium">
                                            <x-heroicon-o-document-text class="w-3.5 h-3.5 text-bsds-teal" /> SOP Retur
                                            Garansi
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-bsds-bg border border-bsds-border text-bsds-navy font-medium">
                                            <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-bsds-teal" /> Audit
                                            Stok Cabang
                                        </span>
                                    </div>
                                </div>

                                <!-- Option Selection Simulator -->
                                <div class="space-y-3">
                                    <div class="text-xs font-bold tracking-wider uppercase text-bsds-gray">
                                        Pilihan Tindakan Keputusan:
                                    </div>

                                    <!-- Option 1 -->
                                    <div
                                        class="flex items-start gap-3 p-4 transition bg-white border rounded-xl border-bsds-border hover:border-bsds-teal/50 hover:shadow-xs">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 text-xs font-bold border rounded-lg bg-bsds-bg text-bsds-navy font-heading shrink-0 border-bsds-border">A</span>
                                        <div class="flex-1">
                                            <div class="text-xs font-medium sm:text-sm text-bsds-ink">Tolak permintaan
                                                penggantian kilat karena stok sistem habis sesuai aturan SOP umum.</div>
                                            <div
                                                class="text-[11px] text-bsds-error mt-1 flex items-center gap-1 font-medium">
                                                <span>Konsekuensi: Risiko eskalasi emosi dan kehilangan loyalitas
                                                    pelanggan VIP</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Option 2 (Highlighted) -->
                                    <div
                                        class="relative flex items-start gap-3 p-4 border-2 shadow-xs bg-teal-50/70 rounded-xl border-bsds-teal">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 text-xs font-bold text-white rounded-lg bg-bsds-teal font-heading shrink-0">B</span>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div class="text-xs font-semibold sm:text-sm text-bsds-navy">Verifikasi
                                                    gudang transit cabang terdekat & tawarkan kurir instan prioritas.
                                                </div>
                                                <span
                                                    class="text-[10px] uppercase font-bold text-bsds-teal bg-white px-2 py-0.5 rounded-full border border-bsds-teal/20 hidden sm:inline-block">Pilihan
                                                    Terbaik</span>
                                            </div>
                                            <div
                                                class="text-[11px] text-bsds-teal font-medium mt-1 flex items-center gap-1">
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5 shrink-0" />
                                                <span>Konsekuensi positif: Menjaga SLA kepuasan pelanggan serta
                                                    memanfaatkan stok transit</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Option 3 -->
                                    <div
                                        class="flex items-start gap-3 p-4 transition bg-white border rounded-xl border-bsds-border hover:border-bsds-teal/50">
                                        <span
                                            class="flex items-center justify-center w-6 h-6 text-xs font-bold border rounded-lg bg-bsds-bg text-bsds-navy font-heading shrink-0 border-bsds-border">C</span>
                                        <div class="flex-1">
                                            <div class="text-xs font-medium sm:text-sm text-bsds-ink">Alihkan seluruh
                                                penanganan ke supervisor tanpa investigasi awal mandiri.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right 1 Col: Live Metrics & Feedback Sidebar -->
                            <div class="space-y-4">
                                <!-- Indicator Progress Widget -->
                                <div class="p-5 bg-white border shadow-xs rounded-2xl border-bsds-border">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4
                                            class="text-xs font-bold tracking-wider uppercase font-heading text-bsds-navy">
                                            Profil Indikator</h4>
                                        <span class="text-[11px] font-semibold text-bsds-teal">Live Analysis</span>
                                    </div>

                                    <div class="space-y-3">
                                        <div>
                                            <div class="flex justify-between mb-1 text-xs font-medium">
                                                <span class="text-bsds-ink">Identifikasi Masalah</span>
                                                <span class="font-semibold text-bsds-navy">88%</span>
                                            </div>
                                            <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-bsds-teal" style="width: 88%">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex justify-between mb-1 text-xs font-medium">
                                                <span class="text-bsds-ink">Pengumpulan Info (SOP)</span>
                                                <span class="font-semibold text-bsds-navy">92%</span>
                                            </div>
                                            <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-bsds-teal" style="width: 92%">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex justify-between mb-1 text-xs font-medium">
                                                <span class="text-bsds-ink">Pertimbangan Alternatif</span>
                                                <span class="font-semibold text-bsds-navy">80%</span>
                                            </div>
                                            <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-bsds-orange" style="width: 80%">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex justify-between mb-1 text-xs font-medium">
                                                <span class="text-bsds-ink">Ketepatan Tindakan</span>
                                                <span class="font-semibold text-bsds-navy">95%</span>
                                            </div>
                                            <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-bsds-success" style="width: 95%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Decision Journey Widget -->
                                <div
                                    class="bg-gradient-to-br from-bsds-navy to-[#1f4265] text-white p-5 rounded-2xl shadow-xs">
                                    <div class="flex items-center gap-2 mb-2 text-bsds-orange">
                                        <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                                        <span class="text-xs font-bold tracking-wider uppercase">Decision Tree</span>
                                    </div>
                                    <p class="text-xs leading-relaxed text-white/80">
                                        Setiap keputusan membuka skenario kelanjutan baru. Analisis rute perjalanan Anda
                                        setelah kasus selesai!
                                    </p>
                                    <div class="flex items-center gap-2 mt-4">
                                        <span
                                            class="px-2 py-1 rounded-md bg-white/10 text-[10px] font-mono text-emerald-300">S1:
                                            Optimal</span>
                                        <span class="text-white/40">&rarr;</span>
                                        <span
                                            class="px-2 py-1 rounded-md bg-white/20 text-[10px] font-mono text-yellow-300">S2:
                                            Aktif</span>
                                        <span class="text-white/40">&rarr;</span>
                                        <span
                                            class="px-2 py-1 rounded-md bg-white/5 text-[10px] font-mono text-white/40">S3:
                                            Next</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- STATS COUNTER STRIP -->
        <section class="py-10 bg-white border-y border-bsds-border sm:py-12">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="grid grid-cols-2 gap-6 text-center divide-y md:grid-cols-4 lg:gap-8 sm:divide-y-0 sm:divide-x divide-bsds-border/60">
                    <div class="pt-4 sm:pt-0">
                        <div class="text-3xl font-extrabold font-heading sm:text-4xl text-bsds-navy">4</div>
                        <div class="mt-1 text-xs font-semibold sm:text-sm text-bsds-teal">Indikator Kompetensi</div>
                        <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Identifikasi, Informasi,
                            Alternatif, Tindakan</p>
                    </div>
                    <div class="pt-4 sm:pt-0">
                        <div class="text-3xl font-extrabold font-heading sm:text-4xl text-bsds-navy">3</div>
                        <div class="mt-1 text-xs font-semibold sm:text-sm text-bsds-teal">Level Kasus Realistis</div>
                        <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Foundation, Developing, &
                            Challenge</p>
                    </div>
                    <div class="pt-4 sm:pt-0">
                        <div class="text-3xl font-extrabold font-heading sm:text-4xl text-bsds-navy">2</div>
                        <div class="mt-1 text-xs font-semibold sm:text-sm text-bsds-teal">Mode Pembelajaran</div>
                        <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Practice (Latihan) & Assessment
                            (Ujian)</p>
                    </div>
                    <div class="pt-4 sm:pt-0">
                        <div class="text-3xl font-extrabold font-heading sm:text-4xl text-bsds-navy">100%</div>
                        <div class="mt-1 text-xs font-semibold sm:text-sm text-bsds-teal">Jejak Keputusan Terekam</div>
                        <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Visual Decision Journey & Evaluasi
                            Refleksi</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FITUR UNGGULAN -->
        <section id="fitur" class="px-4 py-20 mx-auto lg:py-28 max-w-7xl sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto mb-16 text-center">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                    <x-heroicon-o-sparkles class="w-4 h-4 text-bsds-teal" />
                    Fitur Utama Simulator
                </span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                    Dirancang untuk Melatih Cara Berpikir Praktisi Layanan
                </h2>
                <p class="mt-3 text-base text-bsds-gray sm:text-lg">
                    Simulator tidak sekadar memberikan kuis benar atau salah. Anda dihadapkan pada konteks nyata, data
                    operasional, dan konsekuensi berkelanjutan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Investigasi Bukti & SOP</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Sebelum memilih opsi tindakan, periksa dokumen SOP, histori komplain, nota transaksi, dan stok
                        riil agar keputusan berbasis fakta konkret.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-teal">
                        <span>Mencegah keputusan asal tebak</span>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Skenario Bercabang Dinamis</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Setiap pilihan menghasilkan alur baru. Jika Anda mengambil langkah kurang tepat, sistem membuka
                        skenario remedial untuk menguji pemulihan layanan.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-teal">
                        <span>Realistis sesuai kondisi lapangan</span>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Sistem Hint Bertahap</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Menemui jalan buntu saat berlatih? Gunakan petunjuk bertingkat yang membimbing pola pikir
                        analitis tanpa langsung membocorkan jawaban final.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-orange">
                        <span>Bimbingan pemikiran kritis</span>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Visual Decision Journey</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Petakan kembali seluruh rantai keputusan yang telah Anda lalui dalam format timeline interaktif
                        untuk membandingkan rute pilihan Anda.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-teal">
                        <span>Audit & pemahaman alur keputusan</span>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Penilaian 4 Indikator</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Dapatkan feedback diagnostik per indikator: apakah Anda unggul dalam investigasi data atau perlu
                        latihan dalam menimbang alternatif opsi.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-teal">
                        <span>Laporan diagnostik terstruktur</span>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div
                    class="flex flex-col transition-all bg-white border rounded-2xl border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg group">

                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">Practice & Assessment Mode</h3>
                    <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                        Mode Practice untuk belajar bebas dengan petunjuk langsung, serta Mode Assessment untuk evaluasi
                        formal terstandar bagi instansi dan kelas.
                    </p>
                    <div
                        class="flex items-center gap-2 pt-4 mt-4 text-xs font-semibold border-t border-bsds-border/60 text-bsds-navy">
                        <span>Fleksibilitas evaluasi</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ALUR SIMULASI (HOW IT WORKS PIPELINE) -->
        <section id="alur" class="py-20 bg-white border-y border-bsds-border">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto mb-16 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                        <x-heroicon-o-play class="w-3.5 h-3.5 text-bsds-teal" />
                        Alur Pembelajaran
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                        Bagaimana Simulasi Dijalankan?
                    </h2>
                    <p class="mt-3 text-base text-bsds-gray sm:text-lg">
                        4 langkah sederhana yang mengasah insting profesional dalam menangani situasi bisnis nyata.
                    </p>
                </div>

                <div class="relative grid grid-cols-1 gap-6 md:grid-cols-4">
                    <!-- Step 1 -->
                    <div
                        class="relative flex flex-col p-6 transition border bg-bsds-bg rounded-2xl border-bsds-border group hover:border-bsds-teal">
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-xl bg-bsds-navy font-heading">
                                01
                            </span>
                            <x-heroicon-o-book-open
                                class="w-6 h-6 transition-colors text-bsds-navy/40 group-hover:text-bsds-teal" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Read & Briefing</h3>
                        <p class="text-sm leading-relaxed text-bsds-gray">
                            Pelajari peran kerja, latar belakang masalah, dan situasi mendesak yang dihadapi pelanggan.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div
                        class="relative flex flex-col p-6 transition border bg-bsds-bg rounded-2xl border-bsds-border group hover:border-bsds-teal">
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-xl bg-bsds-teal font-heading">
                                02
                            </span>
                            <x-heroicon-o-magnifying-glass
                                class="w-6 h-6 transition-colors text-bsds-teal/40 group-hover:text-bsds-teal" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Investigate SOP</h3>
                        <p class="text-sm leading-relaxed text-bsds-gray">
                            Buka tab investigasi untuk memverifikasi SOP, log pesanan, dan batas kewenangan posisi Anda.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div
                        class="relative flex flex-col p-6 transition border bg-bsds-bg rounded-2xl border-bsds-border group hover:border-bsds-teal">
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-xl bg-bsds-orange font-heading">
                                03
                            </span>
                            <x-heroicon-o-arrows-pointing-out
                                class="w-6 h-6 transition-colors text-bsds-orange/40 group-hover:text-bsds-orange" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Decide & Adapt</h3>
                        <p class="text-sm leading-relaxed text-bsds-gray">
                            Tentukan alternatif terbaik dan hadapi konsekuensi langsung pada skenario berikutnya.
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div
                        class="relative flex flex-col p-6 transition border bg-bsds-bg rounded-2xl border-bsds-border group hover:border-bsds-teal">
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="flex items-center justify-center w-10 h-10 text-sm font-bold text-white rounded-xl bg-emerald-600 font-heading">
                                04
                            </span>
                            <x-heroicon-o-chart-bar
                                class="w-6 h-6 transition-colors text-emerald-600/40 group-hover:text-emerald-600" />
                        </div>
                        <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Reflect & Score</h3>
                        <p class="text-sm leading-relaxed text-bsds-gray">
                            Telusuri visual decision journey dan terima evaluasi skor performa berdasarkan 4 indikator
                            utama.
                        </p>
                    </div>
                </div>

                <!-- Flow Highlight Box -->
                <div
                    class="flex flex-col items-center justify-between gap-6 p-6 mt-12 border bg-bsds-bg rounded-2xl border-bsds-border sm:p-8 sm:flex-row">
                    <div class="flex items-start gap-4">
                        <div class="p-3 rounded-xl bg-bsds-teal/10 text-bsds-teal shrink-0">
                            <x-heroicon-o-light-bulb class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="text-base font-bold font-heading text-bsds-navy">Fokus pada Proses Analisis,
                                Bukan Sekadar Jawaban Akhir</h4>
                            <p class="mt-1 text-sm text-bsds-gray">
                                Di dunia pelayanan bisnis, keputusan terbaik lahir dari pertimbangan yang matang antara
                                kepuasan pelanggan, batas SOP, dan keberlanjutan operasional.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('guide') }}"
                        class="shrink-0 px-5 py-2.5 rounded-xl bg-white border border-bsds-border hover:border-bsds-teal text-bsds-navy text-sm font-semibold transition">
                        Baca Buku Panduan &rarr;
                    </a>
                </div>
            </div>
        </section>

        <!-- SKENARIO KASUS (SHOWCASE STUDI KASUS) -->
        <section id="skenario" class="px-4 py-20 mx-auto lg:py-28 max-w-7xl sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end mb-14">
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-navy bg-bsds-navy/10">
                        <x-heroicon-o-puzzle-piece class="w-4 h-4 text-bsds-navy" />
                        Studi Kasus Nyata
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                        Skenario Siap Uji dari Kasus Bisnis Aktual
                    </h2>
                    <p class="max-w-xl mt-2 text-base text-bsds-gray">
                        Didesain dengan tingkat kesulitan bertahap untuk membangun kapabilitas problem solving secara
                        terarah.
                    </p>
                </div>

                @auth
                    <a href="{{ route('simulations.index') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition shadow-xs rounded-xl bg-bsds-navy hover:bg-bsds-teal">
                        <span>Jelajahi Semua Kasus</span>
                        <x-heroicon-o-arrow-right class="w-4 h-4" />
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition shadow-xs rounded-xl bg-bsds-navy hover:bg-bsds-teal">
                        <span>Masuk untuk Mulai Kasus</span>
                        <x-heroicon-o-arrow-right class="w-4 h-4" />
                    </a>
                @endauth
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Case Card 1 -->
                <div
                    class="flex flex-col p-6 transition bg-white border rounded-2xl border-bsds-border hover:shadow-md hover:border-bsds-teal/50">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-01</span>
                        <span
                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-teal-100 text-bsds-teal">Foundation</span>
                    </div>
                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">
                        Customer Complaint Handling
                    </h3>
                    <p class="flex-1 mb-6 text-sm leading-relaxed text-bsds-gray">
                        Tangani komplain pelanggan VIP yang menerima produk salah saat promo flash sale, di tengah
                        keterbatasan stok gudang dan tekanan waktu keberangkatan.
                    </p>
                    <div
                        class="flex items-center justify-between pt-4 text-xs border-t border-bsds-border/70 text-bsds-gray">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-briefcase class="w-4 h-4 text-bsds-teal" /> CS Officer
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-clock class="w-4 h-4 text-bsds-orange" /> 15 - 20 Mnt
                        </span>
                    </div>
                </div>

                <!-- Case Card 2 -->
                <div
                    class="flex flex-col p-6 transition bg-white border rounded-2xl border-bsds-border hover:shadow-md hover:border-bsds-teal/50">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-02</span>
                        <span
                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Developing</span>
                    </div>
                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">
                        Service Return & Disputed Policy
                    </h3>
                    <p class="flex-1 mb-6 text-sm leading-relaxed text-bsds-gray">
                        Hadapi perselisihan kebijakan pengembalian barang rusak tanpa bukti unboxing awal,
                        menyeimbangkan kepuasan konsumen vs proteksi aset toko.
                    </p>
                    <div
                        class="flex items-center justify-between pt-4 text-xs border-t border-bsds-border/70 text-bsds-gray">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-briefcase class="w-4 h-4 text-bsds-teal" /> Service Lead
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-clock class="w-4 h-4 text-bsds-orange" /> 20 - 25 Mnt
                        </span>
                    </div>
                </div>

                <!-- Case Card 3 -->
                <div
                    class="flex flex-col p-6 transition bg-white border rounded-2xl border-bsds-border hover:shadow-md hover:border-bsds-teal/50">
                    <div class="flex items-center justify-between mb-4">
                        <span
                            class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-03</span>
                        <span
                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">Challenge</span>
                    </div>
                    <h3 class="mb-2 text-lg font-bold font-heading text-bsds-navy">
                        Critical Escalation Management
                    </h3>
                    <p class="flex-1 mb-6 text-sm leading-relaxed text-bsds-gray">
                        Selesaikan insiden viral di media sosial dengan koordinasi lintas divisi: Public Relations,
                        Legal, dan Operational Service Manager.
                    </p>
                    <div
                        class="flex items-center justify-between pt-4 text-xs border-t border-bsds-border/70 text-bsds-gray">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-briefcase class="w-4 h-4 text-bsds-teal" /> Branch Manager
                        </span>
                        <span class="inline-flex items-center gap-1 font-medium">
                            <x-heroicon-o-clock class="w-4 h-4 text-bsds-orange" /> 25 - 30 Mnt
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- INDIKATOR KOMPETENSI SECTION -->
        <section id="indikator" class="py-20 bg-white border-y border-bsds-border">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto mb-16 text-center">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                        <x-heroicon-o-chart-bar class="w-3.5 h-3.5 text-bsds-teal" />
                        Matriks Penilaian Terstandar
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                        4 Indikator Pengambilan Keputusan
                    </h2>
                    <p class="mt-3 text-base text-bsds-gray sm:text-lg">
                        Setiap opsi keputusan diberi bobot penilaian objektif untuk memetakan kekuatan dan aspek
                        perbaikan Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Indikator 1 -->
                    <div class="flex flex-col p-6 border bg-bsds-bg rounded-2xl border-bsds-border">
                        <div class="flex items-center gap-2 mb-3">
                            <span
                                class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-navy text-white">IDN</span>
                            <h3 class="text-base font-bold font-heading text-bsds-navy">Identifikasi Masalah</h3>
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                            Kemampuan mendeteksi akar penyebab masalah utama dari komplain atau dilema pelanggan sebelum
                            terburu-buru mengambil tindakan.
                        </p>
                    </div>

                    <!-- Indikator 2 -->
                    <div class="flex flex-col p-6 border bg-bsds-bg rounded-2xl border-bsds-border">
                        <div class="flex items-center gap-2 mb-3">
                            <span
                                class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-teal text-white">INF</span>
                            <h3 class="text-base font-bold font-heading text-bsds-navy">Pengumpulan Informasi</h3>
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                            Ketepatan memanfaatkan data pendukung seperti SOP layanan, data stok produk, riwayat
                            transaksi, dan konsultasi supervisor.
                        </p>
                    </div>

                    <!-- Indikator 3 -->
                    <div class="flex flex-col p-6 border bg-bsds-bg rounded-2xl border-bsds-border">
                        <div class="flex items-center gap-2 mb-3">
                            <span
                                class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-orange text-white">ALT</span>
                            <h3 class="text-base font-bold font-heading text-bsds-navy">Pertimbangan Opsi</h3>
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                            Kemampuan menimbang trade-off, risiko kerugian, dan dampak jangka panjang antara berbagai
                            solusi alternatif yang ada.
                        </p>
                    </div>

                    <!-- Indikator 4 -->
                    <div class="flex flex-col p-6 border bg-bsds-bg rounded-2xl border-bsds-border">
                        <div class="flex items-center gap-2 mb-3">
                            <span
                                class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-emerald-600 text-white">ACT</span>
                            <h3 class="text-base font-bold font-heading text-bsds-navy">Pemilihan Tindakan</h3>
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-bsds-gray">
                            Ketepatan dan keberanian mengeksekusi langkah terbaik yang sejalan dengan etika bisnis serta
                            prosedur standar pelayanan.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ SECTION (ALPINE ACCORDION) -->
        <section id="faq" class="max-w-4xl px-4 py-20 mx-auto lg:py-28 sm:px-6 lg:px-8"
            x-data="{ activeAccordion: null }">
            <div class="text-center mb-14">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                    <x-heroicon-o-question-mark-circle class="w-4 h-4 text-bsds-teal" />
                    Pertanyaan Umum
                </span>
                <h2 class="mt-4 text-2xl font-bold tracking-tight font-heading sm:text-4xl text-bsds-navy">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="mt-2 text-base text-bsds-gray">
                    Semua yang perlu Anda ketahui mengenai platform simulasi BSDS.
                </p>
            </div>

            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="overflow-hidden transition bg-white border rounded-2xl border-bsds-border">
                    <button type="button" @click="activeAccordion = activeAccordion === 1 ? null : 1"
                        class="flex items-center justify-between w-full gap-4 px-6 py-5 text-base font-semibold text-left transition-colors font-heading text-bsds-navy hover:text-bsds-teal">
                        <span>Siapa yang cocok menggunakan simulator ini?</span>
                        <span class="transition-transform duration-200 shrink-0"
                            x-bind:class="activeAccordion === 1 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                            <x-heroicon-o-chevron-down class="w-5 h-5" />
                        </span>
                    </button>
                    <div x-show="activeAccordion === 1" x-collapse x-cloak
                        class="px-6 pt-4 pb-5 text-sm leading-relaxed border-t text-bsds-gray border-bsds-border/60">
                        Simulator ini dirancang khusus untuk mahasiswa bisnis/manajemen, siswa kejuruan, praktisi
                        customer service, serta tim operasional yang ingin mengasah keterampilan problem solving dan
                        pengambilan keputusan dalam situasi layanan pelanggan.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="overflow-hidden transition bg-white border rounded-2xl border-bsds-border">
                    <button type="button" @click="activeAccordion = activeAccordion === 2 ? null : 2"
                        class="flex items-center justify-between w-full gap-4 px-6 py-5 text-base font-semibold text-left transition-colors font-heading text-bsds-navy hover:text-bsds-teal">
                        <span>Apa perbedaan antara Practice Mode dan Assessment Mode?</span>
                        <span class="transition-transform duration-200 shrink-0"
                            x-bind:class="activeAccordion === 2 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                            <x-heroicon-o-chevron-down class="w-5 h-5" />
                        </span>
                    </button>
                    <div x-show="activeAccordion === 2" x-collapse x-cloak
                        class="px-6 pt-4 pb-5 text-sm leading-relaxed border-t text-bsds-gray border-bsds-border/60">
                        Pada <strong>Practice Mode</strong>, Anda bebas mencoba opsi tanpa tekanan, dapat mengakses hint
                        bertahap, serta melihat langsung penjelasan dampak setiap keputusan. Pada <strong>Assessment
                            Mode</strong>, simulasi berfungsi sebagai ujian evaluasi resmi tanpa petunjuk bantuan, di
                        mana skor akhir Anda langsung direkam dalam laporan capaian kelas.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="overflow-hidden transition bg-white border rounded-2xl border-bsds-border">
                    <button type="button" @click="activeAccordion = activeAccordion === 3 ? null : 3"
                        class="flex items-center justify-between w-full gap-4 px-6 py-5 text-base font-semibold text-left transition-colors font-heading text-bsds-navy hover:text-bsds-teal">
                        <span>Bagaimana jika saya memilih tindakan yang keliru?</span>
                        <span class="transition-transform duration-200 shrink-0"
                            x-bind:class="activeAccordion === 3 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                            <x-heroicon-o-chevron-down class="w-5 h-5" />
                        </span>
                    </button>
                    <div x-show="activeAccordion === 3" x-collapse x-cloak
                        class="px-6 pt-4 pb-5 text-sm leading-relaxed border-t text-bsds-gray border-bsds-border/60">
                        Sistem akan secara dinamis mengarahkan Anda ke skenario lanjutan atau alur remedial sesuai
                        dampak keputusan tersebut. Anda akan melihat langsung reaksi pelanggan dan belajar bagaimana
                        cara memulihkan situasi (service recovery) secara konstruktif.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="overflow-hidden transition bg-white border rounded-2xl border-bsds-border">
                    <button type="button" @click="activeAccordion = activeAccordion === 4 ? null : 4"
                        class="flex items-center justify-between w-full gap-4 px-6 py-5 text-base font-semibold text-left transition-colors font-heading text-bsds-navy hover:text-bsds-teal">
                        <span>Apakah progres simulasi dapat dilanjutkan jika keluar di tengah jalan?</span>
                        <span class="transition-transform duration-200 shrink-0"
                            x-bind:class="activeAccordion === 4 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                            <x-heroicon-o-chevron-down class="w-5 h-5" />
                        </span>
                    </button>
                    <div x-show="activeAccordion === 4" x-collapse x-cloak
                        class="px-6 pt-4 pb-5 text-sm leading-relaxed border-t text-bsds-gray border-bsds-border/60">
                        Ya, progres titik skenario Anda tersimpan otomatis di server. Saat masuk kembali ke dashboard,
                        Anda cukup menekan tombol "Lanjutkan Simulasi" untuk meneruskan dari skenario terakhir.
                    </div>
                </div>
            </div>
        </section>

        <!-- CALL TO ACTION BANNER -->
        <section class="px-4 pb-20 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div
                class="relative rounded-3xl bg-gradient-to-br from-bsds-navy via-[#1b3b5b] to-[#14283d] text-white p-8 sm:p-14 overflow-hidden shadow-2xl">
                <!-- Accent Circles -->
                <div
                    class="absolute rounded-full pointer-events-none -top-24 -right-24 w-80 h-80 bg-bsds-teal/20 blur-2xl">
                </div>
                <div
                    class="absolute rounded-full pointer-events-none -bottom-24 -left-24 w-80 h-80 bg-bsds-orange/20 blur-2xl">
                </div>

                <div class="relative z-10 max-w-3xl">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-orange bg-white/10">
                        Mulai Pengalaman Belajar
                    </span>
                    <h2
                        class="mt-4 text-2xl font-extrabold leading-tight tracking-tight font-heading sm:text-4xl lg:text-5xl">
                        Siap Mengambil Keputusan Terbaik Hari Ini?
                    </h2>
                    <p class="max-w-2xl mt-4 text-base leading-relaxed text-white/80 sm:text-lg">
                        Bergabunglah bersama ratusan pembelajar lainnya. Jelajahi skenario, uji hipotesis penanganan,
                        dan raih wawasan kepemimpinan layanan bisnis.
                    </p>

                    <div class="flex flex-col items-center gap-4 mt-8 sm:flex-row">
                        <a href="{{ Auth::check() ? route('dashboard') : (Route::has('register') ? route('register') : route('login')) }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-bsds-teal hover:bg-teal-600 text-white font-semibold text-base shadow-lg transition">
                            <x-heroicon-o-play class="w-5 h-5 text-bsds-orange" />
                            <span>Mulai Sekarang Tanpa Biaya</span>
                        </a>

                        <a href="{{ route('guide') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/20 transition">
                            <x-heroicon-o-book-open class="w-5 h-5" />
                            <span>Baca Petunjuk Lengkap</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="text-sm bg-white border-t border-bsds-border text-bsds-gray">
        <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 mb-10 md:grid-cols-4">
                <!-- Brand Column -->
                <div class="space-y-4 md:col-span-2">
                    <div class="flex items-center gap-2">
                        <x-application-logo class="w-auto h-8 text-bsds-navy" />
                        <span class="text-lg font-bold font-heading text-bsds-navy">BSDS</span>
                    </div>
                    <p class="max-w-sm text-xs leading-relaxed sm:text-sm text-bsds-gray">
                        Business Service Decision Simulator &mdash; media pembelajaran interaktif berbasis web untuk
                        melatih keterampilan pengambilan keputusan dalam situasi pelayanan bisnis realistis.
                    </p>
                    <div class="font-mono text-xs text-bsds-gray">
                        Decide &bull; Experience &bull; Learn &bull; Reflect
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="mb-3 text-xs font-bold tracking-wider uppercase font-heading text-bsds-navy">Navigasi
                        Utama</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="#fitur" class="transition-colors hover:text-bsds-teal">Fitur Unggulan</a></li>
                        <li><a href="#alur" class="transition-colors hover:text-bsds-teal">Alur Simulasi</a></li>
                        <li><a href="#skenario" class="transition-colors hover:text-bsds-teal">Skenario Kasus</a></li>
                        <li><a href="#indikator" class="transition-colors hover:text-bsds-teal">Indikator
                                Penilaian</a></li>
                        <li><a href="{{ route('guide') }}" class="transition-colors hover:text-bsds-teal">Buku
                                Panduan</a></li>
                    </ul>
                </div>

                <!-- Account & Platform -->
                <div>
                    <h4 class="mb-3 text-xs font-bold tracking-wider uppercase font-heading text-bsds-navy">Akses
                        Platform</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        @auth
                            <li><a href="{{ route('dashboard') }}"
                                    class="transition-colors hover:text-bsds-teal">Dashboard Siswa</a></li>
                            <li><a href="{{ route('simulations.index') }}"
                                    class="transition-colors hover:text-bsds-teal">Daftar Skenario</a></li>
                            <li><a href="{{ route('progress') }}" class="transition-colors hover:text-bsds-teal">Progres
                                    Belajar</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition-colors hover:text-bsds-teal">Masuk
                                    Akun</a></li>
                            @if (Route::has('register'))
                                <li><a href="{{ route('register') }}"
                                        class="transition-colors hover:text-bsds-teal">Pendaftaran Akun Baru</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col items-center justify-between gap-4 pt-8 text-xs border-t border-bsds-border sm:flex-row text-bsds-gray">
                <p>&copy; {{ date('Y') }} Business Service Decision Simulator. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-bsds-bg border border-bsds-border font-mono text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>v{{ app()->version() }}</span>
                    </span>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
