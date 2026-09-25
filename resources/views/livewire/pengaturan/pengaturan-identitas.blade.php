<div>
    <!-- Flash Notifikasi -->
    @if ($pesanSukses)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $pesanSukses }}</span>
            </div>
            <button wire:click="$set('pesanSukses', null)" class="text-emerald-700 hover:text-emerald-900 cursor-pointer font-bold text-xs">✕</button>
        </div>
    @endif

    <form wire:submit="simpan" class="space-y-6">
        <!-- Pengaturan Identitas Teks -->
        <div class="bg-white rounded-2xl border border-garis shadow-xs p-6 space-y-6">
            <div>
                <h3 class="text-base font-bold text-tinta">Identitas Aplikasi & Instansi Desa</h3>
                <p class="text-xs text-tinta/60 mt-0.5">Informasi ini otomatis digunakan pada Kop Dinas Lembar Cetak A4, Header, dan Kebijakan Privasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Nama Sistem <span class="text-rose-600">*</span></label>
                    <input wire:model="nama_sistem" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('nama_sistem') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Subjudul / Slogan Sistem <span class="text-rose-600">*</span></label>
                    <input wire:model="subjudul_sistem" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('subjudul_sistem') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Nama Pemerintahan Desa <span class="text-rose-600">*</span></label>
                    <input wire:model="nama_desa" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('nama_desa') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Kecamatan <span class="text-rose-600">*</span></label>
                    <input wire:model="kecamatan" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('kecamatan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Kabupaten <span class="text-rose-600">*</span></label>
                    <input wire:model="kabupaten" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('kabupaten') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-tinta mb-1.5">Provinsi <span class="text-rose-600">*</span></label>
                    <input wire:model="provinsi" type="text" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta" />
                    @error('provinsi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-tinta mb-1.5">Alamat Lengkap Kantor Desa <span class="text-rose-600">*</span></label>
                <textarea wire:model="alamat_desa" rows="2" required class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta"></textarea>
                @error('alamat_desa') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Pengaturan Aset Visual: Logo & Favicon -->
        <div class="bg-white rounded-2xl border border-garis shadow-xs p-6 space-y-6">
            <div>
                <h3 class="text-base font-bold text-tinta">Logo Dinas & Favicon</h3>
                <p class="text-xs text-tinta/60 mt-0.5">Logo akan dicetak pada Kop Surat Lembar A4 dan ditampilkan pada bilah header aplikasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Upload Logo -->
                <div class="p-4 rounded-xl bg-kertas/50 border border-garis/80 space-y-3">
                    <label class="block text-xs font-bold text-tinta">Logo Instansi Desa (PNG/JPG, Maks. 2MB)</label>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl bg-white border border-garis flex items-center justify-center p-2 overflow-hidden shrink-0">
                            @if ($this->logoPreview)
                                <img src="{{ $this->logoPreview }}" alt="Preview Logo Baru" class="max-h-16 max-w-16 object-contain" />
                            @elseif ($logoBaru)
                                <div class="text-[11px] font-bold text-daun text-center leading-tight">Berkas Logo Dipilih</div>
                            @elseif ($logo_url)
                                <img src="{{ $logo_url }}" alt="Logo Saat Ini" class="max-h-16 max-w-16 object-contain" />
                            @else
                                <div class="w-12 h-12 rounded-full border border-black flex flex-col items-center justify-center text-[8px] font-bold text-tinta text-center leading-none">
                                    <span>DESA</span>
                                    <span>BELEGA</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-2">
                            <input wire:model="logoBaru" type="file" accept="image/*" class="text-xs text-tinta file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-daun file:text-white hover:file:bg-daun-tua cursor-pointer" />
                            @if ($logo_url)
                                <div>
                                    <button wire:click="hapusLogo" 
                                            wire:confirm="Yakin ingin menghapus logo kustom dan mereset ke logo default desa?"
                                            type="button" 
                                            class="text-[11px] text-rose-600 hover:underline cursor-pointer">
                                        Hapus Logo (Reset ke Default)
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('logoBaru') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <!-- Upload Favicon -->
                <div class="p-4 rounded-xl bg-kertas/50 border border-garis/80 space-y-3">
                    <label class="block text-xs font-bold text-tinta">Favicon Browser (PNG/ICO, Maks. 1MB)</label>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white border border-garis flex items-center justify-center p-1 overflow-hidden shrink-0">
                            @if ($this->faviconPreview)
                                <img src="{{ $this->faviconPreview }}" alt="Preview Favicon Baru" class="max-h-8 max-w-8 object-contain" />
                            @elseif ($faviconBaru)
                                <div class="w-8 h-8 rounded-lg bg-daun/15 text-daun flex items-center justify-center font-bold text-[10px] uppercase">
                                    ICO
                                </div>
                            @elseif ($favicon_url)
                                <img src="{{ $favicon_url }}" alt="Favicon Saat Ini" class="max-h-8 max-w-8 object-contain" />
                            @else
                                <div class="w-8 h-8 rounded-lg bg-daun text-white flex items-center justify-center font-bold text-xs">
                                    SB
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-2">
                            <input wire:model="faviconBaru" type="file" accept="image/*,.ico" class="text-xs text-tinta file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-daun file:text-white hover:file:bg-daun-tua cursor-pointer" />
                            @if ($favicon_url)
                                <div>
                                    <button wire:click="hapusFavicon" 
                                            wire:confirm="Yakin ingin menghapus favicon kustom dan mereset ke default?"
                                            type="button" 
                                            class="text-[11px] text-rose-600 hover:underline cursor-pointer">
                                        Hapus Favicon (Reset)
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @error('faviconBaru') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-garis flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-xs font-semibold text-white shadow-xs transition cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Pengaturan Identitas & Logo</span>
                </button>
            </div>
        </div>
    </form>
</div>
