@php
    $pengaturanSistem = \App\Models\PengaturanSistem::first();
    $namaSistem = $pengaturanSistem->nama_sistem ?? 'SIABDES Belega';
    $logoUrl = $pengaturanSistem->logo_url ?? null;
    $faviconUrl = $pengaturanSistem->favicon_url ?? null;
    $effectiveFavicon = $faviconUrl ?: $logoUrl;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — {{ $namaSistem }}</title>

    @if ($effectiveFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $effectiveFavicon }}">
        <link rel="shortcut icon" href="{{ $effectiveFavicon }}">
        <link rel="apple-touch-icon" href="{{ $effectiveFavicon }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2322543d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'/%3E%3C/svg%3E">
    @endif

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
