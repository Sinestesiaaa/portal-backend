<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Dokumen PST</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="/logo.png">
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        {{ $slot }}
    </div>
</body>

</html>
