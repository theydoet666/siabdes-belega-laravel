<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir - {{ $rapat->judul }} - {{ $rapat->kode }}</title>
    
    @vite(['resources/css/app.css', 'resources/css/print.css', 'resources/js/app.js'])
    
    <style>
        /* Standar Margin Kertas A4: Tepat 2cm (20mm) di Semua Sisi */
        @page {
            size: A4 portrait;
            margin: 20mm;
        }

        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 11pt !important;
                line-height: 1.25 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .lembar-a4 {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            thead {
                display: table-header-group !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .blok-pengesahan {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .garis-kop-ganda {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 4px;
            margin: 10px 0 16px 0;
        }
    </style>
</head>
<body x-data="{ tampilQrVerifikasi: true }" class="preview-cetak-body bg-slate-700 font-serif">

    <!-- Floating Top Bar untuk Kontrol Preview Cetak (no-print) -->
    <header class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-800 text-white px-4 py-3 shadow-md mb-6">
        <div class="max-w-5xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('rapat.detail', $rapat->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-sans font-medium text-slate-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Detail</span>
                </a>
                <span class="text-xs font-sans text-slate-400">|</span>
                <span class="text-xs font-sans font-semibold text-slate-300">Preview Lembar Cetak A4 (Kode: <span class="font-mono text-emerald-400">{{ $rapat->kode }}</span>)</span>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Tombol Toggle QR Keabsahan Dokumen -->
                <button @click="tampilQrVerifikasi = !tampilQrVerifikasi" 
                        type="button" 
                        :class="tampilQrVerifikasi ? 'bg-emerald-800 text-white border-emerald-600 shadow-emerald-900/30' : 'bg-slate-800 text-slate-300 border-slate-700'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-sans font-semibold transition cursor-pointer shadow-sm"
                        title="Tampilkan atau sembunyikan QR Code verifikasi keabsahan dokumen">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>QR Keabsahan:</span>
                    <span class="font-bold uppercase text-[10px] px-1.5 py-0.5 rounded" :class="tampilQrVerifikasi ? 'bg-emerald-600 text-white' : 'bg-slate-700 text-slate-300'" x-text="tampilQrVerifikasi ? 'Aktif' : 'Nonaktif'"></span>
                </button>

                <a href="{{ route('rapat.ekspor-excel', $rapat->id) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-xs font-sans font-semibold text-white shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor Excel (.xlsx)</span>
                </a>

                <button onclick="window.print()" 
                        type="button" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-xs font-sans font-bold text-white shadow-sm transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Lembar A4 (Ctrl+P)</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Kontainer Lembar A4 -->
    <main class="lembar-a4">
        
        <!-- KOP DINAS RESMI -->
        <header class="text-center relative">
            <div class="flex items-center justify-between gap-5">
                <!-- Lambang / Logo Desa Belega / Kabupaten (Dibuat Lebih Besar & Proporsional) -->
                <div class="w-24 h-24 shrink-0 flex items-center justify-center">
                    @if ($pengaturan->logo_url)
                        <img src="{{ $pengaturan->logo_url }}" alt="Logo Instansi" style="max-height: 85px; max-width: 85px; object-fit: contain;" />
                    @else
                        <!-- Logo Garis Vektor Default Bali / Pancasila -->
                        <div class="w-20 h-20 border-2 border-black rounded-full flex flex-col items-center justify-center p-1 text-center">
                            <span class="text-[10px] font-bold uppercase leading-none">Desa</span>
                            <span class="text-[12px] font-black uppercase leading-none mt-1">Belega</span>
                        </div>
                    @endif
                </div>

                <!-- Teks Kop Dinas (Ukuran Lebih Besar & Berwibawa) -->
                <div class="flex-1 text-center">
                    <h3 class="font-bold uppercase tracking-wider text-black" style="font-size: 13pt; line-height: 1.25; letter-spacing: 0.5px;">
                        @if (str_starts_with(strtoupper($pengaturan->kabupaten ?? ''), 'PEMERINTAH'))
                            {{ strtoupper($pengaturan->kabupaten) }}
                        @else
                            PEMERINTAH {{ strtoupper($pengaturan->kabupaten ?: 'KABUPATEN GIANYAR') }}
                        @endif
                    </h3>
                    <h3 class="font-bold uppercase tracking-wider text-black" style="font-size: 13pt; line-height: 1.25; letter-spacing: 0.5px;">
                        {{ strtoupper($pengaturan->kecamatan ?: 'Kecamatan Blahbatuh') }}
                    </h3>
                    <h2 class="font-extrabold uppercase tracking-wide text-black mt-0.5 mb-1" style="font-size: 17pt; line-height: 1.25; letter-spacing: 1px;">
                        {{ strtoupper($pengaturan->nama_desa ?: 'Pemerintah Desa Belega') }}
                    </h2>
                    <p class="italic leading-tight text-neutral-800" style="font-size: 9.5pt;">
                        {{ $pengaturan->alamat_desa ?: 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581' }}
                    </p>
                </div>

                <!-- Spacer Kanan Penyeimbang -->
                <div class="w-24 h-24 shrink-0"></div>
            </div>

            <!-- Garis Ganda Pembatas Kop -->
            <div class="garis-kop-ganda"></div>
        </header>

        @php
            $hariInggris = $rapat->tanggal ? $rapat->tanggal->format('l') : date('l');
            $mapHari = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
            ];
            $mapBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $namaHariId = $mapHari[$hariInggris] ?? $hariInggris;
            $tglAngka = $rapat->tanggal ? $rapat->tanggal->format('j') : date('j');
            $blnAngka = $rapat->tanggal ? (int) $rapat->tanggal->format('n') : (int) date('n');
            $thnAngka = $rapat->tanggal ? $rapat->tanggal->format('Y') : date('Y');
            $namaBulanId = $mapBulan[$blnAngka] ?? date('F');
            $formatHariTanggal = "{$namaHariId}, {$tglAngka} {$namaBulanId} {$thnAngka}";
            
            // Format waktu rapi (contoh: 20.00 s/d 22.00 WITA)
            $jamMulaiFormatted = $rapat->jam_mulai ? date('H.i', strtotime($rapat->jam_mulai)) : null;
            $jamSelesaiFormatted = $rapat->jam_selesai ? date('H.i', strtotime($rapat->jam_selesai)) : null;
            
            if ($jamMulaiFormatted && $jamSelesaiFormatted) {
                $formatWaktu = "{$jamMulaiFormatted} s/d {$jamSelesaiFormatted} WITA";
            } elseif ($jamMulaiFormatted) {
                $formatWaktu = "{$jamMulaiFormatted} WITA s/d Selesai";
            } else {
                $formatWaktu = "-";
            }
        @endphp

        <!-- JUDUL & RINCIAN AGENDA RAPAT -->
        <section class="mb-4">
            <div class="text-center mb-4">
                <h1 class="text-base font-bold uppercase tracking-wider underline" style="font-size: 13pt;">DAFTAR HADIR</h1>
            </div>

            <table class="w-full text-[10pt] border-none mb-3 font-serif" style="border: none !important; border-collapse: collapse;">
                <tr style="border: none !important;">
                    <td style="border: none !important; padding: 2px 0; width: 120px; vertical-align: top;" class="font-bold">Acara</td>
                    <td style="border: none !important; padding: 2px 6px; width: 15px; vertical-align: top;" class="font-bold">:</td>
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;" class="font-bold">{{ $rapat->judul }}</td>
                </tr>
                <tr style="border: none !important;">
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;" class="font-bold">Hari, Tanggal</td>
                    <td style="border: none !important; padding: 2px 6px; vertical-align: top;" class="font-bold">:</td>
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;">{{ $formatHariTanggal }}</td>
                </tr>
                <tr style="border: none !important;">
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;" class="font-bold">Waktu</td>
                    <td style="border: none !important; padding: 2px 6px; vertical-align: top;" class="font-bold">:</td>
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;">{{ $formatWaktu }}</td>
                </tr>
                <tr style="border: none !important;">
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;" class="font-bold">Tempat</td>
                    <td style="border: none !important; padding: 2px 6px; vertical-align: top;" class="font-bold">:</td>
                    <td style="border: none !important; padding: 2px 0; vertical-align: top;">{{ $rapat->tempat }}</td>
                </tr>
            </table>
        </section>

        <!-- TABEL DAFTAR HADIR (Tanpa Kolom Waktu) -->
        <table class="w-full border-collapse border border-black text-[10pt] mb-4">
            <thead>
                <tr class="bg-gray-100 text-center font-bold">
                    <th style="border: 1px solid #000; padding: 6px 4px; width: 6%;">No.</th>
                    <th style="border: 1px solid #000; padding: 6px 6px; width: 34%;">Nama Lengkap</th>
                    <th style="border: 1px solid #000; padding: 6px 6px; width: 22%;">Jabatan</th>
                    <th style="border: 1px solid #000; padding: 6px 6px; width: 20%;">Instansi / Banjar</th>
                    <th style="border: 1px solid #000; padding: 6px 4px; width: 18%;">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarUndangan as $index => $undangan)
                    @php
                        $nomor = $index + 1;
                        $kehadiran = $undangan->kehadiran->first();
                        $hadir = ($kehadiran !== null && !$kehadiran->dibatalkan);
                        
                        // Bersihkan karakter aneh
                        $namaBersih = trim(preg_replace('/[*_~`]+/', '', $undangan->nama));
                        $jabatanBersih = trim(preg_replace('/[*_~`]+/', '', $undangan->jabatan ?? '-'));
                        $instansiBersih = trim(preg_replace('/[*_~`]+/', '', $undangan->instansi ?? '-'));
                    @endphp
                    <tr style="border: 1px solid #000;">
                        <td style="border: 1px solid #000; padding: 6px 4px; text-align: center; vertical-align: middle;">
                            {{ $nomor }}
                        </td>
                        <td style="border: 1px solid #000; padding: 6px 6px; vertical-align: middle;">
                            <div class="font-semibold">{{ $namaBersih }}</div>
                            @if ($hadir && !empty($kehadiran->diwakili_oleh))
                                <div class="text-[8pt] italic text-neutral-600">
                                    (Diwakili: {{ trim(preg_replace('/[*_~`]+/', '', $kehadiran->diwakili_oleh)) }})
                                </div>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 6px 6px; vertical-align: middle;">
                            {{ $jabatanBersih ?: '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 6px 6px; vertical-align: middle;">
                            {{ $instansiBersih ?: '-' }}
                        </td>
                        <td style="border: 1px solid #000; padding: 3px 4px; vertical-align: middle;">
                            @if ($hadir && $kehadiran->ttd_path)
                                <div class="flex items-center justify-center">
                                    <img src="{{ asset('storage/' . $kehadiran->ttd_path) }}" 
                                         alt="TTD" 
                                         style="max-height: 40px; max-width: 110px; object-fit: contain;" />
                                </div>
                            @elseif ($hadir)
                                <div class="text-center text-[8.5pt] font-bold text-emerald-800">
                                    ✓ HADIR
                                </div>
                            @else
                                <!-- Zig-zag kotak tanda tangan manual jika dicetak sebelum rapat -->
                                <div class="flex items-center h-8 px-1 {{ $nomor % 2 === 1 ? 'justify-start' : 'justify-end' }}">
                                    <span class="text-[8.5pt] text-neutral-400 font-mono">{{ $nomor }}. ........</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="border: 1px solid #000; padding: 12px; text-align: center;" class="italic text-neutral-500">
                            Belum ada daftar undangan / peserta untuk rapat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- BLOK PENGESAHAN 3 PIHAK SESUAI FORMAT RESMI DESA BELEGA -->
        <section class="blok-pengesahan text-[10pt] mt-6" style="page-break-inside: avoid; break-inside: avoid;">
            @php
                $adaVerifikator = $rapat->verifikator_tampil ?? true;
                $adaPelaksana = $rapat->pelaksana_tampil ?? true;
                $adaMengetahui = $rapat->mengetahui_tampil ?? true;
                $adaBarisAtas = $adaVerifikator || $adaPelaksana;
            @endphp

            @if ($adaBarisAtas)
                <div class="flex justify-between items-start mb-6">
                    <!-- 1. Pihak Verifikator (Kiri) -->
                    <div class="w-5/12 text-center">
                        @if ($adaVerifikator)
                            <div>{{ $rapat->verifikator_label ?: 'Telah dilakukan verifikasi' }}</div>
                            <div class="font-semibold">{{ $rapat->verifikator_jabatan ?: 'Sekretaris Desa' }}</div>
                            <div class="h-20"></div>
                            <div class="font-bold underline uppercase">
                                ({{ $rapat->verifikator_nama ?: 'Gusti Ketut Amertayasa, S.M' }})
                            </div>
                            @if (!empty($rapat->verifikator_nip))
                                <div class="text-[8.5pt]">NIP. {{ $rapat->verifikator_nip }}</div>
                            @endif
                        @endif
                    </div>

                    <!-- 2. Pihak Pelaksana Kegiatan (Kanan) -->
                    <div class="w-5/12 text-center">
                        @if ($adaPelaksana)
                            <div>{{ $rapat->penandatangan_lokasi ?: 'Kantor Perbekel Desa Belega' }}, {{ $tglAngka }} {{ $namaBulanId }} {{ $thnAngka }}</div>
                            <div class="font-semibold">{{ $rapat->pelaksana_jabatan ?: 'Kasi Pelayanan' }}</div>
                            <div class="h-20"></div>
                            <div class="font-bold underline uppercase">
                                ({{ $rapat->pelaksana_nama ?: 'Ni Made Arini' }})
                            </div>
                            @if (!empty($rapat->pelaksana_nip))
                                <div class="text-[8.5pt]">NIP. {{ $rapat->pelaksana_nip }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            @if ($adaMengetahui)
                <!-- 3. Pihak Mengetahui / Mengesahkan (Tengah Bawah) -->
                <div class="flex justify-center text-center">
                    <div class="w-5/12 text-center">
                        <div>{{ $rapat->mengetahui_label ?: 'Mengetahui :' }}</div>
                        <div class="font-semibold">{{ $rapat->penandatangan_jabatan ?: 'Perbekel Belega' }}</div>
                        <div class="h-20"></div>
                        <div class="font-bold underline uppercase">
                            ({{ $rapat->penandatangan_nama ?: 'I WAYAN SUDARSANA, S.Sos.' }})
                        </div>
                        @if (!empty($rapat->penandatangan_nip))
                            <div class="text-[8.5pt]">NIP. {{ $rapat->penandatangan_nip }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <!-- FOOTER RESMI & VERIFIKASI KEABSAHAN DOKUMEN -->
        <footer class="mt-8 pt-3 border-t border-black/40 text-[9pt]" style="break-inside: avoid; page-break-inside: avoid;">
            
            <!-- Blok QR Code Keabsahan Dokumen (Dapat Diaktifkan / Dinonaktifkan via Tombol Top Bar) -->
            <div x-show="tampilQrVerifikasi" class="flex items-center justify-between gap-4 pb-2.5">
                <!-- Sisi Kiri: QR Code & Deskripsi Verifikasi Resmi -->
                <div class="flex items-center gap-3">
                    <div class="w-13 h-13 shrink-0 bg-white p-0.5 border border-black/30 rounded flex items-center justify-center">
                        @if (!empty($qrVerifikasiSvg))
                            <div class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full">
                                {!! $qrVerifikasiSvg !!}
                            </div>
                        @else
                            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-[8px] font-mono">QR</div>
                        @endif
                    </div>
                    <div class="leading-tight font-sans">
                        <div class="font-bold text-black uppercase tracking-wide text-[9.5pt]">
                            VERIFIKASI DOKUMEN RESMI
                        </div>
                        <div class="text-[8pt] text-neutral-600 mt-0.5">
                            Pindai QR untuk verifikasi keabsahan daftar hadir
                        </div>
                        <div class="text-[8.5pt] font-mono font-bold text-black mt-0.5">
                            KODE : {{ $rapat->kode }}
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Waktu Cetak & Lokasi Kantor Resmi -->
                <div class="text-right leading-tight font-sans">
                    <div class="text-[8.5pt] text-neutral-700">
                        Dicetak: {{ $waktuCetakSingkat ?? $waktuCetak }}
                    </div>
                    <div class="font-bold text-black uppercase text-[9pt] mt-0.5">
                        {{ strtoupper($rapat->penandatangan_lokasi ?: ($pengaturan->nama_desa ?: 'KANTOR PERBEKEL DESA BELEGA')) }}
                    </div>
                </div>
            </div>

            <!-- Rekap Total Kehadiran Peserta -->
            <div class="flex items-center justify-between text-[8.5pt] text-neutral-600 italic pt-1.5 border-t border-dotted border-black/30">
                <div>
                    Total Kehadiran: <span class="font-bold font-sans not-italic text-black">{{ $totalHadir }}</span> dari <span class="font-bold font-sans not-italic text-black">{{ $totalUndangan }}</span> Peserta
                    @if ($totalUndangan > 0)
                        ({{ round(($totalHadir / $totalUndangan) * 100, 1) }}%)
                    @endif
                </div>
                <div x-show="!tampilQrVerifikasi">
                    Dicetak pada: {{ $waktuCetak }}
                </div>
            </div>
        </footer>

    </main>

</body>
</html>
