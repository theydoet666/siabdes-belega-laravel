<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi & Pelindungan Data Pribadi - SIABDES Belega</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-kertas text-tinta font-sans min-h-screen flex flex-col justify-between selection:bg-daun selection:text-white antialiased">

    <!-- Header Sederhana -->
    <header class="bg-white border-b border-garis sticky top-0 z-40 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-daun-tua text-white flex items-center justify-center font-bold text-sm shadow-xs group-hover:scale-105 transition">
                    SB
                </div>
                <div>
                    <div class="font-bold text-base text-tinta leading-tight">SIABDES Belega</div>
                    <div class="text-[11px] text-tinta/60">Pemerintah Desa Belega, Gianyar</div>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl border border-garis hover:border-daun text-xs font-semibold text-tinta/80 hover:text-daun transition">
                    Masuk Operator
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Utama Kebijakan Privasi -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10 flex-1">
        <!-- Banner Judul -->
        <div class="bg-white rounded-3xl border border-garis shadow-sm p-6 sm:p-10 mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-daun-tua text-xs font-semibold mb-4 border border-emerald-200">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Kepatuhan UU No. 27 Tahun 2022</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-tinta tracking-tight">
                Kebijakan Privasi & Pelindungan Data Pribadi
            </h1>
            <p class="text-sm text-tinta/70 mt-2 leading-relaxed">
                Pemerintah Desa Belega berkomitmen penuh dalam melindungi kerahasiaan dan keamanan data pribadi setiap peserta rapat dinas, tokoh masyarakat, dan warga sesuai amanat Undang-Undang Pelindungan Data Pribadi (UU PDP).
            </p>
            <div class="mt-4 pt-4 border-t border-garis/60 flex items-center gap-4 text-xs text-tinta/50">
                <span>Terakhir diperbarui: 24 September 2026</span>
                <span>·</span>
                <span>Versi Regulasi: 1.0 (UU PDP)</span>
            </div>
        </div>

        <!-- Rincian Klausul Kebijakan Privasi -->
        <div class="space-y-6 text-sm text-tinta/80 leading-relaxed">
            
            <!-- Pasal 1 -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">1</span>
                    Dasar Hukum dan Kedudukan Pengendali Data
                </h2>
                <p>
                    Pemerintah Desa Belega, Kecamatan Blahbatuh, Kabupaten Gianyar bertindak sebagai <strong>Pengendali Data Pribadi</strong> dalam penyelenggaraan Sistem Informasi Absensi Digital Desa (SIABDES). Pemrosesan data kehadiran diselenggarakan berdasarkan ketentuan:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-tinta/70">
                    <li>Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).</li>
                    <li>Undang-Undang Republik Indonesia Nomor 6 Tahun 2014 tentang Desa beserta perubahannya.</li>
                    <li>Peraturan Desa dan Keputusan Perbekel Belega tentang Tata Kelola Administrasi dan Rapat Kedinasan.</li>
                </ul>
            </section>

            <!-- Pasal 2 -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">2</span>
                    Data Pribadi yang Dikumpulkan & Diproses
                </h2>
                <p>
                    Dalam rangka presensi dan pencatatan daftar hadir resmi rapat desa, data pribadi yang dikumpulkan terbatas pada:
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                    <div class="p-3.5 rounded-xl bg-kertas/60 border border-garis/60">
                        <div class="font-bold text-xs text-daun mb-1">A. Data Identitas Umum</div>
                        <ul class="text-xs text-tinta/70 space-y-1 list-disc pl-4">
                            <li>Nama Lengkap</li>
                            <li>Jabatan / Kedudukan</li>
                            <li>Instansi, Banjar Dinas, atau Lembaga Desa</li>
                            <li>Nomor Telepon / WhatsApp (opsional)</li>
                        </ul>
                    </div>
                    <div class="p-3.5 rounded-xl bg-kertas/60 border border-garis/60">
                        <div class="font-bold text-xs text-pena mb-1">B. Data Verifikasi Kehadiran</div>
                        <ul class="text-xs text-tinta/70 space-y-1 list-disc pl-4">
                            <li>Tanda Tangan Digital (vektor PNG transparan)</li>
                            <li>Foto Wajah Bukti Kehadiran (opsional saat registrasi)</li>
                            <li>Stempel Waktu Kehadiran (WITA)</li>
                            <li>Keterangan Perwakilan (bila hadir mewakili)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Pasal 3 -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">3</span>
                    Tujuan Pemrosesan Data Pribadi
                </h2>
                <p>
                    Data pribadi yang dikumpulkan hanya digunakan untuk keperluan resmi kedinasan dan akuntabilitas publik Pemerintah Desa Belega, mencakup:
                </p>
                <ol class="list-decimal pl-5 mt-2 space-y-1.5 text-tinta/70">
                    <li>Verifikasi keabsahan kehadiran peserta pada Musyawarah Desa (Musdes), Musrenbangdes, Rapat Koordinasi, dan Rapat RT/RW/Banjar.</li>
                    <li>Penyusunan Lampiran Resmi Berita Acara dan Laporan Pertanggungjawaban Realisasi Anggaran Pendapatan dan Belanja Desa (APBDes).</li>
                    <li>Pemberian hak peserta (uang transport/kehormatan dinas bila dialokasikan secara sah sesuai APBDes).</li>
                    <li>Pemenuhan kewajiban audit oleh Inspektorat Daerah dan Badan Pemeriksa Keuangan (BPK).</li>
                </ol>
            </section>

            <!-- Pasal 4 -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">4</span>
                    Masa Retensi & Penghapusan Otomatis Foto Kehadiran
                </h2>
                <p>
                    Untuk meminimalkan risiko penyimpanan data visual (minimasi data), sistem SIABDES menerapkan kebijakan retensi otomatis:
                </p>
                <div class="mt-3 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong class="font-bold">Penghapusan Foto Otomatis 90 Hari:</strong>
                        <p class="mt-1">
                            Seluruh file foto wajah bukti kehadiran yang tersimpan akan <strong>dihapus secara permanen dari server penyimpanan</strong> secara otomatis setelah 90 hari kalender sejak tanggal pelaksanaan rapat, tanpa mempengaruhi rekap daftar hadir teks dan tanda tangan digital.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Pasal 5 -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">5</span>
                    Hak-Hak Subjek Data Pribadi
                </h2>
                <p>
                    Sesuai Bab IV UU No. 27 Tahun 2022 tentang Hak Subjek Data Pribadi, setiap peserta rapat berhak untuk:
                </p>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-tinta/70">
                    <li>Mendapatkan kejelasan informasi mengenai tujuan pemrosesan data identitasnya.</li>
                    <li>Meminta perbaikan data nama, jabatan, atau instansi apabila terdapat kekeliruan penulisan pada daftar hadir.</li>
                    <li>Memperoleh salinan bukti kehadiran pribadi melalui Kartu Bukti Hadir digital.</li>
                    <li>Mengajukan keberatan atau pengaduan atas dugaan penyalahgunaan data kepada Pejabat Pengelola Informasi dan Dokumentasi (PPID) Desa.</li>
                </ul>
            </section>

            <!-- Bagian Kontak & Catatan Peninjauan -->
            <section class="bg-white rounded-2xl border border-garis p-6 shadow-xs">
                <h2 class="text-base font-bold text-tinta mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-daun/10 text-daun flex items-center justify-center text-xs font-bold font-mono">6</span>
                    Kontak Layanan & Pejabat Pengelola Data
                </h2>
                <p class="text-tinta/70 mb-3">
                    Bila Anda memiliki pertanyaan, permohonan hak subjek data, atau keberatan terkait privasi pada sistem SIABDES Belega, silakan menghubungi:
                </p>
                <div class="p-4 rounded-xl bg-kertas/70 border border-garis text-xs space-y-1">
                    <div><strong>Instansi:</strong> Kantor Perbekel Desa Belega</div>
                    <div><strong>Alamat:</strong> Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581</div>
                    <div><strong>Layanan PPID:</strong> ppid@belega.desa.id</div>
                    <div><strong>Telepon / Pengaduan:</strong> (0361) 943-XXX / Pos Pelayanan Desa Belega</div>
                </div>

                <!-- Catatan Peninjauan Pemilik Produk -->
                <div class="mt-4 p-3 rounded-lg bg-slate-50 border border-slate-200 text-[11px] text-slate-600">
                    <span class="font-bold text-slate-800">Catatan Peninjauan untuk Pemerintah Desa Belega:</span>
                    <p class="mt-0.5">
                        Kontak email dan nomor telepon PPID di atas merupakan format standar tata kelola dan dapat diperbarui melalui Panel Pengaturan Identitas Sistem oleh Administrator Desa.
                    </p>
                </div>
            </section>

        </div>

        <!-- Tombol Aksi -->
        <div class="mt-8 flex items-center justify-between">
            <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-daun hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Halaman Utama</span>
            </a>

            <button onclick="window.print()" type="button" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-kertas border border-garis text-xs font-semibold text-tinta transition cursor-pointer">
                <svg class="w-4 h-4 text-tinta/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Kebijakan Privasi</span>
            </button>
        </div>
    </main>

    <!-- Footer Resmi -->
    <footer class="bg-white border-t border-garis py-6 mt-12 text-center text-xs text-tinta/60">
        <div class="max-w-4xl mx-auto px-4">
            <p>© 2026 Pemerintah Desa Belega. Sistem Informasi Absensi Digital Desa (SIABDES).</p>
            <p class="text-[11px] text-tinta/40 mt-1">Kecamatan Blahbatuh, Kabupaten Gianyar, Provinsi Bali</p>
        </div>
    </footer>

</body>
</html>
