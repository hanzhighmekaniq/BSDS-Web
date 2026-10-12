<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Business Service Decision Simulator') }} &bull; Media Pembelajaran Interaktif</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-bsds-ink antialiased bg-bsds-bg selection:bg-bsds-teal/20 selection:text-bsds-navy overflow-x-hidden">

        <!-- Sticky Header with Backdrop Blur -->
        <header x-data="{ mobileMenuOpen: false, scrolled: false }"
                @scroll.window="scrolled = (window.pageYOffset > 10)"
                x-bind:class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm border-b border-bsds-border/80' : 'bg-white border-b border-bsds-border'"
                class="sticky top-0 z-50 transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <!-- Brand Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div class="p-2 rounded-xl bg-bsds-navy/5 group-hover:bg-bsds-teal/10 transition-colors">
                        <x-application-logo class="h-8 w-auto text-bsds-navy group-hover:text-bsds-teal transition-colors" />
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-heading font-bold text-xl text-bsds-navy tracking-tight">BSDS</span>
                            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-bsds-teal/15 text-bsds-teal">
                                Simulator
                            </span>
                        </div>
                        <span class="hidden md:block text-[11px] text-bsds-gray -mt-0.5">Business Service Decision</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-bsds-gray">
                    <a href="#fitur" class="hover:text-bsds-navy transition-colors">Fitur Unggulan</a>
                    <a href="#alur" class="hover:text-bsds-navy transition-colors">Alur Simulasi</a>
                    <a href="#skenario" class="hover:text-bsds-navy transition-colors">Skenario Kasus</a>
                    <a href="#indikator" class="hover:text-bsds-navy transition-colors">Indikator</a>
                    <a href="#faq" class="hover:text-bsds-navy transition-colors">FAQ</a>
                    <a href="{{ route('guide') }}" class="hover:text-bsds-teal text-bsds-teal/90 transition-colors inline-flex items-center gap-1 font-semibold">
                        <x-heroicon-o-book-open class="w-4 h-4" />
                        Panduan
                    </a>
                </nav>

                <!-- Auth Buttons -->
                <div class="hidden sm:flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                            <x-heroicon-o-play class="w-4 h-4 text-bsds-orange" />
                            <span>Buka Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-sm font-semibold text-bsds-navy hover:text-bsds-teal transition-colors">
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
                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 rounded-xl text-bsds-navy hover:bg-bsds-bg focus:outline-none"
                        aria-label="Toggle navigation menu">
                    <x-heroicon-o-bars-3 x-show="!mobileMenuOpen" class="w-6 h-6" />
                    <x-heroicon-o-x-mark x-show="mobileMenuOpen" class="w-6 h-6" x-cloak />
                </button>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileMenuOpen"
                 x-collapse
                 x-cloak
                 class="lg:hidden bg-white border-b border-bsds-border px-4 pt-3 pb-6 space-y-4">
                <nav class="flex flex-col space-y-2 text-sm font-medium text-bsds-gray">
                    <a @click="mobileMenuOpen = false" href="#fitur" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Fitur Unggulan</a>
                    <a @click="mobileMenuOpen = false" href="#alur" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Alur Simulasi</a>
                    <a @click="mobileMenuOpen = false" href="#skenario" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Skenario Kasus</a>
                    <a @click="mobileMenuOpen = false" href="#indikator" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">Indikator Kompetensi</a>
                    <a @click="mobileMenuOpen = false" href="#faq" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-ink">FAQ</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('guide') }}" class="px-3 py-2 rounded-lg hover:bg-bsds-bg text-bsds-teal font-semibold">Panduan Lengkap</a>
                </nav>
                <div class="pt-4 border-t border-bsds-border flex flex-col gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-bsds-navy text-white text-sm font-semibold">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl border border-bsds-border text-bsds-navy text-sm font-semibold hover:bg-bsds-bg">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-bsds-navy text-white text-sm font-semibold">
                                Daftar Akun
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative">
            <!-- Background Ambient Glow & Patterns -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[680px] pointer-events-none overflow-hidden -z-10">
                <div class="absolute top-10 left-10 w-96 h-96 bg-bsds-teal/10 rounded-full blur-3xl"></div>
                <div class="absolute top-36 right-10 w-96 h-96 bg-bsds-orange/10 rounded-full blur-3xl"></div>
                <div class="absolute top-80 left-1/3 w-80 h-80 bg-bsds-navy/5 rounded-full blur-3xl"></div>
            </div>

            <!-- HERO SECTION -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 pb-16 lg:pb-24">
                <div class="text-center max-w-3xl mx-auto">
                    <!-- Eyebrow Tag -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-bsds-border shadow-xs text-xs font-semibold text-bsds-teal">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-bsds-teal opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-bsds-teal"></span>
                        </span>
                        <span>Platform Simulasi Pengambilan Keputusan Bisnis</span>
                        <x-heroicon-o-sparkles class="w-3.5 h-3.5 text-bsds-orange" />
                    </div>

                    <!-- Main Headline -->
                    <h1 class="font-heading font-extrabold text-3xl sm:text-5xl lg:text-6xl text-bsds-navy tracking-tight leading-[1.15] mt-6">
                        Latih Refleks Keputusan Layanan Bisnis <span class="text-transparent bg-clip-text bg-gradient-to-r from-bsds-teal to-bsds-navy">Secara Nyata</span>
                    </h1>

                    <!-- Slogan & Description -->
                    <p class="font-heading text-bsds-teal font-semibold text-base sm:text-lg tracking-wide mt-4">
                        Decide. Experience. Learn.
                    </p>

                    <p class="text-bsds-gray sm:text-lg max-w-2xl mx-auto mt-3 leading-relaxed">
                        Hadapi skenario komplain, dilema SOP, dan krisis operasional dalam simulasi bercabang adaptif. Setiap pilihan memicu konsekuensi yang membentuk profil performa Anda.
                    </p>

                    <!-- Hero Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 mt-8">
                        <a href="{{ Auth::check() ? route('dashboard') : (Route::has('register') ? route('register') : route('login')) }}"
                           class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-2.5 px-8 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold text-base shadow-md hover:shadow-lg transition group">
                            <x-heroicon-o-play class="w-5 h-5 text-bsds-orange group-hover:scale-110 transition-transform" />
                            <span>Mulai Simulasi Sekarang</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" />
                        </a>

                        <a href="#alur"
                           class="w-full sm:w-auto min-h-[52px] inline-flex items-center justify-center gap-2 px-6 rounded-xl bg-white hover:bg-bsds-bg border border-bsds-border text-bsds-navy font-semibold text-base transition">
                            <x-heroicon-o-academic-cap class="w-5 h-5 text-bsds-gray" />
                            <span>Pelajari Alur Kerja</span>
                        </a>
                    </div>

                    <!-- Key Value Badges -->
                    <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8 mt-10 text-xs sm:text-sm font-medium text-bsds-gray">
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
                <div class="mt-14 lg:mt-18 max-w-5xl mx-auto relative">
                    <!-- Glow Behind Mockup -->
                    <div class="absolute -inset-1.5 bg-gradient-to-r from-bsds-navy/20 via-bsds-teal/20 to-bsds-orange/20 rounded-3xl blur-xl opacity-70"></div>

                    <!-- Main App Card -->
                    <div class="relative bg-white rounded-2xl sm:rounded-3xl border border-bsds-border shadow-2xl overflow-hidden">
                        <!-- Top Window Bar -->
                        <div class="bg-bsds-navy px-4 sm:px-6 py-3.5 flex items-center justify-between border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-red-400"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                                <span class="ml-3 text-xs font-mono text-white/70 hidden sm:inline-block">BSDS Simulator Workspace &bull; Skenario Interaktif</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-bsds-teal/20 text-teal-300 border border-teal-400/30">
                                    Practice Mode
                                </span>
                                <span class="text-xs text-white/60 font-mono hidden sm:inline">ID: CASE-01-S2</span>
                            </div>
                        </div>

                        <!-- Mockup Content Grid -->
                        <div class="p-5 sm:p-8 bg-bsds-bg/60">
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Left 2 Cols: Simulation Scenario Context & Choices -->
                                <div class="lg:col-span-2 space-y-5">
                                    <!-- Scenario Header Card -->
                                    <div class="bg-white p-5 rounded-2xl border border-bsds-border shadow-xs">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase bg-bsds-navy text-white">CASE-01</span>
                                                <span class="text-xs font-semibold text-bsds-gray">Titik Keputusan #02</span>
                                            </div>
                                            <span class="inline-flex items-center gap-1 text-xs text-bsds-orange font-semibold">
                                                <x-heroicon-o-clock class="w-3.5 h-3.5" />
                                                Est. 15 Mnt
                                            </span>
                                        </div>
                                        <h3 class="font-heading font-bold text-base sm:text-lg text-bsds-navy">
                                            Pelanggan VIP Mengeluhkan Barang Salah Kirim saat Promo
                                        </h3>
                                        <p class="text-xs sm:text-sm text-bsds-gray mt-2 leading-relaxed">
                                            Ibu Rani (Anggota Platinum) menerima varian produk yang keliru. Stok produk pengganti di sistem toko menunjukkan angka nol, sementara pelanggan meminta solusi dalam waktu 1 jam sebelum keberangkatan luar negeri.
                                        </p>

                                        <!-- Investigation Evidence Tabs -->
                                        <div class="mt-4 pt-3 border-t border-bsds-border/70 flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-medium text-bsds-gray">Data Tersedia:</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-bsds-bg border border-bsds-border text-bsds-navy font-medium">
                                                <x-heroicon-o-document-text class="w-3.5 h-3.5 text-bsds-teal" /> SOP Retur Garansi
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-bsds-bg border border-bsds-border text-bsds-navy font-medium">
                                                <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-bsds-teal" /> Audit Stok Cabang
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Option Selection Simulator -->
                                    <div class="space-y-3">
                                        <div class="text-xs font-bold uppercase tracking-wider text-bsds-gray">
                                            Pilihan Tindakan Keputusan:
                                        </div>

                                        <!-- Option 1 -->
                                        <div class="bg-white p-4 rounded-xl border border-bsds-border hover:border-bsds-teal/50 hover:shadow-xs transition flex items-start gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-bsds-bg text-bsds-navy font-heading font-bold text-xs flex items-center justify-center shrink-0 border border-bsds-border">A</span>
                                            <div class="flex-1">
                                                <div class="text-xs sm:text-sm font-medium text-bsds-ink">Tolak permintaan penggantian kilat karena stok sistem habis sesuai aturan SOP umum.</div>
                                                <div class="text-[11px] text-bsds-error mt-1 flex items-center gap-1 font-medium">
                                                    <span>Konsekuensi: Risiko eskalasi emosi dan kehilangan loyalitas pelanggan VIP</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Option 2 (Highlighted) -->
                                        <div class="bg-teal-50/70 p-4 rounded-xl border-2 border-bsds-teal shadow-xs flex items-start gap-3 relative">
                                            <span class="w-6 h-6 rounded-lg bg-bsds-teal text-white font-heading font-bold text-xs flex items-center justify-center shrink-0">B</span>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-xs sm:text-sm font-semibold text-bsds-navy">Verifikasi gudang transit cabang terdekat & tawarkan kurir instan prioritas.</div>
                                                    <span class="text-[10px] uppercase font-bold text-bsds-teal bg-white px-2 py-0.5 rounded-full border border-bsds-teal/20 hidden sm:inline-block">Pilihan Terbaik</span>
                                                </div>
                                                <div class="text-[11px] text-bsds-teal font-medium mt-1 flex items-center gap-1">
                                                    <x-heroicon-o-check-circle class="w-3.5 h-3.5 shrink-0" />
                                                    <span>Konsekuensi positif: Menjaga SLA kepuasan pelanggan serta memanfaatkan stok transit</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Option 3 -->
                                        <div class="bg-white p-4 rounded-xl border border-bsds-border hover:border-bsds-teal/50 transition flex items-start gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-bsds-bg text-bsds-navy font-heading font-bold text-xs flex items-center justify-center shrink-0 border border-bsds-border">C</span>
                                            <div class="flex-1">
                                                <div class="text-xs sm:text-sm font-medium text-bsds-ink">Alihkan seluruh penanganan ke supervisor tanpa investigasi awal mandiri.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right 1 Col: Live Metrics & Feedback Sidebar -->
                                <div class="space-y-4">
                                    <!-- Indicator Progress Widget -->
                                    <div class="bg-white p-5 rounded-2xl border border-bsds-border shadow-xs">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-bsds-navy">Profil Indikator</h4>
                                            <span class="text-[11px] font-semibold text-bsds-teal">Live Analysis</span>
                                        </div>

                                        <div class="space-y-3">
                                            <div>
                                                <div class="flex justify-between text-xs mb-1 font-medium">
                                                    <span class="text-bsds-ink">Identifikasi Masalah</span>
                                                    <span class="text-bsds-navy font-semibold">88%</span>
                                                </div>
                                                <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                    <div class="h-full bg-bsds-teal rounded-full" style="width: 88%"></div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex justify-between text-xs mb-1 font-medium">
                                                    <span class="text-bsds-ink">Pengumpulan Info (SOP)</span>
                                                    <span class="text-bsds-navy font-semibold">92%</span>
                                                </div>
                                                <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                    <div class="h-full bg-bsds-teal rounded-full" style="width: 92%"></div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex justify-between text-xs mb-1 font-medium">
                                                    <span class="text-bsds-ink">Pertimbangan Alternatif</span>
                                                    <span class="text-bsds-navy font-semibold">80%</span>
                                                </div>
                                                <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                    <div class="h-full bg-bsds-orange rounded-full" style="width: 80%"></div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="flex justify-between text-xs mb-1 font-medium">
                                                    <span class="text-bsds-ink">Ketepatan Tindakan</span>
                                                    <span class="text-bsds-navy font-semibold">95%</span>
                                                </div>
                                                <div class="h-1.5 bg-bsds-bg rounded-full overflow-hidden">
                                                    <div class="h-full bg-bsds-success rounded-full" style="width: 95%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Decision Journey Widget -->
                                    <div class="bg-gradient-to-br from-bsds-navy to-[#1f4265] text-white p-5 rounded-2xl shadow-xs">
                                        <div class="flex items-center gap-2 mb-2 text-bsds-orange">
                                            <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                                            <span class="text-xs font-bold uppercase tracking-wider">Decision Tree</span>
                                        </div>
                                        <p class="text-xs text-white/80 leading-relaxed">
                                            Setiap keputusan membuka skenario kelanjutan baru. Analisis rute perjalanan Anda setelah kasus selesai!
                                        </p>
                                        <div class="mt-4 flex items-center gap-2">
                                            <span class="px-2 py-1 rounded-md bg-white/10 text-[10px] font-mono text-emerald-300">S1: Optimal</span>
                                            <span class="text-white/40">&rarr;</span>
                                            <span class="px-2 py-1 rounded-md bg-white/20 text-[10px] font-mono text-yellow-300">S2: Aktif</span>
                                            <span class="text-white/40">&rarr;</span>
                                            <span class="px-2 py-1 rounded-md bg-white/5 text-[10px] font-mono text-white/40">S3: Next</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- STATS COUNTER STRIP -->
            <section class="border-y border-bsds-border bg-white py-10 sm:py-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8 text-center divide-y sm:divide-y-0 sm:divide-x divide-bsds-border/60">
                        <div class="pt-4 sm:pt-0">
                            <div class="font-heading font-extrabold text-3xl sm:text-4xl text-bsds-navy">4</div>
                            <div class="text-xs sm:text-sm font-semibold text-bsds-teal mt-1">Indikator Kompetensi</div>
                            <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Identifikasi, Informasi, Alternatif, Tindakan</p>
                        </div>
                        <div class="pt-4 sm:pt-0">
                            <div class="font-heading font-extrabold text-3xl sm:text-4xl text-bsds-navy">3</div>
                            <div class="text-xs sm:text-sm font-semibold text-bsds-teal mt-1">Level Kasus Realistis</div>
                            <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Foundation, Developing, & Challenge</p>
                        </div>
                        <div class="pt-4 sm:pt-0">
                            <div class="font-heading font-extrabold text-3xl sm:text-4xl text-bsds-navy">2</div>
                            <div class="text-xs sm:text-sm font-semibold text-bsds-teal mt-1">Mode Pembelajaran</div>
                            <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Practice (Latihan) & Assessment (Ujian)</p>
                        </div>
                        <div class="pt-4 sm:pt-0">
                            <div class="font-heading font-extrabold text-3xl sm:text-4xl text-bsds-navy">100%</div>
                            <div class="text-xs sm:text-sm font-semibold text-bsds-teal mt-1">Jejak Keputusan Terekam</div>
                            <p class="text-xs text-bsds-gray mt-1 max-w-[200px] mx-auto">Visual Decision Journey & Evaluasi Refleksi</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FITUR UNGGULAN -->
            <section id="fitur" class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                        <x-heroicon-o-sparkles class="w-4 h-4 text-bsds-teal" />
                        Fitur Utama Simulator
                    </span>
                    <h2 class="font-heading font-bold text-2xl sm:text-4xl text-bsds-navy mt-4 tracking-tight">
                        Dirancang untuk Melatih Cara Berpikir Praktisi Layanan
                    </h2>
                    <p class="text-bsds-gray text-base sm:text-lg mt-3">
                        Simulator tidak sekadar memberikan kuis benar atau salah. Anda dihadapkan pada konteks nyata, data operasional, dan konsekuensi berkelanjutan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-navy/10 text-bsds-navy group-hover:bg-bsds-navy group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-magnifying-glass class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Investigasi Bukti & SOP</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Sebelum memilih opsi tindakan, periksa dokumen SOP, histori komplain, nota transaksi, dan stok riil agar keputusan berbasis fakta konkret.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-teal">
                            <span>Mencegah keputusan asal tebak</span>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-teal/10 text-bsds-teal group-hover:bg-bsds-teal group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-arrows-pointing-out class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Skenario Bercabang Dinamis</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Setiap pilihan menghasilkan alur baru. Jika Anda mengambil langkah kurang tepat, sistem membuka skenario remedial untuk menguji pemulihan layanan.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-teal">
                            <span>Realistis sesuai kondisi lapangan</span>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-orange/15 text-bsds-orange group-hover:bg-bsds-orange group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-light-bulb class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Sistem Hint Bertahap</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Menemui jalan buntu saat berlatih? Gunakan petunjuk bertingkat yang membimbing pola pikir analitis tanpa langsung membocorkan jawaban final.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-orange">
                            <span>Bimbingan pemikiran kritis</span>
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-navy/10 text-bsds-navy group-hover:bg-bsds-navy group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-rectangle-stack class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Visual Decision Journey</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Petakan kembali seluruh rantai keputusan yang telah Anda lalui dalam format timeline interaktif untuk membandingkan rute pilihan Anda.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-teal">
                            <span>Audit & pemahaman alur keputusan</span>
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-teal/10 text-bsds-teal group-hover:bg-bsds-teal group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-chart-bar class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Penilaian 4 Indikator</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Dapatkan feedback diagnostik per indikator: apakah Anda unggul dalam investigasi data atau perlu latihan dalam menimbang alternatif opsi.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-teal">
                            <span>Laporan diagnostik terstruktur</span>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-7 hover:border-bsds-teal/50 hover:shadow-lg transition-all group flex flex-col">
                        <div class="w-13 h-13 rounded-2xl bg-bsds-navy/10 text-bsds-navy group-hover:bg-bsds-navy group-hover:text-white transition-colors flex items-center justify-center mb-6">
                            <x-heroicon-o-academic-cap class="w-7 h-7" />
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">Practice & Assessment Mode</h3>
                        <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                            Mode Practice untuk belajar bebas dengan petunjuk langsung, serta Mode Assessment untuk evaluasi formal terstandar bagi instansi dan kelas.
                        </p>
                        <div class="mt-4 pt-4 border-t border-bsds-border/60 flex items-center gap-2 text-xs font-semibold text-bsds-navy">
                            <span>Fleksibilitas evaluasi</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ALUR SIMULASI (HOW IT WORKS PIPELINE) -->
            <section id="alur" class="py-20 bg-white border-y border-bsds-border">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                            <x-heroicon-o-play class="w-3.5 h-3.5 text-bsds-teal" />
                            Alur Pembelajaran
                        </span>
                        <h2 class="font-heading font-bold text-2xl sm:text-4xl text-bsds-navy mt-4 tracking-tight">
                            Bagaimana Simulasi Dijalankan?
                        </h2>
                        <p class="text-bsds-gray text-base sm:text-lg mt-3">
                            4 langkah sederhana yang mengasah insting profesional dalam menangani situasi bisnis nyata.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                        <!-- Step 1 -->
                        <div class="bg-bsds-bg rounded-2xl border border-bsds-border p-6 flex flex-col relative group hover:border-bsds-teal transition">
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-bsds-navy text-white font-heading font-bold text-sm flex items-center justify-center">
                                    01
                                </span>
                                <x-heroicon-o-book-open class="w-6 h-6 text-bsds-navy/40 group-hover:text-bsds-teal transition-colors" />
                            </div>
                            <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Read & Briefing</h3>
                            <p class="text-sm text-bsds-gray leading-relaxed">
                                Pelajari peran kerja, latar belakang masalah, dan situasi mendesak yang dihadapi pelanggan.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-bsds-bg rounded-2xl border border-bsds-border p-6 flex flex-col relative group hover:border-bsds-teal transition">
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-bsds-teal text-white font-heading font-bold text-sm flex items-center justify-center">
                                    02
                                </span>
                                <x-heroicon-o-magnifying-glass class="w-6 h-6 text-bsds-teal/40 group-hover:text-bsds-teal transition-colors" />
                            </div>
                            <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Investigate SOP</h3>
                            <p class="text-sm text-bsds-gray leading-relaxed">
                                Buka tab investigasi untuk memverifikasi SOP, log pesanan, dan batas kewenangan posisi Anda.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-bsds-bg rounded-2xl border border-bsds-border p-6 flex flex-col relative group hover:border-bsds-teal transition">
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-bsds-orange text-white font-heading font-bold text-sm flex items-center justify-center">
                                    03
                                </span>
                                <x-heroicon-o-arrows-pointing-out class="w-6 h-6 text-bsds-orange/40 group-hover:text-bsds-orange transition-colors" />
                            </div>
                            <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Decide & Adapt</h3>
                            <p class="text-sm text-bsds-gray leading-relaxed">
                                Tentukan alternatif terbaik dan hadapi konsekuensi langsung pada skenario berikutnya.
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="bg-bsds-bg rounded-2xl border border-bsds-border p-6 flex flex-col relative group hover:border-bsds-teal transition">
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-heading font-bold text-sm flex items-center justify-center">
                                    04
                                </span>
                                <x-heroicon-o-chart-bar class="w-6 h-6 text-emerald-600/40 group-hover:text-emerald-600 transition-colors" />
                            </div>
                            <h3 class="font-heading font-bold text-base text-bsds-navy mb-1.5">Reflect & Score</h3>
                            <p class="text-sm text-bsds-gray leading-relaxed">
                                Telusuri visual decision journey dan terima evaluasi skor performa berdasarkan 4 indikator utama.
                            </p>
                        </div>
                    </div>

                    <!-- Flow Highlight Box -->
                    <div class="mt-12 bg-bsds-bg rounded-2xl border border-bsds-border p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <div class="p-3 rounded-xl bg-bsds-teal/10 text-bsds-teal shrink-0">
                                <x-heroicon-o-light-bulb class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="font-heading font-bold text-base text-bsds-navy">Fokus pada Proses Analisis, Bukan Sekadar Jawaban Akhir</h4>
                                <p class="text-sm text-bsds-gray mt-1">
                                    Di dunia pelayanan bisnis, keputusan terbaik lahir dari pertimbangan yang matang antara kepuasan pelanggan, batas SOP, dan keberlanjutan operasional.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('guide') }}" class="shrink-0 px-5 py-2.5 rounded-xl bg-white border border-bsds-border hover:border-bsds-teal text-bsds-navy text-sm font-semibold transition">
                            Baca Buku Panduan &rarr;
                        </a>
                    </div>
                </div>
            </section>

            <!-- SKENARIO KASUS (SHOWCASE STUDI KASUS) -->
            <section id="skenario" class="py-20 lg:py-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-14 gap-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-navy bg-bsds-navy/10">
                            <x-heroicon-o-puzzle-piece class="w-4 h-4 text-bsds-navy" />
                            Studi Kasus Nyata
                        </span>
                        <h2 class="font-heading font-bold text-2xl sm:text-4xl text-bsds-navy mt-4 tracking-tight">
                            Skenario Siap Uji dari Kasus Bisnis Aktual
                        </h2>
                        <p class="text-bsds-gray text-base mt-2 max-w-xl">
                            Didesain dengan tingkat kesulitan bertahap untuk membangun kapabilitas problem solving secara terarah.
                        </p>
                    </div>

                    @auth
                        <a href="{{ route('simulations.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition shadow-xs">
                            <span>Jelajahi Semua Kasus</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition shadow-xs">
                            <span>Masuk untuk Mulai Kasus</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    @endauth
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Case Card 1 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-6 flex flex-col hover:shadow-md hover:border-bsds-teal/50 transition">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-01</span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-teal-100 text-bsds-teal">Foundation</span>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">
                            Customer Complaint Handling
                        </h3>
                        <p class="text-sm text-bsds-gray leading-relaxed mb-6 flex-1">
                            Tangani komplain pelanggan VIP yang menerima produk salah saat promo flash sale, di tengah keterbatasan stok gudang dan tekanan waktu keberangkatan.
                        </p>
                        <div class="pt-4 border-t border-bsds-border/70 flex items-center justify-between text-xs text-bsds-gray">
                            <span class="inline-flex items-center gap-1 font-medium">
                                <x-heroicon-o-briefcase class="w-4 h-4 text-bsds-teal" /> CS Officer
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium">
                                <x-heroicon-o-clock class="w-4 h-4 text-bsds-orange" /> 15 - 20 Mnt
                            </span>
                        </div>
                    </div>

                    <!-- Case Card 2 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-6 flex flex-col hover:shadow-md hover:border-bsds-teal/50 transition">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-02</span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Developing</span>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">
                            Service Return & Disputed Policy
                        </h3>
                        <p class="text-sm text-bsds-gray leading-relaxed mb-6 flex-1">
                            Hadapi perselisihan kebijakan pengembalian barang rusak tanpa bukti unboxing awal, menyeimbangkan kepuasan konsumen vs proteksi aset toko.
                        </p>
                        <div class="pt-4 border-t border-bsds-border/70 flex items-center justify-between text-xs text-bsds-gray">
                            <span class="inline-flex items-center gap-1 font-medium">
                                <x-heroicon-o-briefcase class="w-4 h-4 text-bsds-teal" /> Service Lead
                            </span>
                            <span class="inline-flex items-center gap-1 font-medium">
                                <x-heroicon-o-clock class="w-4 h-4 text-bsds-orange" /> 20 - 25 Mnt
                            </span>
                        </div>
                    </div>

                    <!-- Case Card 3 -->
                    <div class="bg-white rounded-2xl border border-bsds-border p-6 flex flex-col hover:shadow-md hover:border-bsds-teal/50 transition">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-bsds-navy/10 text-bsds-navy tracking-wider">CASE-03</span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-rose-100 text-rose-800">Challenge</span>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-bsds-navy mb-2">
                            Critical Escalation Management
                        </h3>
                        <p class="text-sm text-bsds-gray leading-relaxed mb-6 flex-1">
                            Selesaikan insiden viral di media sosial dengan koordinasi lintas divisi: Public Relations, Legal, dan Operational Service Manager.
                        </p>
                        <div class="pt-4 border-t border-bsds-border/70 flex items-center justify-between text-xs text-bsds-gray">
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
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                            <x-heroicon-o-chart-bar class="w-3.5 h-3.5 text-bsds-teal" />
                            Matriks Penilaian Terstandar
                        </span>
                        <h2 class="font-heading font-bold text-2xl sm:text-4xl text-bsds-navy mt-4 tracking-tight">
                            4 Indikator Pengambilan Keputusan
                        </h2>
                        <p class="text-bsds-gray text-base sm:text-lg mt-3">
                            Setiap opsi keputusan diberi bobot penilaian objektif untuk memetakan kekuatan dan aspek perbaikan Anda.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Indikator 1 -->
                        <div class="bg-bsds-bg p-6 rounded-2xl border border-bsds-border flex flex-col">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-navy text-white">IDN</span>
                                <h3 class="font-heading font-bold text-base text-bsds-navy">Identifikasi Masalah</h3>
                            </div>
                            <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                                Kemampuan mendeteksi akar penyebab masalah utama dari komplain atau dilema pelanggan sebelum terburu-buru mengambil tindakan.
                            </p>
                        </div>

                        <!-- Indikator 2 -->
                        <div class="bg-bsds-bg p-6 rounded-2xl border border-bsds-border flex flex-col">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-teal text-white">INF</span>
                                <h3 class="font-heading font-bold text-base text-bsds-navy">Pengumpulan Informasi</h3>
                            </div>
                            <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                                Ketepatan memanfaatkan data pendukung seperti SOP layanan, data stok produk, riwayat transaksi, dan konsultasi supervisor.
                            </p>
                        </div>

                        <!-- Indikator 3 -->
                        <div class="bg-bsds-bg p-6 rounded-2xl border border-bsds-border flex flex-col">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-bsds-orange text-white">ALT</span>
                                <h3 class="font-heading font-bold text-base text-bsds-navy">Pertimbangan Opsi</h3>
                            </div>
                            <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                                Kemampuan menimbang trade-off, risiko kerugian, dan dampak jangka panjang antara berbagai solusi alternatif yang ada.
                            </p>
                        </div>

                        <!-- Indikator 4 -->
                        <div class="bg-bsds-bg p-6 rounded-2xl border border-bsds-border flex flex-col">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-emerald-600 text-white">ACT</span>
                                <h3 class="font-heading font-bold text-base text-bsds-navy">Pemilihan Tindakan</h3>
                            </div>
                            <p class="text-sm text-bsds-gray leading-relaxed flex-1">
                                Ketepatan dan keberanian mengeksekusi langkah terbaik yang sejalan dengan etika bisnis serta prosedur standar pelayanan.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ SECTION (ALPINE ACCORDION) -->
            <section id="faq" class="py-20 lg:py-28 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeAccordion: null }">
                <div class="text-center mb-14">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10">
                        <x-heroicon-o-question-mark-circle class="w-4 h-4 text-bsds-teal" />
                        Pertanyaan Umum
                    </span>
                    <h2 class="font-heading font-bold text-2xl sm:text-4xl text-bsds-navy mt-4 tracking-tight">
                        Pertanyaan yang Sering Diajukan
                    </h2>
                    <p class="text-bsds-gray text-base mt-2">
                        Semua yang perlu Anda ketahui mengenai platform simulasi BSDS.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <div class="bg-white rounded-2xl border border-bsds-border overflow-hidden transition">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 1 ? null : 1"
                                class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-heading font-semibold text-base text-bsds-navy hover:text-bsds-teal transition-colors">
                            <span>Siapa yang cocok menggunakan simulator ini?</span>
                            <span class="shrink-0 transition-transform duration-200"
                                  x-bind:class="activeAccordion === 1 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                                <x-heroicon-o-chevron-down class="w-5 h-5" />
                            </span>
                        </button>
                        <div x-show="activeAccordion === 1" x-collapse x-cloak class="px-6 pb-5 text-sm text-bsds-gray leading-relaxed border-t border-bsds-border/60 pt-4">
                            Simulator ini dirancang khusus untuk mahasiswa bisnis/manajemen, siswa kejuruan, praktisi customer service, serta tim operasional yang ingin mengasah keterampilan problem solving dan pengambilan keputusan dalam situasi layanan pelanggan.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="bg-white rounded-2xl border border-bsds-border overflow-hidden transition">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 2 ? null : 2"
                                class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-heading font-semibold text-base text-bsds-navy hover:text-bsds-teal transition-colors">
                            <span>Apa perbedaan antara Practice Mode dan Assessment Mode?</span>
                            <span class="shrink-0 transition-transform duration-200"
                                  x-bind:class="activeAccordion === 2 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                                <x-heroicon-o-chevron-down class="w-5 h-5" />
                            </span>
                        </button>
                        <div x-show="activeAccordion === 2" x-collapse x-cloak class="px-6 pb-5 text-sm text-bsds-gray leading-relaxed border-t border-bsds-border/60 pt-4">
                            Pada <strong>Practice Mode</strong>, Anda bebas mencoba opsi tanpa tekanan, dapat mengakses hint bertahap, serta melihat langsung penjelasan dampak setiap keputusan. Pada <strong>Assessment Mode</strong>, simulasi berfungsi sebagai ujian evaluasi resmi tanpa petunjuk bantuan, di mana skor akhir Anda langsung direkam dalam laporan capaian kelas.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="bg-white rounded-2xl border border-bsds-border overflow-hidden transition">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 3 ? null : 3"
                                class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-heading font-semibold text-base text-bsds-navy hover:text-bsds-teal transition-colors">
                            <span>Bagaimana jika saya memilih tindakan yang keliru?</span>
                            <span class="shrink-0 transition-transform duration-200"
                                  x-bind:class="activeAccordion === 3 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                                <x-heroicon-o-chevron-down class="w-5 h-5" />
                            </span>
                        </button>
                        <div x-show="activeAccordion === 3" x-collapse x-cloak class="px-6 pb-5 text-sm text-bsds-gray leading-relaxed border-t border-bsds-border/60 pt-4">
                            Sistem akan secara dinamis mengarahkan Anda ke skenario lanjutan atau alur remedial sesuai dampak keputusan tersebut. Anda akan melihat langsung reaksi pelanggan dan belajar bagaimana cara memulihkan situasi (service recovery) secara konstruktif.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="bg-white rounded-2xl border border-bsds-border overflow-hidden transition">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === 4 ? null : 4"
                                class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-heading font-semibold text-base text-bsds-navy hover:text-bsds-teal transition-colors">
                            <span>Apakah progres simulasi dapat dilanjutkan jika keluar di tengah jalan?</span>
                            <span class="shrink-0 transition-transform duration-200"
                                  x-bind:class="activeAccordion === 4 ? 'rotate-180 text-bsds-teal' : 'text-bsds-gray'">
                                <x-heroicon-o-chevron-down class="w-5 h-5" />
                            </span>
                        </button>
                        <div x-show="activeAccordion === 4" x-collapse x-cloak class="px-6 pb-5 text-sm text-bsds-gray leading-relaxed border-t border-bsds-border/60 pt-4">
                            Ya, progres titik skenario Anda tersimpan otomatis di server. Saat masuk kembali ke dashboard, Anda cukup menekan tombol "Lanjutkan Simulasi" untuk meneruskan dari skenario terakhir.
                        </div>
                    </div>
                </div>
            </section>

            <!-- CALL TO ACTION BANNER -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
                <div class="relative rounded-3xl bg-gradient-to-br from-bsds-navy via-[#1b3b5b] to-[#14283d] text-white p-8 sm:p-14 overflow-hidden shadow-2xl">
                    <!-- Accent Circles -->
                    <div class="absolute -top-24 -right-24 w-80 h-80 bg-bsds-teal/20 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-bsds-orange/20 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider text-bsds-orange bg-white/10">
                            Mulai Pengalaman Belajar
                        </span>
                        <h2 class="font-heading font-extrabold text-2xl sm:text-4xl lg:text-5xl tracking-tight mt-4 leading-tight">
                            Siap Mengambil Keputusan Terbaik Hari Ini?
                        </h2>
                        <p class="text-white/80 text-base sm:text-lg mt-4 leading-relaxed max-w-2xl">
                            Bergabunglah bersama ratusan pembelajar lainnya. Jelajahi skenario, uji hipotesis penanganan, dan raih wawasan kepemimpinan layanan bisnis.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-4 mt-8">
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
        <footer class="bg-white border-t border-bsds-border text-bsds-gray text-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
                    <!-- Brand Column -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-application-logo class="h-8 w-auto text-bsds-navy" />
                            <span class="font-heading font-bold text-lg text-bsds-navy">BSDS</span>
                        </div>
                        <p class="text-xs sm:text-sm text-bsds-gray max-w-sm leading-relaxed">
                            Business Service Decision Simulator &mdash; media pembelajaran interaktif berbasis web untuk melatih keterampilan pengambilan keputusan dalam situasi pelayanan bisnis realistis.
                        </p>
                        <div class="text-xs text-bsds-gray font-mono">
                            Decide &bull; Experience &bull; Learn &bull; Reflect
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-bsds-navy mb-3">Navigasi Utama</h4>
                        <ul class="space-y-2 text-xs sm:text-sm">
                            <li><a href="#fitur" class="hover:text-bsds-teal transition-colors">Fitur Unggulan</a></li>
                            <li><a href="#alur" class="hover:text-bsds-teal transition-colors">Alur Simulasi</a></li>
                            <li><a href="#skenario" class="hover:text-bsds-teal transition-colors">Skenario Kasus</a></li>
                            <li><a href="#indikator" class="hover:text-bsds-teal transition-colors">Indikator Penilaian</a></li>
                            <li><a href="{{ route('guide') }}" class="hover:text-bsds-teal transition-colors">Buku Panduan</a></li>
                        </ul>
                    </div>

                    <!-- Account & Platform -->
                    <div>
                        <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-bsds-navy mb-3">Akses Platform</h4>
                        <ul class="space-y-2 text-xs sm:text-sm">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="hover:text-bsds-teal transition-colors">Dashboard Siswa</a></li>
                                <li><a href="{{ route('simulations.index') }}" class="hover:text-bsds-teal transition-colors">Daftar Skenario</a></li>
                                <li><a href="{{ route('progress') }}" class="hover:text-bsds-teal transition-colors">Progres Belajar</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-bsds-teal transition-colors">Masuk Akun</a></li>
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" class="hover:text-bsds-teal transition-colors">Pendaftaran Akun Baru</a></li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>

                <div class="pt-8 border-t border-bsds-border flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-bsds-gray">
                    <p>&copy; {{ date('Y') }} Business Service Decision Simulator. Hak Cipta Dilindungi.</p>
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-bsds-bg border border-bsds-border font-mono text-[11px]">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>v{{ app()->version() }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
