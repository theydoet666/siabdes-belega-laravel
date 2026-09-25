<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('rapat.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-tinta/60 hover:text-daun transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Rapat</span>
        </a>
    </div>

    <!-- Header Form -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-tinta">Buat Agenda Rapat Baru</h1>
        <p class="text-sm text-tinta/60 mt-1">Lengkapi informasi rapat, PIN meja kiosk absensi, dan data pejabat penandatangan lembar hadir</p>
    </div>

    <form wire:submit="simpan" class="space-y-8">
        <!-- Section 1: Informasi Rapat Utama -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold">1. Informasi Pokok Rapat</h2>
                    <p class="text-[11px] text-emerald-200/80">Agenda, jadwal pelaksanaan, dan lokasi musyawarah desa</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <!-- Judul Rapat -->
                <div>
                    <label for="judul" class="block text-xs font-semibold text-tinta mb-1.5">
                        Judul / Topik Agenda Rapat <span class="text-rose-600">*</span>
                    </label>
                    <input wire:model="judul" 
                           type="text" 
                           id="judul" 
                           required 
                           placeholder="contoh: Musyawarah Desa Pembahasan RKPDes 2027" 
                           class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('judul') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    @error('judul')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid Tanggal, Jam Mulai, Jam Selesai -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal" class="block text-xs font-semibold text-tinta mb-1.5">
                            Tanggal Pelaksanaan <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="tanggal" 
                               type="date" 
                               id="tanggal" 
                               required 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('tanggal') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('tanggal')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-xs font-semibold text-tinta mb-1.5">
                            Jam Mulai (WITA)
                        </label>
                        <input wire:model="jam_mulai" 
                               type="time" 
                               id="jam_mulai" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-xs font-semibold text-tinta mb-1.5">
                            Jam Selesai (WITA)
                        </label>
                        <input wire:model="jam_selesai" 
                               type="time" 
                               id="jam_selesai" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                    </div>
                </div>

                <!-- Grid Tempat & Penyelenggara -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tempat" class="block text-xs font-semibold text-tinta mb-1.5">
                            Tempat / Ruangan <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="tempat" 
                               type="text" 
                               id="tempat" 
                               required 
                               placeholder="Ruang Rapat Kantor Desa Belega" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('tempat') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('tempat')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="penyelenggara" class="block text-xs font-semibold text-tinta mb-1.5">
                            Penyelenggara / Lembaga <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="penyelenggara" 
                               type="text" 
                               id="penyelenggara" 
                               required 
                               placeholder="Pemerintah Desa Belega" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('penyelenggara') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('penyelenggara')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Catatan / Keterangan -->
                <div>
                    <label for="catatan" class="block text-xs font-semibold text-tinta mb-1.5">
                        Catatan Agenda (Opsional)
                    </label>
                    <textarea wire:model="catatan" 
                              id="catatan" 
                              rows="2" 
                              placeholder="Keterangan tambahan mengenai tata tertib atau perlengkapan rapat..."
                              class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition"></textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: Keamanan Kiosk & Retensi PDP -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center border border-white/20">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold">2. Keamanan Kiosk & Kepatuhan Privasi (UU PDP)</h2>
                    <p class="text-[11px] text-emerald-200/80">Kunci PIN tablet registrasi dan kebijakan masa simpan foto wajah</p>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- PIN Kiosk 6 Digit -->
                    <div>
                        <label for="pin_kiosk" class="block text-xs font-semibold text-tinta mb-1.5">
                            PIN Meja Kiosk (6 Digit) <span class="text-rose-600">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input wire:model="pin_kiosk" 
                                   type="text" 
                                   id="pin_kiosk" 
                                   maxlength="6"
                                   required 
                                   placeholder="123456" 
                                   class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('pin_kiosk') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm font-mono tracking-widest text-tinta focus:outline-none focus:ring-1 transition" />
                            <button wire:click="generatePinBaru" 
                                    type="button" 
                                    class="px-3 py-2.5 rounded-xl border border-garis hover:border-daun text-xs font-semibold text-tinta/80 hover:text-daun shrink-0 transition cursor-pointer"
                                    title="Acak PIN Baru">
                                Acak PIN
                            </button>
                        </div>
                        <p class="text-[11px] text-tinta/50 mt-1">Digunakan operator untuk membuka dan mengunci antarmuka absensi di tablet kiosk.</p>
                        @error('pin_kiosk')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Retensi Privasi Hari -->
                    <div>
                        <label for="retensi_hari" class="block text-xs font-semibold text-tinta mb-1.5">
                            Masa Retensi Foto Privasi (Hari) <span class="text-rose-600">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="retensi_hari" 
                                   type="number" 
                                   id="retensi_hari" 
                                   min="1" 
                                   max="365" 
                                   required 
                                   class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs text-tinta/50">
                                Hari
                            </div>
                        </div>
                        <p class="text-[11px] text-tinta/50 mt-1">Standar default 90 hari sesuai amanat UU No. 27/2022 PDP. Foto akan dihapus otomatis setelah masa ini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Konfigurasi Penandatangan Lembar Hadir (3 Pihak) -->
        <div class="bg-white rounded-2xl border border-garis shadow-sm overflow-hidden">
            <div class="bg-daun-tua px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center border border-white/20">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold">3. Pejabat Pengesah Lembar Hadir Resmi (Cetak A4)</h2>
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
                                <p class="text-[11px] text-tinta/60">Tanda tangan verifikasi administrasi (cth: Sekretaris Desa)</p>
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

        <!-- Tombol Aksi Submit -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('rapat.index') }}" 
               class="px-5 py-3 rounded-xl border border-garis text-sm font-medium text-tinta/80 hover:bg-kertas/70 transition">
                Batal
            </a>
            <button type="submit" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-daun hover:bg-daun-tua text-white text-sm font-semibold shadow-md shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                <span wire:loading.remove>Simpan & Lanjutkan ke Rapat</span>
                <span wire:loading class="inline-flex items-center gap-2">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan Agenda...
                </span>
            </button>
        </div>
    </form>
</div>
