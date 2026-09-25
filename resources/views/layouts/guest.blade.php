<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — SIABDES Belega</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="bg-kertas text-tinta min-h-screen flex flex-col justify-between antialiased selection:bg-daun selection:text-white">
    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </div>

    <!-- Footer Resmi -->
    <footer class="py-4 border-t border-garis/60 text-center text-xs text-tinta/60">
        <p>© {{ date('Y') }} Pemerintah Desa Belega, Kec. Blahbatuh, Kab. Gianyar · SIABDES v2.0</p>
    </footer>

    @livewireScripts
</body>
</html>
