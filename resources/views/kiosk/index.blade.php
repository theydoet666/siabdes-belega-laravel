@php
    $pengaturanSistem = \App\Models\PengaturanSistem::first();
    $namaSistem = $pengaturanSistem->nama_sistem ?? 'SIABDES Belega';
    $logoUrl = $pengaturanSistem->logo_url ?? null;
    $urlRegistrasi = $urlRegistrasi ?? url('/r/' . $rapat->kode);
    $qrSvg = $qrSvg ?? \SimpleSoftwareIO\QrCode\Facades\QrCode::size(240)->margin(1)->generate($urlRegistrasi);
@endphp

<x-layouts.kiosk :rapat="$rapat">
    <div x-data="kioskApp({ rapat: {{ Js::from($rapat) }}, undanganAwal: {{ Js::from($undanganAwal) }}, jalur: '{{ request()->routeIs('registrasi.mandiri') ? 'mandiri' : 'kiosk' }}' })"
         class="min-h-screen flex flex-col justify-between">
        
        <!-- Header Kiosk Tablet -->
        <header class="bg-daun-tua text-white px-4 sm:px-8 py-4 shadow-md sticky top-0 z-40 border-b border-white/10">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <!-- Identitas Desa -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center font-bold text-white shadow-inner overflow-hidden">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $namaSistem }}" class="w-8 h-8 object-contain" />
                        @else
                            <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-base tracking-tight">{{ $namaSistem }}</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 uppercase font-semibold">Kiosk Mode</span>
                        </div>
                        <p class="text-xs text-emerald-200/80 line-clamp-1 max-w-sm sm:max-w-md">{{ $rapat->judul }}</p>
                    </div>
                </div>

                <!-- Status Jaringan & Tombol Aksi Header -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @include('komponen.status-jaringan')

                    <!-- Tombol Refresh Data -->
                    <button @click="refreshData" 
                            :disabled="sedangRefresh"
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-emerald-100 transition cursor-pointer disabled:opacity-60"
                            title="Perbarui & Sinkronkan Data Undangan">
                        <svg class="w-3.5 h-3.5 text-emerald-300" :class="{ 'animate-spin': sedangRefresh }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="hidden sm:inline" x-text="sedangRefresh ? 'Memperbarui...' : 'Refresh Data'">Refresh Data</span>
                    </button>

                    <!-- Tombol Kunci Meja -->
                    <form method="POST" action="{{ route('kiosk.kunci', ['kode' => $rapat->kode]) }}">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-semibold text-emerald-100 transition cursor-pointer"
                                title="Kunci Meja Kiosk">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span class="hidden sm:inline">Kunci Meja</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Workspace Kiosk -->
        <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">

            <!-- ================= LAYAR 1: CARI NAMA ================= -->
            <div x-show="layar === 'cari'" x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95" class="space-y-6">
                <!-- Header Cari -->
                <div class="text-center space-y-2">
                    <div class="inline-block px-3 py-1 rounded-full bg-daun/10 border border-daun/20 text-daun font-mono font-bold text-xs">
                        KODE RAPAT: {{ $rapat->kode }}
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-tinta tracking-tight">Selamat Datang</h1>
                    <p class="text-sm sm:text-base text-tinta/70 max-w-lg mx-auto">
                        Silakan ketik nama Anda untuk memulai absensi dan penandatanganan digital
                    </p>
                </div>

                <!-- Input Pencarian Cerdas (200ms Debounce) -->
                <div class="relative max-w-2xl mx-auto">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-daun">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input x-model.debounce.200ms="kueriCari" 
                               @input="cariNama"
                               type="text" 
                               autofocus
                               placeholder="Ketik nama lengkap atau instansi Anda..." 
                               class="w-full pl-14 pr-12 py-4 sm:py-5 bg-white border-2 border-garis focus:border-daun focus:ring-4 focus:ring-daun/15 rounded-3xl text-base sm:text-xl font-semibold text-tinta placeholder-tinta/30 shadow-lg shadow-daun/5 transition focus:outline-none min-h-12" />
                        <template x-if="kueriCari">
                            <button @click="kueriCari = ''; hasilCari = []" class="absolute inset-y-0 right-0 pr-5 flex items-center text-tinta/40 hover:text-tinta">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </template>
                    </div>

                    <!-- Rekomendasi Hasil Pencarian -->
                    <div x-show="hasilCari.length > 0" class="mt-3 bg-white rounded-3xl border-2 border-garis shadow-xl overflow-hidden divide-y divide-garis/60">
                        <template x-for="item in hasilCari" :key="item.id">
                            <button @click="pilihUndangan(item)" 
                                    :disabled="item.sudah_hadir"
                                    :class="item.sudah_hadir ? 'bg-slate-50/90 cursor-not-allowed opacity-75' : 'hover:bg-emerald-50/60 cursor-pointer'"
                                    class="w-full p-4 sm:p-5 text-left flex items-center justify-between transition min-h-12">
                                <div class="space-y-0.5">
                                    <div class="font-bold text-sm sm:text-base text-tinta" x-text="item.nama"></div>
                                    <div class="text-xs text-tinta/60">
                                        <span x-text="item.jabatan || 'Peserta Rapat'"></span>
                                        <template x-if="item.instansi">
                                            <span> · <span x-text="item.instansi"></span></span>
                                        </template>
                                    </div>
                                </div>
                                <div>
                                    <template x-if="item.sudah_hadir">
                                        <div class="flex items-center gap-1.5">
                                            <template x-if="item.kehadiran_id">
                                                <a :href="'/r/' + rapat.kode + '/bukti/' + item.kehadiran_id" 
                                                   target="_blank"

                                                   @click.stop
                                                   class="px-3 py-1.5 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition cursor-pointer"
                                                   title="Buka / Unduh Kartu Bukti Hadir Digital">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    <span>Lihat Bukti</span>
                                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </template>
                                            <template x-if="!item.kehadiran_id">
                                                <span class="px-3.5 py-1.5 rounded-xl bg-slate-200/80 text-tinta/70 text-xs font-bold inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-daun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Sudah Hadir</span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!item.sudah_hadir">
                                        <span class="px-4 py-2 rounded-xl bg-daun text-white text-xs font-bold shadow-xs">
                                            Pilih Saya →
                                        </span>
                                    </template>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Opsi Alternatif: Lihat Semua / QR Code / Tambahan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 max-w-2xl mx-auto">
                    <button @click="bukaModalDaftar" 
                            type="button" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-white hover:bg-kertas border-2 border-garis text-tinta font-semibold text-xs sm:text-sm shadow-xs transition min-h-12 cursor-pointer">
                        <svg class="w-4 h-4 text-pena shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>Daftar Undangan</span>
                    </button>

                    <button @click="bukaModalQr" 
                            type="button" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100/80 border-2 border-emerald-200 text-daun font-semibold text-xs sm:text-sm shadow-xs transition min-h-12 cursor-pointer">
                        <svg class="w-4 h-4 text-daun shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Scan QR Lewat HP</span>
                    </button>

                    <button @click="bukaFormTambahan" 
                            type="button" 
                            class="inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-amber-50 hover:bg-amber-100/80 border-2 border-amber-200 text-amber-900 font-semibold text-xs sm:text-sm shadow-xs transition min-h-12 cursor-pointer">
                        <svg class="w-4 h-4 text-kuning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>Belum Terdaftar?</span>
                    </button>
                </div>
            </div>


            <!-- ================= LAYAR 2: MODAL DAFTAR UNDANGAN ================= -->
            <div x-show="layar === 'modal_daftar'" x-cloak class="space-y-4">
                <div class="bg-white rounded-3xl border-2 border-garis shadow-xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-garis pb-4">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-tinta">Daftar Seluruh Undangan</h2>
                            <p class="text-xs text-tinta/60">Pilih nama Anda dari daftar berikut</p>
                        </div>
                        <button @click="pindahLayar('cari')" class="px-4 py-2 rounded-xl bg-kertas border border-garis text-xs font-semibold text-tinta/80 hover:bg-garis/50 transition">
                            ← Kembali
                        </button>
                    </div>

                    <!-- Search Modal -->
                    <div class="relative">
                        <input x-model="kueriModal" 
                               type="text" 
                               placeholder="Cari nama atau instansi..." 
                               class="w-full pl-10 pr-4 py-3 bg-kertas/50 border border-garis rounded-2xl text-sm text-tinta focus:outline-none focus:border-daun" />
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-tinta/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Grid Nama -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-1">
                        <template x-for="item in daftarLengkapPaginasi" :key="item.id">
                            <div :class="item.sudah_hadir ? 'bg-emerald-50/20 border-garis/80' : 'border-garis hover:border-daun hover:bg-emerald-50/40'"
                                 class="p-4 rounded-2xl border text-left transition flex items-center justify-between min-h-12 gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-sm text-tinta truncate" x-text="item.nama"></div>
                                    <div class="text-[11px] text-tinta/60 truncate" x-text="(item.jabatan || '') + (item.instansi ? ' · ' + item.instansi : '')"></div>
                                </div>
                                <div class="shrink-0 flex items-center gap-1.5">
                                    <template x-if="item.sudah_hadir">
                                        <div class="flex items-center gap-1.5">
                                            <template x-if="item.kehadiran_id">
                                                <a :href="'/r/' + rapat.kode + '/bukti/' + item.kehadiran_id" 
                                                   target="_blank"

                                                   @click.stop
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 text-xs font-bold transition shadow-xs cursor-pointer"
                                                   title="Buka / Unduh Kartu Bukti Hadir Digital">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    <span>Lihat Bukti</span>
                                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                    </svg>
                                                </a>
                                            </template>
                                            <template x-if="!item.kehadiran_id">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-daun border border-emerald-200 text-xs font-bold">
                                                    <svg class="w-3 h-3 text-daun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>Sudah Hadir</span>
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!item.sudah_hadir">
                                        <button @click="pilihUndangan(item)" 
                                                type="button"
                                                class="px-4 py-2 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-bold shadow-xs cursor-pointer transition">
                                            Pilih →
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Paginasi Modal -->
                    <div class="flex items-center justify-between pt-3 border-t border-garis text-xs text-tinta/70">
                        <span>Halaman <span x-text="halamanModal"></span> dari <span x-text="totalHalamanModal"></span></span>
                        <div class="flex items-center gap-2">
                            <button @click="if (halamanModal > 1) halamanModal--" :disabled="halamanModal <= 1" class="px-3 py-1.5 rounded-lg border border-garis disabled:opacity-40">Sebelumnya</button>
                            <button @click="if (halamanModal < totalHalamanModal) halamanModal++" :disabled="halamanModal >= totalHalamanModal" class="px-3 py-1.5 rounded-lg border border-garis disabled:opacity-40">Berikutnya</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- ================= LAYAR 3: FORM UNDANGAN TAMBAHAN ================= -->
            <div x-show="layar === 'form_tambahan'" x-cloak class="max-w-lg mx-auto w-full">
                <div class="bg-white rounded-3xl border-2 border-garis shadow-xl p-6 sm:p-8 space-y-5">
                    <div>
                        <div class="inline-block px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-kuning font-semibold text-xs mb-2">
                            Undangan Tambahan
                        </div>
                        <h2 class="text-xl font-bold text-tinta">Formulir Kehadiran Tambahan</h2>
                        <p class="text-xs text-tinta/60 mt-0.5">Lengkapi identitas Anda untuk mencatat kehadiran resmi</p>
                    </div>

                    <template x-if="errorFormTambahan">
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs" x-text="errorFormTambahan"></div>
                    </template>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-tinta mb-1">Nama Lengkap <span class="text-rose-600">*</span></label>
                            <input x-model="namaTambahan" type="text" placeholder="contoh: I Made Suardika" class="w-full px-4 py-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun min-h-12" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-tinta mb-1">Jabatan (Opsional)</label>
                            <input x-model="jabatanTambahan" type="text" placeholder="contoh: Tokoh Masyarakat" class="w-full px-4 py-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun min-h-12" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-tinta mb-1">Instansi / Banjar (Opsional)</label>
                            <input x-model="instansiTambahan" type="text" placeholder="contoh: Banjar Belega Kangin" class="w-full px-4 py-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun min-h-12" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-tinta mb-1">No. HP / WhatsApp (Opsional)</label>
                            <input x-model="hpTambahan" type="text" placeholder="08123456789" class="w-full px-4 py-3 bg-kertas/50 border border-garis rounded-xl text-sm text-tinta focus:outline-none focus:border-daun min-h-12" />
                        </div>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-garis">
                        <button @click="pindahLayar('cari')" type="button" class="px-5 py-3 rounded-xl border border-garis text-sm font-semibold text-tinta/80 hover:bg-kertas transition min-h-12">
                            Batal
                        </button>
                        <button @click="simpanFormTambahan" type="button" class="px-6 py-3 rounded-xl bg-daun hover:bg-daun-tua text-white font-semibold text-sm shadow-md shadow-daun/20 transition min-h-12">
                            Lanjut ke Tanda Tangan →
                        </button>
                    </div>
                </div>
            </div>


            <!-- ================= LAYAR 4: KANVAS TANDA TANGAN ================= -->
            <div x-show="layar === 'ttd'" x-cloak class="max-w-2xl mx-auto w-full space-y-4">
                <div x-data="tandaTanganPad()" class="bg-white rounded-3xl border-2 border-garis shadow-xl p-6 sm:p-8 space-y-5">
                    <!-- Identitas Peserta -->
                    <div class="border-b border-garis pb-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-daun">Langkah 1 dari 2</span>
                            <span class="text-[11px] text-tinta/60 bg-kertas/80 border border-garis px-2.5 py-0.5 rounded-full font-medium">Foto Wajah di Langkah 2 Bersifat Opsional</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-tinta mt-1">Tanda Tangan Kehadiran</h2>
                        <div class="text-sm text-tinta/70 mt-1">
                            Peserta: <strong class="text-tinta" x-text="undanganDipilih?.nama"></strong>
                            <template x-if="undanganDipilih?.instansi">
                                <span> (<span x-text="undanganDipilih?.instansi"></span>)</span>
                            </template>
                        </div>
                    </div>

                    <!-- Area Kanvas -->
                    <div class="space-y-2">
                        <div class="relative w-full h-64 sm:h-72 bg-kertas/50 rounded-2xl border-2 border-dashed border-garis overflow-hidden touch-none">
                            <canvas x-ref="signatureCanvas"
                                    @pointerdown="mulaiMenggambar"
                                    @pointermove="gambar"
                                    @pointerup="selesaiMenggambar"
                                    @pointercancel="selesaiMenggambar"
                                    class="w-full h-full cursor-crosshair block touch-none">
                            </canvas>

                            <!-- Panduan Placeholder jika kosong -->
                            <div x-show="!adaCoretan" class="absolute inset-0 flex items-center justify-center pointer-events-none text-tinta/25 text-sm font-medium">
                                Silakan goreskan tanda tangan Anda di dalam kotak ini
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-tinta/60 px-1">
                            <span>* Garis tanda tangan dinas otomatis diperhalus</span>
                            <div class="flex items-center gap-3">
                                <button @click="batalkan" type="button" class="text-tinta/60 hover:text-tinta underline cursor-pointer">Batalkan Coretan</button>
                                <button @click="bersihkan" type="button" class="text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">Hapus Semua</button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Tanda Tangan -->
                    <div class="pt-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-garis">
                        <button @click="pindahLayar('cari')" type="button" class="px-5 py-3 rounded-2xl border border-garis text-sm font-semibold text-tinta/80 hover:bg-kertas min-h-12 text-center">
                            ← Kembali
                        </button>
                        
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                            <!-- Tombol Langsung Simpan (Lewati Foto) -->
                            <button @click="lanjutDariTtd(eksporPng(), true)" 
                                    type="button" 
                                    class="px-5 py-3 rounded-2xl border-2 border-emerald-600/30 bg-emerald-50 hover:bg-emerald-100 text-daun font-semibold text-sm transition min-h-12 cursor-pointer flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-daun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Lewati Foto & Selesaikan</span>
                            </button>

                            <!-- Tombol Lanjut ke Foto -->
                            <button @click="lanjutDariTtd(eksporPng(), false)" 
                                    type="button" 
                                    class="px-6 py-3 rounded-2xl bg-daun hover:bg-daun-tua text-white font-semibold text-sm shadow-md shadow-daun/20 transition min-h-12 cursor-pointer flex items-center justify-center gap-2">
                                <span>Lanjut ke Foto Wajah</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- ================= LAYAR 5: PENGAMBILAN FOTO WAJAH (OPSIONAL) ================= -->
            <div x-show="layar === 'foto'" x-cloak class="max-w-lg mx-auto w-full">
                <div x-data="kameraWajah()" 
                     @siapkan-kamera-wajah.window="resetDanMulai()"
                     @hentikan-kamera-wajah.window="hentikanKamera()"
                     class="bg-white rounded-3xl border-2 border-garis shadow-xl p-6 sm:p-8 space-y-5 text-center relative overflow-hidden">
                    
                    <!-- Flash Effect Pulse -->
                    <div x-show="flashActive" class="absolute inset-0 bg-white z-50 transition-opacity duration-200 pointer-events-none"></div>

                    <!-- Header Foto -->
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-daun font-semibold text-[11px] mb-1.5">
                            <span>Langkah 2 dari 2</span>
                            <span>•</span>
                            <span class="font-bold">Opsional (Boleh Dilewatkan)</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold text-tinta mt-0.5">Foto Bukti Kehadiran</h2>
                        <p class="text-xs text-tinta/60 mt-1">Posisikan wajah Anda pada bingkai kamera, atau Anda dapat langsung melewatinya</p>
                    </div>

                    <!-- Area Kamera / Pratinjau Foto -->
                    <div class="relative w-full aspect-4/3 max-w-sm mx-auto bg-slate-900 rounded-3xl overflow-hidden border-2 border-garis flex items-center justify-center shadow-inner">
                        <!-- Video Kamera Live -->
                        <video x-ref="videoElement" 
                               x-show="!fotoBase64 && kameraAktif"
                               autoplay 
                               playsinline 
                               muted 
                               class="w-full h-full object-cover transform -scale-x-100">
                        </video>

                        <!-- Pratinjau Foto Hasil Jepretan -->
                        <template x-if="fotoBase64">
                            <img :src="fotoBase64" alt="Hasil Foto" class="w-full h-full object-cover" />
                        </template>

                        <!-- Overlay Bingkai Wajah -->
                        <div x-show="!fotoBase64 && kameraAktif" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-48 h-60 rounded-full border-2 border-dashed border-white/60 shadow-2xl flex items-center justify-center">
                                <span class="text-[10px] uppercase font-bold tracking-widest text-white/70 bg-black/40 px-2 py-0.5 rounded-full backdrop-blur-xs">
                                    Panduan Wajah
                                </span>
                            </div>
                        </div>

                        <!-- Overlay Countdown Timer (3s) -->
                        <div x-show="isCountingDown" class="absolute inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center z-30">
                            <div class="w-24 h-24 rounded-full bg-daun text-white flex items-center justify-center text-5xl font-extrabold shadow-2xl animate-pulse">
                                <span x-text="countdown"></span>
                            </div>
                        </div>

                        <!-- Pesan Error Kamera -->
                        <div x-show="pesanErrorKamera && !fotoBase64" class="p-4 text-center text-white text-xs space-y-2">
                            <svg class="w-8 h-8 mx-auto text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p x-text="pesanErrorKamera"></p>
                        </div>
                    </div>

                    <!-- Notifikasi Kepatuhan Privasi PDP -->
                    <p class="text-[10px] text-tinta/50 max-w-xs mx-auto">
                        Foto disimpan terenkripsi dan dihapus otomatis setelah masa retensi sesuai UU No. 27/2022 PDP.
                    </p>

                    <!-- Tombol Aksi Kamera -->
                    <div class="space-y-3 pt-2">
                        <!-- Mode Belum Ada Foto -->
                        <template x-if="!fotoBase64 && kameraAktif">
                            <div class="space-y-2.5">
                                <button @click="mulaiCountdown" 
                                        :disabled="isCountingDown"
                                        type="button" 
                                        class="w-full py-3.5 rounded-2xl bg-daun hover:bg-daun-tua text-white font-bold text-sm shadow-md shadow-daun/20 transition min-h-12 cursor-pointer flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Ambil Foto (Timer 3 Detik)</span>
                                </button>
                                
                                <!-- Tombol Lewati Foto yang Jelas & Terlihat -->
                                <button @click="hentikanKamera(); selesaikanCheckin(null)" 
                                        type="button" 
                                        class="w-full py-3.5 rounded-2xl border-2 border-garis bg-kertas/60 hover:bg-kertas text-tinta/80 hover:text-tinta font-semibold text-sm transition min-h-12 cursor-pointer flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-tinta/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                    <span>Lewati Pengambilan Foto (Selesaikan Kehadiran)</span>
                                </button>

                                <div class="pt-1">
                                    <button @click="hentikanKamera(); pindahLayar('ttd')" 
                                            type="button" 
                                            class="text-xs font-semibold text-tinta/60 hover:text-tinta cursor-pointer inline-flex items-center gap-1">
                                        <span>← Kembali ke Tanda Tangan</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Mode Foto Sudah Diambil -->
                        <template x-if="fotoBase64">
                            <div class="space-y-2.5">
                                <button @click="selesaikanCheckin(fotoBase64)" 
                                        type="button" 
                                        class="w-full py-3.5 rounded-2xl bg-daun hover:bg-daun-tua text-white font-bold text-sm shadow-md shadow-daun/20 transition min-h-12 cursor-pointer flex items-center justify-center gap-2">
                                    <span>Gunakan Foto Ini & Selesaikan</span>
                                    <span>✓</span>
                                </button>
                                
                                <div class="flex items-center justify-center gap-4 text-xs font-semibold pt-1">
                                    <button @click="fotoUlang" 
                                            type="button" 
                                            class="text-tinta/70 hover:text-rose-600 cursor-pointer">
                                        🔄 Foto Ulang
                                    </button>
                                    <span class="text-garis">|</span>
                                    <button @click="selesaikanCheckin(null)" 
                                            type="button" 
                                            class="text-tinta/70 hover:text-tinta cursor-pointer">
                                        Lewati (Simpan Tanpa Foto)
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Mode Kamera Gagal / Tidak Tersedia -->
                        <template x-if="!kameraAktif && !fotoBase64">
                            <div class="space-y-2">
                                <button @click="resetDanMulai()" 
                                        type="button" 
                                        class="w-full py-2.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-daun font-semibold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Aktifkan / Coba Kamera Lagi</span>
                                </button>
                                <button @click="selesaikanCheckin(null)" 
                                        type="button" 
                                        class="w-full py-3.5 rounded-2xl bg-daun hover:bg-daun-tua text-white font-bold text-sm shadow-md transition min-h-12 cursor-pointer">
                                    Selesaikan Absensi Tanpa Foto →
                                </button>
                                <button @click="hentikanKamera(); pindahLayar('ttd')" 
                                        type="button" 
                                        class="text-xs font-semibold text-tinta/60 hover:text-tinta cursor-pointer block mx-auto py-1">
                                    ← Kembali ke Tanda Tangan
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>


            <!-- ================= LAYAR 6: KARTU BUKTI KEHADIRAN ================= -->
            <div x-show="layar === 'bukti'" x-cloak class="space-y-6">
                <!-- Komponen Kartu Bukti Hadir -->
                @include('komponen.kartu-bukti-hadir')

                <!-- Countdown Auto-Reset & Tombol Selesai -->
                <div class="max-w-md mx-auto text-center space-y-3">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-garis shadow-xs text-xs font-semibold text-tinta/70">
                        <svg class="w-4 h-4 text-daun animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Kembali ke layar awal dalam <strong class="text-daun" x-text="timerSelesai"></strong> detik</span>
                    </div>

                    <button @click="resetKeLayarAwal" 
                            type="button" 
                            class="w-full py-4 rounded-2xl bg-daun hover:bg-daun-tua text-white font-bold text-base shadow-md shadow-daun/20 transition min-h-12 cursor-pointer">
                        Selesai (Peserta Berikutnya)
                    </button>
                </div>
            </div>

        </main>

        <!-- ================= MODAL QR CODE ABSENSI MANDIRI VIA HP ================= -->
        <div x-show="tampilModalQr" 
             x-cloak 
             @keydown.escape.window="tutupModalQr"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div @click.away="tutupModalQr" 
                 class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border-2 border-garis overflow-hidden">
                <!-- Header Modal QR -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">QR Absensi Mandiri</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Kode Rapat: <span class="font-mono font-bold tracking-wider text-amber-300">{{ $rapat->kode }}</span></p>
                        </div>
                    </div>
                    <button @click="tutupModalQr" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Konten Modal QR -->
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <h4 class="font-bold text-base text-tinta line-clamp-1">{{ $rapat->judul }}</h4>
                        <p class="text-xs text-tinta/60 mt-1">Arahkan kamera smartphone Anda ke QR Code di bawah untuk membuka absensi mandiri langsung di HP</p>
                    </div>

                    <!-- QR Code Frame -->
                    <div class="inline-block p-4 bg-white rounded-2xl border-2 border-dashed border-garis shadow-inner mb-5">
                        <div class="w-60 h-60 flex items-center justify-center mx-auto">
                            {!! $qrSvg !!}
                        </div>
                    </div>

                    <!-- Tautan URL & Salin -->
                    <div x-data="{ disalin: false }" class="space-y-3">
                        <div class="flex items-center gap-2 p-2 bg-kertas rounded-xl border border-garis">
                            <input type="text" 
                                   readonly 
                                   value="{{ $urlRegistrasi }}" 
                                   class="bg-transparent border-0 text-xs font-mono text-tinta/80 flex-1 px-2 focus:outline-none select-all" />
                            <button type="button" 
                                    @click="navigator.clipboard.writeText('{{ $urlRegistrasi }}'); disalin = true; setTimeout(() => disalin = false, 2500)"
                                    class="px-3 py-1.5 rounded-lg bg-daun hover:bg-daun-tua text-white text-xs font-semibold shrink-0 transition cursor-pointer">
                                <span x-show="!disalin">Salin Tautan</span>
                                <span x-show="disalin" x-cloak class="flex items-center gap-1 text-emerald-200">
                                    ✓ Tersalin
                                </span>
                            </button>
                        </div>

                        <div class="flex items-center justify-center gap-2 pt-2">
                            <button type="button" 
                                    @click="tutupModalQr" 
                                    class="w-full py-3 rounded-xl bg-daun hover:bg-daun-tua text-white font-semibold text-sm shadow-md shadow-daun/20 transition cursor-pointer">
                                Tutup QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Kiosk -->
        <footer class="py-3 text-center text-xs text-tinta/50 border-t border-garis/60 bg-white/50">
            <p>{{ $pengaturanSistem->nama_desa ?? 'Pemerintah Desa Belega' }} · {{ $namaSistem }} Kiosk Absensi Digital v2.0</p>
        </footer>
    </div>
</x-layouts.kiosk>
