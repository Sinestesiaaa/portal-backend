<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Portal Dokumen PST</title>
    <link rel="icon" type="image/png" href="/logo.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Custom Page Styles --}}
    @stack('styles')
</head>

<body class="font-sans antialiased bg-[#F8FAFC] text-gray-800">
    <div class="min-h-screen">

        {{-- 🔹 Navigation Bar --}}
        @include('layouts.navigation')

        {{-- 🔹 Page Header (optional) --}}
        @isset($header)
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-xl font-semibold text-gray-800 tracking-wide">
                        {{ $header }}
                    </h1>
                </div>
            </header>
        @endisset

        {{-- 🔹 Page Content --}}
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</body>

</html>
