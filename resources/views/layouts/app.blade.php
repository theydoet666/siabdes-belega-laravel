@php
    $pengaturanSistem = \App\Models\PengaturanSistem::first();
    $namaSistem = $pengaturanSistem->nama_sistem ?? 'SIABDES Belega';
    $subjudulSistem = $pengaturanSistem->subjudul_sistem ?? 'Sistem Absensi Rapat Kantor Desa Belega';
    $logoUrl = $pengaturanSistem->logo_url ?? null;
    $faviconUrl = $pengaturanSistem->favicon_url ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $namaSistem }} — {{ $subjudulSistem }}</title>

    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
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
    <!-- Header Navigasi -->
    <header class="bg-daun-tua text-white sticky top-0 z-40 shadow-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Identitas -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('rapat.index') }}" class="flex items-center space-x-3 group">
                        @if ($logoUrl)
                            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center p-1 border border-white/20 group-hover:bg-white/20 transition overflow-hidden">
                                <img src="{{ $logoUrl }}" alt="Logo" class="max-h-8 max-w-8 object-contain" />
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center font-bold text-white border border-white/20 group-hover:bg-white/20 transition">
                                <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="font-bold tracking-tight text-base sm:text-lg leading-tight flex items-center gap-2">
                                <span>{{ $namaSistem }}</span>
                                <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">v2.0</span>
                            </div>
                            <p class="text-xs text-emerald-200/80 hidden sm:block">{{ $subjudulSistem }}</p>
                        </div>
                    </a>
                </div>

                <!-- Menu Navigasi Utama -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('rapat.index') }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('rapat.*') ? 'bg-white/15 text-white' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        Daftar Rapat
                    </a>
                    
                    @can('admin')
                    <a href="{{ route('pengaturan') }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('pengaturan*') ? 'bg-white/15 text-white' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                        Pengaturan
                    </a>
                    @endcan
                </nav>

                <!-- Info Operator & Logout -->
                <div class="flex items-center space-x-3">
                    @auth
                        @php
                            $profil = auth()->user()->profil;
                            $isAdmin = $profil && $profil->peran === \App\Enums\PeranPengguna::ADMIN;
                        @endphp
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-white leading-tight">
                                {{ $profil?->nama ?? auth()->user()->name }}
                            </div>
                            <div class="text-[11px] {{ $isAdmin ? 'text-amber-300' : 'text-emerald-200' }}">
                                {{ $isAdmin ? 'Administrator' : 'Operator Rapat' }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    data-confirm="Apakah Anda yakin ingin keluar dari sistem SIABDES Belega?"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white/10 hover:bg-rose-500/80 hover:text-white text-emerald-100 border border-white/15 transition cursor-pointer"
                                    title="Keluar dari sistem">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Subbar -->
        <div class="md:hidden border-t border-white/10 bg-daun-tua/90 px-4 py-2 flex items-center space-x-2">
            <a href="{{ route('rapat.index') }}" 
               class="px-3 py-1 rounded text-xs font-medium {{ request()->routeIs('rapat.*') ? 'bg-white/20 text-white' : 'text-emerald-100/70' }}">
                Daftar Rapat
            </a>
            @can('admin')
            <a href="{{ route('pengaturan') }}" 
               class="px-3 py-1 rounded text-xs font-medium {{ request()->routeIs('pengaturan*') ? 'bg-white/20 text-white' : 'text-emerald-100/70' }}">
                Pengaturan
            </a>
            @endcan
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{ $slot }}
    </main>

    <!-- Footer Resmi (§5.7 PRD) -->
    @include('komponen.footer-resmi')

    @livewireScripts
</body>
</html>
