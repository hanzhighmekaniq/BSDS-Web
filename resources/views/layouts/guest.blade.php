<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-bsds-ink antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-bsds-bg px-4">
            <div class="flex flex-col items-center gap-2">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-14 h-14 text-bsds-navy" />
                </a>
                <h1 class="font-heading font-semibold text-bsds-navy text-lg tracking-wide text-center">BUSINESS SERVICE DECISION SIMULATOR</h1>
                <p class="text-xs text-bsds-gray">Decide. Experience. Learn.</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white border border-bsds-border overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
