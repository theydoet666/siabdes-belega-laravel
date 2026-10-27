<div>
    <!-- Notifikasi Flash -->
    @if ($pesanSukses)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    <!-- Breadcrumb & Navigasi Atas -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('rapat.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-tinta/60 hover:text-daun transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Rapat</span>
        </a>
    </div>

    <!-- Header Detail Rapat -->
    <div class="bg-white rounded-3xl border border-garis shadow-sm p-6 sm:p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <!-- Info Utama & Kode -->
            <div class="space-y-2 flex-1">
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Badge Kode Unik 4 Karakter -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-daun/10 text-daun border border-daun/20 font-mono font-bold text-xs sm:text-sm tracking-wider">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        KODE: {{ $rapat->kode }}
                    </span>

                    <!-- Status Siklus Hidup Rapat -->
                    @if ($rapat->status === \App\Enums\StatusRapat::DRAFT)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                            Draft (Belum Dibuka)
                        </span>
                    @elseif ($rapat->status === \App\Enums\StatusRapat::DIBUKA)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-daun-tua border border-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            Dibuka (Absensi Aktif)
                        </span>
                    @elseif ($rapat->status === \App\Enums\StatusRapat::DITUTUP)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                            Ditutup (Selesai)
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-tinta">{{ $rapat->judul }}</h1>

                <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-tinta/60 pt-1">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-tinta/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $rapat->tanggal?->translatedFormat('l, d F Y') }}
                    </span>
                    @if ($rapat->jam_mulai)
                        <span>·</span>
                        <span>{{ $rapat->jam_mulai }} @if($rapat->jam_selesai) - {{ $rapat->jam_selesai }} @endif WITA</span>
                    @endif
                    <span>·</span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-tinta/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $rapat->tempat }}
                    </span>
                </div>
            </div>

            <!-- Kontrol Transisi Status Rapat -->
            <div class="bg-kertas/80 p-3 rounded-2xl border border-garis flex flex-col sm:flex-row items-center gap-2">
                <span class="text-xs font-semibold text-tinta/70 px-2">Ubah Status:</span>
                <div class="inline-flex rounded-xl bg-white p-1 border border-garis shadow-xs">
                    <button wire:click="ubahStatus('draft')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $rapat->status === \App\Enums\StatusRapat::DRAFT ? 'bg-gray-800 text-white shadow-xs' : 'text-tinta/60 hover:text-tinta' }}">
                        Draft
                    </button>
                    <button wire:click="ubahStatus('dibuka')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $rapat->status === \App\Enums\StatusRapat::DIBUKA ? 'bg-daun text-white shadow-xs' : 'text-tinta/60 hover:text-tinta' }}">
                        Dibuka
                    </button>
                    <button wire:click="ubahStatus('ditutup')" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ $rapat->status === \App\Enums\StatusRapat::DITUTUP ? 'bg-slate-700 text-white shadow-xs' : 'text-tinta/60 hover:text-tinta' }}">
                        Ditutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Bilah Tombol Aksi Cepat Modul Rapat -->
        <div class="mt-8 pt-6 border-t border-garis flex flex-wrap items-center gap-3">
            <!-- Buka Mode Kiosk -->
            <a href="/kiosk/{{ $rapat->kode }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs sm:text-sm font-semibold shadow-sm shadow-daun/20 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Buka Meja Kiosk</span>
            </a>

            <!-- Tampilkan QR Code Mandiri -->
            <button wire:click="$dispatch('buka-panel-qr', { rapatId: '{{ $rapat->id }}' })" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-kertas border border-garis text-xs sm:text-sm font-semibold text-tinta transition cursor-pointer">
                <svg class="w-4 h-4 text-pena" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Tampilkan QR Mandiri</span>
            </button>

            <!-- Cetak Lembar A4 -->
            <a href="{{ route('rapat.cetak', $rapat->id) }}" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-kertas border border-garis text-xs sm:text-sm font-semibold text-tinta transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Lembar A4</span>
            </a>

            <!-- Ekspor Excel -->
            <a href="{{ route('rapat.ekspor-excel', $rapat->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-kertas border border-garis text-xs sm:text-sm font-semibold text-tinta transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Ekspor Excel</span>
            </a>

            <!-- Refresh Data -->
            <button wire:click="segarkanData" 
                    wire:loading.attr="disabled"
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-kertas border border-garis text-xs sm:text-sm font-semibold text-tinta transition cursor-pointer disabled:opacity-50"
                    title="Perbarui Data Undangan & Kehadiran">
                <svg class="w-4 h-4 text-emerald-600" wire:loading.class="animate-spin" wire:target="segarkanData" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span wire:loading.remove wire:target="segarkanData">Refresh Data</span>
                <span wire:loading wire:target="segarkanData">Memperbarui...</span>
            </button>

            <!-- Duplikasi Rapat -->
            <button wire:click="$dispatch('buka-modal-duplikasi', { rapatId: '{{ $rapat->id }}' })" 
                    type="button" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-kertas border border-garis text-xs sm:text-sm font-medium text-tinta transition cursor-pointer">
                <svg class="w-4 h-4 text-kuning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Duplikasi Rapat</span>
            </button>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Total Undangan</div>
            <div class="text-2xl font-bold text-tinta mt-1">{{ $rapat->undangan_count ?? 0 }} <span class="text-xs font-normal text-tinta/50">orang</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Total Hadir</div>
            <div class="text-2xl font-bold text-daun mt-1">{{ $rapat->kehadiran_count ?? 0 }} <span class="text-xs font-normal text-tinta/50">orang</span></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-garis shadow-xs">
            <div class="text-xs font-semibold text-tinta/60 uppercase tracking-wider">Persentase Kehadiran</div>
            <div class="text-2xl font-bold text-pena mt-1">
                @php
                    $persen = ($rapat->undangan_count > 0) ? round(($rapat->kehadiran_count / $rapat->undangan_count) * 100, 1) : 0;
                @endphp
                {{ $persen }}%
            </div>
        </div>
    </div>

    <!-- Navigasi Tab Detail Rapat -->
    <div class="border-b border-garis mb-6">
        <nav class="flex space-x-2 sm:space-x-4 -mb-px overflow-x-auto">
            <!-- Tab Undangan -->
            <button wire:click="$set('tabAktif', 'undangan')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'undangan' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Daftar Undangan ({{ $rapat->undangan_count ?? 0 }})</span>
            </button>

            <!-- Tab Konfigurasi Rapat -->
            <button wire:click="$set('tabAktif', 'konfigurasi')" 
                    type="button" 
                    class="py-3 px-4 font-semibold text-sm border-b-2 transition whitespace-nowrap cursor-pointer flex items-center gap-2 {{ $tabAktif === 'konfigurasi' ? 'border-daun text-daun' : 'border-transparent text-tinta/60 hover:text-tinta hover:border-garis' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Pengaturan & Pejabat Pengesah</span>
            </button>
        </nav>
    </div>

    <!-- Konten Tab 1: Undangan -->
    @if ($tabAktif === 'undangan')
        <livewire:undangan.tabel-undangan :rapatId="$rapat->id" :key="'tabel-undangan-'.$rapat->id" />
    @endif

    <!-- Konten Tab 2: Konfigurasi -->
    @if ($tabAktif === 'konfigurasi')
    <!-- Form Edit Konfigurasi Rapat -->
    <form wire:submit="simpanPerubahan" class="space-y-8">
        <!-- Informasi Rapat -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white">
                <h2 class="text-sm font-bold">Informasi Agenda Rapat</h2>
                <p class="text-[11px] text-emerald-200/80">Ubah judul, waktu, atau lokasi bila ada perubahan jadwal</p>
            </div>

            <div class="p-6 space-y-5">
                <div>
                    <label for="judul" class="block text-xs font-semibold text-tinta mb-1.5">
                        Judul Rapat <span class="text-rose-600">*</span>
                    </label>
                    <input wire:model="judul" type="text" id="judul" required class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('judul') border-rose-300 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    @error('judul') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal" class="block text-xs font-semibold text-tinta mb-1.5">
                            Tanggal <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="tanggal" type="date" id="tanggal" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                    <div>
                        <label for="jam_mulai" class="block text-xs font-semibold text-tinta mb-1.5">
                            Jam Mulai (WITA)
                        </label>
                        <input wire:model="jam_mulai" type="time" id="jam_mulai" class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                    <div>
                        <label for="jam_selesai" class="block text-xs font-semibold text-tinta mb-1.5">
                            Jam Selesai (WITA)
                        </label>
                        <input wire:model="jam_selesai" type="time" id="jam_selesai" class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tempat" class="block text-xs font-semibold text-tinta mb-1.5">
                            Tempat / Ruangan <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="tempat" type="text" id="tempat" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                    <div>
                        <label for="penyelenggara" class="block text-xs font-semibold text-tinta mb-1.5">
                            Penyelenggara <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="penyelenggara" type="text" id="penyelenggara" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                </div>

                <div>
                    <label for="catatan" class="block text-xs font-semibold text-tinta mb-1.5">
                        Catatan Agenda
                    </label>
                    <textarea wire:model="catatan" id="catatan" rows="2" class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition"></textarea>
                </div>
            </div>
        </div>

        <!-- Keamanan Kiosk & Retensi PDP -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white">
                <h2 class="text-sm font-bold">Keamanan Meja Kiosk & Retensi Privasi (UU PDP)</h2>
                <p class="text-[11px] text-emerald-200/80">Atur PIN kunci meja dan batas retensi foto</p>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="pin_kiosk_baru" class="block text-xs font-semibold text-tinta mb-1.5">
                            Ganti PIN Kiosk (6 Digit)
                        </label>
                        <input wire:model="pin_kiosk_baru" type="password" maxlength="6" id="pin_kiosk_baru" placeholder="Kosongkan jika tidak ingin mengubah PIN" class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('pin_kiosk_baru') border-rose-300 @else border-garis focus:border-daun @enderror rounded-xl text-sm font-mono tracking-widest text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('pin_kiosk_baru') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-tinta/50 mt-1">Isi 6 angka baru hanya jika ingin mengubah PIN tablet absensi.</p>
                    </div>

                    <div>
                        <label for="retensi_hari" class="block text-xs font-semibold text-tinta mb-1.5">
                            Masa Retensi Foto (Hari)
                        </label>
                        <input wire:model="retensi_hari" type="number" min="1" max="365" id="retensi_hari" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Penandatangan Lembar Hadir (3 Pihak) -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center border border-white/20">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold">Pejabat Pengesah Lembar Hadir Resmi (Cetak A4)</h2>
                        <p class="text-[11px] text-emerald-200/80">Pilih dan sesuaikan bagian tanda tangan yang akan dicetak pada laporan resmi</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- 1. Bagian Verifikator (Kiri Atas) -->
                <div class="p-5 rounded-2xl border {{ $verifikator_tampil ? 'border-daun/30 bg-emerald-50/30' : 'border-garis bg-kertas/30 opacity-75' }} transition">
                    <div class="flex items-center justify-between pb-3 border-b border-garis/60 mb-4">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="verifikator_tampil" class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-daun"></div>
                            </label>
                            <div>
                                <span class="text-xs font-bold text-tinta uppercase tracking-wide">1. Pihak Verifikasi (Kiri Atas)</span>
                                <p class="text-[11px] text-tinta/60">Tanda tangan verifikasi administrasi (cth: Telah dilakukan verifikasi / Sekdes)</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $verifikator_tampil ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $verifikator_tampil ? 'Ditampilkan' : 'Disembunyikan' }}
                        </span>
                    </div>

                    @if ($verifikator_tampil)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="verifikator_label" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Teks Pengantar Verifikasi
                                </label>
                                <input wire:model="verifikator_label" 
                                       type="text" 
                                       id="verifikator_label" 
                                       placeholder="Telah dilakukan verifikasi" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="verifikator_jabatan" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Jabatan Verifikator
                                </label>
                                <input wire:model="verifikator_jabatan" 
                                       type="text" 
                                       id="verifikator_jabatan" 
                                       placeholder="Sekretaris Desa" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="verifikator_nama" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Nama Lengkap & Gelar
                                </label>
                                <input wire:model="verifikator_nama" 
                                       type="text" 
                                       id="verifikator_nama" 
                                       placeholder="Gusti Ketut Amertayasa, S.M" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="verifikator_nip" class="block text-xs font-semibold text-tinta mb-1.5">
                                    NIP Verifikator (Opsional)
                                </label>
                                <input wire:model="verifikator_nip" 
                                       type="text" 
                                       id="verifikator_nip" 
                                       placeholder="Kosongkan jika tidak ada" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 2. Bagian Pelaksana Kegiatan (Kanan Atas) -->
                <div class="p-5 rounded-2xl border {{ $pelaksana_tampil ? 'border-daun/30 bg-emerald-50/30' : 'border-garis bg-kertas/30 opacity-75' }} transition">
                    <div class="flex items-center justify-between pb-3 border-b border-garis/60 mb-4">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="pelaksana_tampil" class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-daun"></div>
                            </label>
                            <div>
                                <span class="text-xs font-bold text-tinta uppercase tracking-wide">2. Pelaksana Kegiatan (Kanan Atas)</span>
                                <p class="text-[11px] text-tinta/60">Kasi / Kaur yang memiliki kegiatan beserta tanggal pengesahan</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $pelaksana_tampil ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $pelaksana_tampil ? 'Ditampilkan' : 'Disembunyikan' }}
                        </span>
                    </div>

                    @if ($pelaksana_tampil)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="pelaksana_jabatan" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Jabatan Pelaksana
                                </label>
                                <input wire:model="pelaksana_jabatan" 
                                       type="text" 
                                       id="pelaksana_jabatan" 
                                       placeholder="Kasi Pelayanan" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="pelaksana_nama" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Nama Lengkap & Gelar
                                </label>
                                <input wire:model="pelaksana_nama" 
                                       type="text" 
                                       id="pelaksana_nama" 
                                       placeholder="Ni Made Arini" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="pelaksana_nip" class="block text-xs font-semibold text-tinta mb-1.5">
                                    NIP Pelaksana (Opsional)
                                </label>
                                <input wire:model="pelaksana_nip" 
                                       type="text" 
                                       id="pelaksana_nip" 
                                       placeholder="Kosongkan jika tidak ada" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="penandatangan_lokasi" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Lokasi Kantor Pengesahan <span class="text-rose-600">*</span>
                                </label>
                                <input wire:model="penandatangan_lokasi" 
                                       type="text" 
                                       id="penandatangan_lokasi" 
                                       required 
                                       placeholder="Kantor Perbekel Desa Belega" 
                                       class="w-full px-3.5 py-2.5 bg-white border @error('penandatangan_lokasi') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 3. Bagian Mengetahui / Mengesahkan (Tengah Bawah) -->
                <div class="p-5 rounded-2xl border {{ $mengetahui_tampil ? 'border-daun/30 bg-emerald-50/30' : 'border-garis bg-kertas/30 opacity-75' }} transition">
                    <div class="flex items-center justify-between pb-3 border-b border-garis/60 mb-4">
                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="mengetahui_tampil" class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-daun"></div>
                            </label>
                            <div>
                                <span class="text-xs font-bold text-tinta uppercase tracking-wide">3. Pihak Mengetahui / Kepala Desa (Tengah Bawah)</span>
                                <p class="text-[11px] text-tinta/60">Tanda tangan mengetahui Perbekel / Plt. Perbekel</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md {{ $mengetahui_tampil ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $mengetahui_tampil ? 'Ditampilkan' : 'Disembunyikan' }}
                        </span>
                    </div>

                    @if ($mengetahui_tampil)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="mengetahui_label" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Teks Label Pengesahan
                                </label>
                                <input wire:model="mengetahui_label" 
                                       type="text" 
                                       id="mengetahui_label" 
                                       placeholder="Mengetahui :" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="penandatangan_jabatan" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Jabatan Kepala Desa / Pimpinan
                                </label>
                                <input wire:model="penandatangan_jabatan" 
                                       type="text" 
                                       id="penandatangan_jabatan" 
                                       placeholder="Plt. Perbekel Desa Belega" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="penandatangan_nama" class="block text-xs font-semibold text-tinta mb-1.5">
                                    Nama Lengkap & Gelar
                                </label>
                                <input wire:model="penandatangan_nama" 
                                       type="text" 
                                       id="penandatangan_nama" 
                                       placeholder="Gusti Ketut Amertayasa, S.M" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>

                            <div>
                                <label for="penandatangan_nip" class="block text-xs font-semibold text-tinta mb-1.5">
                                    NIP Pejabat (Opsional)
                                </label>
                                <input wire:model="penandatangan_nip" 
                                       type="text" 
                                       id="penandatangan_nip" 
                                       placeholder="Kosongkan jika tidak ada" 
                                       class="w-full px-3.5 py-2.5 bg-white border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tombol Simpan Perubahan -->
        <div class="flex items-center justify-end gap-3">
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-md shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                <span wire:loading.remove>Simpan Perubahan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>

    <!-- Danger Zone: Hapus Rapat (Khusus Administrator §4.2) -->
    @can('delete', $rapat)
        <div class="mt-12 bg-rose-50/50 rounded-2xl border border-rose-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-rose-900">Zona Berbahaya: Hapus Rapat Permanen</h3>
                    <p class="text-xs text-rose-700/80 mt-0.5">
                        Menghapus seluruh data rapat, relasi undangan, data kehadiran, serta seluruh file fisik tanda tangan & foto dari server storage secara permanen.
                    </p>
                </div>
                <div>
                    <button wire:click="konfirmasiHapus" 
                            type="button" 
                            class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                        Hapus Rapat Ini
                    </button>
                </div>
            </div>
        </div>
    @endcan
    @endif

    <!-- Modal Konfirmasi Hapus Rapat -->
    @if ($bukaModalHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center border border-rose-200">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-tinta">Hapus Rapat Permanen?</h3>
                <p class="text-xs text-tinta/70 mt-2">
                    Anda akan menghapus rapat <strong class="text-tinta font-bold">{{ $rapat->judul }}</strong> beserta seluruh berkas tanda tangan & foto kehadiran terkait. Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <button wire:click="batalHapus" type="button" class="px-4 py-2.5 rounded-xl border border-garis text-xs font-medium text-tinta/80 hover:bg-kertas transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="hapusRapat" wire:loading.attr="disabled" type="button" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove>Ya, Hapus Permanen</span>
                        <span wire:loading>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Sub-komponen Modal QR & Duplikasi -->
    <livewire:rapat.panel-qr />
    <livewire:rapat.modal-duplikasi-rapat />
</div>
