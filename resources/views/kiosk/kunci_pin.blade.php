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
    <title>Kunci PIN Kiosk — {{ $rapat->judul }}</title>

    @if ($effectiveFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $effectiveFavicon }}">
        <link rel="shortcut icon" href="{{ $effectiveFavicon }}">
        <link rel="apple-touch-icon" href="{{ $effectiveFavicon }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2322543d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'/%3E%3C/svg%3E">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-kertas text-tinta min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden">
            <!-- Header Kunci PIN -->
            <div class="bg-daun-tua px-8 py-8 text-center text-white">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-xl font-bold">Meja Kiosk Terkunci PIN</h1>
                <p class="text-xs text-emerald-200/90 mt-1">Masukkan 6 digit PIN untuk membuka registrasi absensi</p>
                <div class="mt-3 inline-block px-3 py-1 rounded-full bg-white/10 text-xs font-mono font-semibold text-white border border-white/20">
                    KODE: {{ $rapat->kode }}
                </div>
            </div>

            <!-- Form PIN -->
            <div class="p-8">
                <div class="mb-6 text-center">
                    <h2 class="font-bold text-sm text-tinta line-clamp-1">{{ $rapat->judul }}</h2>
                    <p class="text-xs text-tinta/50 mt-0.5">{{ $rapat->tempat }}</p>
                </div>

                @if ($pesanError)
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $pesanError }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('kiosk.index', ['kode' => $rapat->kode]) }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="pin_kiosk_input" class="block text-xs font-semibold text-tinta mb-2 text-center">
                            Masukkan PIN 6 Digit
                        </label>
                        <input type="password" 
                               name="pin_kiosk_input" 
                               id="pin_kiosk_input" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               required 
                               autofocus 
                               placeholder="••••••" 
                               class="w-full text-center text-2xl font-mono tracking-widest py-3 px-4 bg-kertas/50 border border-garis rounded-2xl text-tinta focus:outline-none focus:border-daun focus:ring-2 focus:ring-daun/20 transition" />
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-xl bg-daun hover:bg-daun-tua text-white font-semibold text-sm shadow-md shadow-daun/20 transition cursor-pointer">
                        Buka Meja Registrasi Kiosk
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
