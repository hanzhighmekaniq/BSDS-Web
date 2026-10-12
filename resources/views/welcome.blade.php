<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Business Service Decision Simulator') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-bsds-ink antialiased bg-bsds-bg">

        <!-- Header -->
        <header class="bg-white border-b border-bsds-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="h-8 w-auto text-bsds-navy" />
                    <span class="font-heading font-semibold text-bsds-navy">BSDS</span>
                </a>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="min-h-[40px] inline-flex items-center px-5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-bsds-navy hover:text-bsds-teal transition">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="min-h-[40px] inline-flex items-center px-5 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white text-sm font-semibold transition">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <main>
            <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-14 text-center">
                <span class="inline-block text-xs font-semibold uppercase tracking-wider text-bsds-teal bg-bsds-teal/10 px-3 py-1 rounded-full">
                    Media Pembelajaran Interaktif
                </span>

                <h1 class="font-heading font-semibold text-3xl sm:text-5xl text-bsds-navy leading-tight mt-5">
                    Business Service<br class="hidden sm:block"> Decision Simulator
                </h1>

                <p class="font-heading text-bsds-teal font-semibold tracking-wide mt-3">
                    Decide. Experience. Learn.
                </p>

                <p class="text-bsds-gray max-w-xl mx-auto mt-4 leading-relaxed">
                    Latih keterampilan pengambilan keputusan melalui simulasi pelayanan bisnis yang realistis.
                    Setiap keputusan Anda memengaruhi situasi berikutnya.
                </p>

                <div class="mt-8">
                    <a href="{{ Auth::check() ? route('dashboard') : route('login') }}"
                        class="min-h-[52px] inline-flex items-center px-8 rounded-xl bg-bsds-navy hover:bg-bsds-teal text-white font-semibold transition">
                        &#9654; Mulai Simulasi
                    </a>
                </div>

                <div class="flex items-center justify-center gap-6 sm:gap-10 mt-10 text-xs sm:text-sm font-semibold text-bsds-gray">
                    <span>Practice</span>
                    <span class="w-1 h-1 rounded-full bg-bsds-border"></span>
                    <span>Simulate</span>
                    <span class="w-1 h-1 rounded-full bg-bsds-border"></span>
                    <span>Reflect</span>
                </div>
            </section>

            <!-- How it works -->
            <section class="bg-white border-y border-bsds-border">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                    <h2 class="font-heading font-semibold text-xl text-bsds-navy text-center mb-10">How It Works</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="rounded-2xl border border-bsds-border p-6 text-center">
                            <div class="w-12 h-12 rounded-xl bg-bsds-navy/10 text-bsds-navy flex items-center justify-center text-xl mx-auto mb-4">01</div>
                            <h3 class="font-heading font-semibold text-bsds-navy mb-1">Analyze</h3>
                            <p class="text-sm text-bsds-gray">Pahami masalah dan informasi yang tersedia.</p>
                        </div>
                        <div class="rounded-2xl border border-bsds-border p-6 text-center">
                            <div class="w-12 h-12 rounded-xl bg-bsds-teal/10 text-bsds-teal flex items-center justify-center text-xl mx-auto mb-4">02</div>
                            <h3 class="font-heading font-semibold text-bsds-navy mb-1">Decide</h3>
                            <p class="text-sm text-bsds-gray">Pilih tindakan berdasarkan situasi yang dihadapi.</p>
                        </div>
                        <div class="rounded-2xl border border-bsds-border p-6 text-center">
                            <div class="w-12 h-12 rounded-xl bg-bsds-orange/10 text-bsds-orange flex items-center justify-center text-xl mx-auto mb-4">03</div>
                            <h3 class="font-heading font-semibold text-bsds-navy mb-1">Adapt</h3>
                            <p class="text-sm text-bsds-gray">Hadapi konsekuensi dan situasi baru yang muncul.</p>
                        </div>
                    </div>
                </div>
            </section>

            <p class="text-center text-xs text-bsds-gray py-8">
                Business Service Decision Simulator &bull; v{{ app()->version() }}
            </p>
        </main>
    </body>
</html>
