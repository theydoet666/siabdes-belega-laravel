<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $rapat->judul ?? 'Kiosk Absensi' }} — SIABDES Belega</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            touch-action: pan-y;
            user-select: none;
            -webkit-user-select: none;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-kertas text-tinta min-h-screen flex flex-col justify-between antialiased selection:bg-none">
    {{ $slot }}
</body>
</html>
