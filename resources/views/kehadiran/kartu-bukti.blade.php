<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Bukti Kehadiran - {{ $undangan->nama }} - {{ $rapat->kode }}</title>

    @if ($pengaturan->favicon_url)
        <link rel="icon" href="{{ $pengaturan->favicon_url }}">
    @elseif ($pengaturan->logo_url)
        <link rel="icon" href="{{ $pengaturan->logo_url }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800|plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .kartu-container {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                max-width: 100% !important;
                margin: 0 auto !important;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-slate-100 min-h-screen font-sans antialiased py-6 sm:py-10 px-4 sm:px-6">

    <div class="max-w-xl mx-auto space-y-5">

        <!-- Top Nav / Action Bar (No Print) -->
        <div class="no-print flex items-center justify-between gap-3 bg-white/10 backdrop-blur-md rounded-2xl p-3 sm:p-4 border border-white/15 shadow-lg">
            <a href="{{ route('dokumen.verifikasi', $rapat->kode) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Halaman Verifikasi</span>
            </a>

            <div class="flex items-center gap-2">
                <!-- Tombol Bagikan ke WhatsApp -->
                <a href="{{ $linkWa }}" 
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-sm transition">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>

                <!-- Tombol Cetak / PDF -->
                <button onclick="window.print()" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-white/10 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        <!-- ================= KARTU BUKTI HADIR UTAMA ================= -->
        <div id="kartu-kehadiran-cetak" class="kartu-container bg-white text-slate-800 rounded-3xl shadow-2xl border border-slate-200 overflow-hidden relative">
            
            <!-- Pattern Garis & Ornamen Balinese/Modern Top Bar -->
            <div class="h-3 bg-gradient-to-r from-emerald-600 via-teal-500 to-amber-500"></div>

            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Kop Kartu Resmi -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 shrink-0 bg-slate-50 border border-slate-200 rounded-2xl p-1.5 flex items-center justify-center">
                            @if ($pengaturan->logo_url)
                                <img src="{{ $pengaturan->logo_url }}" alt="Logo" class="max-h-full max-w-full object-contain" />
                            @else
                                <div class="w-full h-full border border-slate-800 rounded-full flex flex-col items-center justify-center text-slate-900 text-center font-bold text-[7px]">
                                    BELEGA
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                {{ $pengaturan->kabupaten ?: 'Pemerintah Kabupaten Gianyar' }}
                            </div>
                            <h2 class="text-sm sm:text-base font-extrabold text-slate-900 uppercase tracking-tight">
                                {{ $pengaturan->nama_desa ?: 'Pemerintah Desa Belega' }}
                            </h2>
                        </div>
                    </div>

                    <!-- Badge Status Sah -->
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>Hadir Sah</span>
                        </span>
                        <div class="text-[9px] font-mono text-slate-400 mt-0.5">SIABDES-{{ $rapat->kode }}</div>
                    </div>
                </div>

                <!-- Judul Kartu -->
                <div class="text-center space-y-1">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 uppercase tracking-wide">
                        KARTU BUKTI KEHADIRAN DIGITAL
                    </h1>
                    <p class="text-[11px] text-slate-500">
                        Bukti resmi keikutsertaan rapat / musyawarah desa terdaftar
                    </p>
                </div>

                <!-- Bagian Profil & Identitas Peserta -->
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
                    <!-- Foto Wajah jika ada -->
                    @if ($fotoUrl)
                        <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl overflow-hidden border-2 border-emerald-600/30 shadow-md">
                            <img src="{{ $fotoUrl }}" alt="Foto Peserta" class="w-full h-full object-cover" />
                        </div>
                    @else

                        <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-xl border border-emerald-200">
                            {{ strtoupper(substr($undangan->nama, 0, 2)) }}
                        </div>
                    @endif

                    <!-- Detail Nama & Jabatan -->
                    <div class="flex-1 text-center sm:text-left space-y-1">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Peserta</div>
                        <div class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight">
                            {{ $undangan->nama }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            {{ $undangan->jabatan ?: 'Peserta Undangan' }}
                            @if ($undangan->instansi)
                                <span class="text-slate-400">·</span> {{ $undangan->instansi }}
                            @endif
                        </div>
                        @if ($kehadiran->diwakili_oleh)
                            <div class="text-[11px] text-amber-700 italic bg-amber-50 px-2 py-0.5 rounded border border-amber-200 inline-block">
                                Diwakili oleh: {{ $kehadiran->diwakili_oleh }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Detail Agenda & Waktu Hadir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-0.5 sm:col-span-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Agenda / Acara</span>
                        <div class="font-bold text-slate-900 text-xs sm:text-sm leading-snug">{{ $rapat->judul }}</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-0.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari & Tanggal Rapat</span>
                        <div class="font-semibold text-slate-800">{{ $tanggalRapatFormatted }}</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-0.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tempat Kegiatan</span>
                        <div class="font-semibold text-slate-800">{{ $rapat->tempat }}</div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100 space-y-0.5 sm:col-span-2">
                        <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Waktu Tepat Absensi (*WITA*)</span>
                        <div class="font-bold text-emerald-950 text-xs sm:text-sm">{{ $waktuHadirFormatted }}</div>
                    </div>
                </div>

                <!-- Tanda Tangan & QR Validasi Keabsahan -->
                <div class="flex items-center justify-between border-t border-slate-200 pt-4 gap-4">
                    
                    <!-- Kotak Tanda Tangan Peserta -->
                    <div class="text-center w-1/2 border-r border-slate-200 pr-4">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tanda Tangan Digital</span>
                        <div class="h-16 flex items-center justify-center bg-slate-50/50 rounded-xl border border-slate-200/80 p-1">
                            @if ($ttdUrl)
                                <img src="{{ $ttdUrl }}" alt="Tanda Tangan" class="max-h-full max-w-full object-contain" />
                            @else

                                <div class="text-[11px] font-bold text-emerald-700 font-mono">✓ TERCATAT DIGITAL</div>
                            @endif
                        </div>
                        <div class="text-[10px] font-semibold text-slate-700 mt-1 uppercase">{{ $undangan->nama }}</div>
                    </div>

                    <!-- QR Code Verifikasi Dokumen -->
                    <div class="flex items-center gap-3 w-1/2 pl-2">
                        <div class="w-16 h-16 shrink-0 bg-white p-0.5 border border-slate-300 rounded-xl flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                            @if ($qrVerifikasiSvg)
                                {!! $qrVerifikasiSvg !!}
                            @else
                                <div class="text-[8px] font-mono">QR</div>
                            @endif
                        </div>
                        <div class="leading-tight">
                            <div class="text-[10px] font-bold uppercase text-slate-900">Verifikasi Resmi</div>
                            <div class="text-[8.5pt] text-slate-500 mt-0.5">Pindai untuk cek integritas dokumen rapat</div>
                            <div class="text-[8.5pt] font-mono font-bold text-emerald-700 mt-0.5">KODE: {{ $rapat->kode }}</div>
                        </div>
                    </div>

                </div>

                <!-- Stempel / Watermark Keabsahan Desa -->
                <div class="pt-3 border-t border-dashed border-slate-200 text-center text-[10px] text-slate-400">
                    Dokumen digital sah yang diakui dan tercatat di Sistem SIABDES Pemerintah Desa Belega.
                </div>

            </div>

        </div>

        <!-- Tombol Download Image Instan (No Print) -->
        <div class="no-print space-y-2">
            <button onclick="unduhKartuSebagaiGambar()" 
                    type="button" 
                    id="btn-unduh-gambar"
                    class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-xl shadow-emerald-950/30 flex items-center justify-center gap-2 transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Simpan / Unduh Kartu ke Galeri (PNG)</span>
            </button>
            <p class="text-[11px] text-center text-slate-400">
                Kartu ini dapat Anda simpan di ponsel sebagai arsip laporan pertanggungjawaban / SPPD.
            </p>
        </div>

    </div>

    <script>
        async function unduhKartuSebagaiGambar() {
            const btn = document.getElementById('btn-unduh-gambar');
            const target = document.getElementById('kartu-kehadiran-cetak');
            
            const h2c = window.html2canvas || (typeof html2canvas !== 'undefined' ? html2canvas : null);
            if (!target || !h2c) {
                if (window.SiabdesAlert) {
                    window.SiabdesAlert.fire({
                        icon: 'info',
                        title: 'Unduh Melalui Cetak / PDF',
                        text: 'Silakan gunakan tombol "Cetak" di atas untuk menyimpan kartu bukti hadir sebagai PDF atau cetak dokumen.',
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    alert('Silakan gunakan tombol Cetak di atas untuk menyimpan dokumen.');
                }
                return;
            }

            const teksAwal = btn.innerHTML;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Sedang Menyiapkan Gambar Kartu...</span>';
            btn.disabled = true;

            try {
                const canvas = await h2c(target, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                const dataUrl = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = `Kartu_Bukti_Hadir_{{ $rapat->kode }}_{{ \Illuminate\Support\Str::slug($undangan->nama) }}.png`;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                if (window.SiabdesAlert) {
                    window.SiabdesAlert.fire({
                        icon: 'success',
                        title: 'Kartu Berhasil Disimpan',
                        text: 'Kartu bukti kehadiran telah diunduh ke galeri / berkas perangkat Anda.',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            } catch (err) {
                console.error('Error saat membuat gambar:', err);
                if (window.SiabdesAlert) {
                    window.SiabdesAlert.fire({
                        icon: 'warning',
                        title: 'Gunakan Tombol Cetak / PDF',
                        text: 'Browser Anda membatasi penyimpanan canvas langsung. Anda dapat menekan tombol "Cetak" di atas untuk simpan PDF.',
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    alert('Gagal membuat gambar langsung. Silakan gunakan tombol Cetak untuk menyimpan PDF.');
                }
            } finally {
                btn.innerHTML = teksAwal;
                btn.disabled = false;
            }
        }
    </script>

</body>
</html>
