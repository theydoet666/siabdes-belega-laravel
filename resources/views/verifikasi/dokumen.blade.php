<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keabsahan Dokumen - {{ $rapat->kode }} - {{ $pengaturan->nama_sistem ?? 'SIABDES Belega' }}</title>

    @if ($pengaturan->favicon_url)
        <link rel="icon" href="{{ $pengaturan->favicon_url }}">
    @elseif ($pengaturan->logo_url)
        <link rel="icon" href="{{ $pengaturan->logo_url }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-slate-100 min-h-screen font-sans antialiased py-6 sm:py-10 px-4 sm:px-6">

    <div class="max-w-3xl mx-auto space-y-6">

        <!-- HEADER KOP RESMI PEMERINTAH DESA -->
        <header class="bg-white/10 backdrop-blur-md rounded-3xl border border-white/15 p-5 sm:p-7 shadow-2xl text-center relative overflow-hidden">
            <div class="absolute -top-24 -left-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-center sm:text-left">
                <!-- Logo Instansi -->
                <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 bg-white/90 p-2 rounded-2xl shadow-md border border-white/20 flex items-center justify-center">
                    @if ($pengaturan->logo_url)
                        <img src="{{ $pengaturan->logo_url }}" alt="Logo Instansi" class="max-h-full max-w-full object-contain" />
                    @else
                        <div class="w-full h-full border-2 border-slate-800 rounded-full flex flex-col items-center justify-center text-slate-900 text-center font-bold">
                            <span class="text-[9px] uppercase leading-none">Desa</span>
                            <span class="text-[11px] font-black uppercase leading-none mt-0.5">Belega</span>
                        </div>
                    @endif
                </div>

                <div class="space-y-0.5">
                    <div class="text-[11px] sm:text-xs font-semibold tracking-wider uppercase text-emerald-400">
                        {{ $pengaturan->kabupaten ?: 'Kabupaten Gianyar' }} — {{ $pengaturan->kecamatan ?: 'Kecamatan Blahbatuh' }}
                    </div>
                    <h1 class="text-lg sm:text-xl font-extrabold uppercase tracking-wide text-white">
                        {{ $pengaturan->nama_desa ?: 'Pemerintah Desa Belega' }}
                    </h1>
                    <p class="text-xs text-slate-300">
                        {{ $pengaturan->subjudul_sistem ?: 'Sistem Informasi Absensi & Dokumen Rapat Digital (SIABDES)' }}
                    </p>
                </div>
            </div>
        </header>

        <!-- KARTU STATUS VERIFIKASI KEABSAHAN DOKUMEN -->
        <main class="bg-white rounded-3xl text-slate-800 shadow-2xl border border-slate-100 overflow-hidden">
            
            <!-- Banner Status Validitas -->
            <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white p-6 sm:p-8 text-center relative">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white text-emerald-700 shadow-lg mb-3">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                
                <div class="inline-block px-3 py-1 rounded-full bg-emerald-500/30 text-emerald-100 border border-emerald-400/40 text-[11px] font-bold uppercase tracking-wider mb-2">
                    E-Government Desa Terverifikasi
                </div>

                <h2 class="text-xl sm:text-2xl font-black uppercase tracking-wide">
                    Dokumen Resmi Terverifikasi
                </h2>
                <p class="text-xs sm:text-sm text-emerald-100 max-w-lg mx-auto mt-1">
                    Daftar hadir dan berita acara kegiatan ini telah terdaftar secara sah dan valid dalam basis data Pemerintah Desa Belega.
                </p>
            </div>

            <!-- Rincian Dokumen Rapat -->
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Info Header Kode Dokumen & Waktu Pengecekan -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-500">Kode Unik Dokumen:</span>
                        <span class="ml-1.5 font-mono font-bold text-slate-900 text-sm bg-slate-200/80 px-2 py-0.5 rounded">{{ $rapat->kode }}</span>
                    </div>
                    <div class="text-slate-600">
                        <span>Diverifikasi pada:</span>
                        <span class="font-medium text-slate-800">{{ $waktuVerifikasi }}</span>
                    </div>
                </div>

                <!-- Detail Kegiatan Rapat -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Informasi Kegiatan Rapat
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100 space-y-1 sm:col-span-2">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nama / Agenda Acara</span>
                            <div class="font-bold text-slate-900 text-base leading-snug">{{ $rapat->judul }}</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100 space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hari & Tanggal</span>
                            <div class="font-semibold text-slate-800">{{ $tanggalRapatFormatted }}</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100 space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Waktu Pelaksanaan</span>
                            <div class="font-semibold text-slate-800">{{ $waktuRapatFormatted }}</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100 space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tempat / Lokasi</span>
                            <div class="font-semibold text-slate-800">{{ $rapat->tempat }}</div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-100 space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Penyelenggara</span>
                            <div class="font-semibold text-slate-800">{{ $rapat->penyelenggara ?: 'Pemerintah Desa Belega' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Kehadiran Rapat -->
                <div class="space-y-3 pt-2">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Rekapitulasi Kehadiran
                    </h3>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                            <div class="text-xl sm:text-2xl font-black text-emerald-800">{{ $totalHadir }}</div>
                            <div class="text-[10px] sm:text-xs font-semibold text-emerald-700 uppercase mt-0.5">Hadir Resmi</div>
                        </div>
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-xl sm:text-2xl font-black text-slate-800">{{ $totalUndangan }}</div>
                            <div class="text-[10px] sm:text-xs font-semibold text-slate-600 uppercase mt-0.5">Total Undangan</div>
                        </div>
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-teal-50 border border-teal-100">
                            <div class="text-xl sm:text-2xl font-black text-teal-800">{{ $persentaseHadir }}%</div>
                            <div class="text-[10px] sm:text-xs font-semibold text-teal-700 uppercase mt-0.5">Persentase</div>
                        </div>
                    </div>
                </div>

                <!-- Pejabat Penandatangan & Pengesah -->
                <div class="space-y-3 pt-2">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Pejabat Pengesah Dokumen
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <!-- Verifikator -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-semibold text-slate-500 uppercase">{{ $rapat->verifikator_label ?: 'Verifikator' }}</div>
                            <div class="font-bold text-slate-900 mt-1">{{ $rapat->verifikator_nama ?: 'Gusti Ketut Amertayasa, S.M' }}</div>
                            <div class="text-slate-600 text-[11px]">{{ $rapat->verifikator_jabatan ?: 'Sekretaris Desa' }}</div>
                        </div>

                        <!-- Pelaksana -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-semibold text-slate-500 uppercase">Pelaksana Kegiatan</div>
                            <div class="font-bold text-slate-900 mt-1">{{ $rapat->pelaksana_nama ?: 'Ni Made Arini' }}</div>
                            <div class="text-slate-600 text-[11px]">{{ $rapat->pelaksana_jabatan ?: 'Kasi Pelayanan' }}</div>
                        </div>

                        <!-- Mengetahui -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="text-[10px] font-semibold text-slate-500 uppercase">Mengetahui / Mengesahkan</div>
                            <div class="font-bold text-slate-900 mt-1">{{ $rapat->penandatangan_nama ?: 'I WAYAN SUDARSANA, S.Sos.' }}</div>
                            <div class="text-slate-600 text-[11px]">{{ $rapat->penandatangan_jabatan ?: 'Perbekel Belega' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Kepatuhan Hukum & Privasi (PDP) -->
                <div class="pt-4 border-t border-slate-200 text-[11px] text-slate-500 leading-relaxed flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <span class="font-semibold text-slate-700">Jaminan Keabsahan & Kepatuhan Privasi:</span>
                        Informasi ini diterbitkan secara otomatis oleh Sistem SIABDES Pemerintah Desa Belega sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (PDP). Daftar rincian kontak dan data privat peserta dilindungi dan hanya dapat diakses melalui portal resmi administrasi desa.
                    </div>
                </div>

            </div>

            <!-- Footer Card -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
                <div>
                    © {{ date('Y') }} {{ $pengaturan->nama_desa ?: 'Pemerintah Desa Belega' }}
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('privasi') }}" class="text-emerald-700 hover:text-emerald-900 font-semibold hover:underline">
                        Kebijakan Privasi
                    </a>
                </div>
            </div>
        </main>

    </div>

</body>
</html>
