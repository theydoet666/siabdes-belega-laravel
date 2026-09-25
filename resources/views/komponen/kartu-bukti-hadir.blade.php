<!-- Komponen Kartu Bukti Hadir Resmi (§5.4 & §9 PRD) -->
<div class="w-full max-w-md mx-auto bg-white rounded-3xl shadow-xl border-2 border-garis overflow-hidden relative selection:bg-none">
    <!-- Header Kartu Bukti -->
    <div class="bg-daun-tua p-6 text-white text-center relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-white/5 pointer-events-none"></div>
        <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20">
            <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-xs font-bold uppercase tracking-widest text-emerald-200">Kartu Bukti Kehadiran Resmi</h3>
        <h2 class="text-base font-bold text-white mt-1 line-clamp-1" x-text="dataBuktiHadir?.rapat_judul || rapat.judul"></h2>
        <p class="text-[11px] text-emerald-100/70 mt-0.5">Pemerintah Desa Belega · Kec. Blahbatuh, Gianyar</p>
    </div>

    <!-- Konten Kartu -->
    <div class="p-6 space-y-5 relative">
        <!-- Cap Stempel Dinas Berputar -9 Derajat (§5.4 PRD) -->
        <div class="absolute right-4 bottom-14 pointer-events-none select-none opacity-90 z-20" style="transform: rotate(-9deg);">
            <div class="w-28 h-28 rounded-full border-4 border-dashed border-pena flex flex-col items-center justify-center p-2 text-center bg-white/40 backdrop-blur-xs shadow-sm">
                <span class="text-[9px] font-extrabold uppercase tracking-wider text-pena leading-tight">PEMERINTAH DESA</span>
                <span class="text-xs font-black uppercase text-pena my-0.5">★ BELEGA ★</span>
                <span class="text-[10px] font-black uppercase px-2 py-0.5 bg-pena text-white rounded-sm mt-0.5 tracking-wider">TERCATAT</span>
            </div>
        </div>

        <!-- Info Peserta -->
        <div class="space-y-3 pb-3 border-b border-garis/60">
            <div>
                <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider">Nama Peserta Rapat</span>
                <div class="text-lg font-bold text-tinta mt-0.5 leading-snug" x-text="dataBuktiHadir?.nama"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider">Jabatan</span>
                    <div class="font-semibold text-tinta/80 mt-0.5" x-text="dataBuktiHadir?.jabatan || '-'"></div>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider">Instansi / Lembaga</span>
                    <div class="font-semibold text-tinta/80 mt-0.5" x-text="dataBuktiHadir?.instansi || '-'"></div>
                </div>
            </div>
        </div>

        <!-- Waktu Kehadiran WITA & Sumber -->
        <div class="grid grid-cols-2 gap-3 text-xs pb-3 border-b border-garis/60">
            <div>
                <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider">Waktu Registrasi</span>
                <div class="font-bold text-daun mt-0.5 font-mono text-sm" x-text="dataBuktiHadir?.waktu_wita"></div>
            </div>
            <div>
                <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider">Jalur Registrasi</span>
                <div class="mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-daun border border-emerald-200 uppercase">
                        Meja Kiosk
                    </span>
                </div>
            </div>
        </div>

        <!-- Pratinjau Tanda Tangan & Foto -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider block mb-1">Tanda Tangan</span>
                <div class="h-20 bg-kertas rounded-xl border border-garis p-1.5 flex items-center justify-center overflow-hidden">
                    <img :src="dataBuktiHadir?.ttd" alt="Tanda Tangan Digital" class="max-h-full max-w-full object-contain" />
                </div>
            </div>
            <div>
                <span class="text-[10px] font-bold text-tinta/40 uppercase tracking-wider block mb-1">Foto Bukti</span>
                <div class="h-20 bg-kertas rounded-xl border border-garis p-1 flex items-center justify-center overflow-hidden">
                    <template x-if="dataBuktiHadir?.foto">
                        <img :src="dataBuktiHadir?.foto" alt="Foto Kehadiran" class="h-full w-full object-cover rounded-lg" />
                    </template>
                    <template x-if="!dataBuktiHadir?.foto">
                        <span class="text-[10px] text-tinta/40 text-center px-1">Tanpa Foto</span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Footer Pengesahan Kiosk -->
        <div class="pt-2 text-center text-[10px] text-tinta/40 font-mono">
            ID: <span x-text="dataBuktiHadir?.id"></span>
        </div>
    </div>
</div>
