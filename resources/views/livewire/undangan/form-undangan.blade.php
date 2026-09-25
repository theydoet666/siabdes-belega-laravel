<div>
    @if ($tampil && $rapat)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-tinta/60 backdrop-blur-xs">
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-garis overflow-hidden">
                <!-- Header Modal -->
                <div class="bg-daun-tua px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                            <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold leading-tight">Tambah Undangan Manual</h3>
                            <p class="text-xs text-emerald-200/80 mt-0.5">Rapat: <span class="font-semibold text-white">{{ $rapat->judul }}</span></p>
                        </div>
                    </div>
                    <button wire:click="tutup" type="button" class="text-white/70 hover:text-white cursor-pointer text-xl font-bold p-1">
                        ✕
                    </button>
                </div>

                <!-- Form Tambah Undangan -->
                <form wire:submit="simpan" class="p-6 space-y-4">
                    <!-- Nama -->
                    <div>
                        <label for="namaUndangan" class="block text-xs font-semibold text-tinta mb-1.5">
                            Nama Lengkap Undangan <span class="text-rose-600">*</span>
                        </label>
                        <input wire:model="nama" 
                               type="text" 
                               id="namaUndangan" 
                               required 
                               placeholder="contoh: I Made Suardika" 
                               class="w-full px-3.5 py-2.5 bg-kertas/50 border @error('nama') border-rose-300 focus:border-rose-500 @else border-garis focus:border-daun @enderror rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        @error('nama')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jabatan & Instansi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="jabatanUndangan" class="block text-xs font-semibold text-tinta mb-1.5">
                                Jabatan (Opsional)
                            </label>
                            <input wire:model="jabatan" 
                                   type="text" 
                                   id="jabatanUndangan" 
                                   placeholder="contoh: Tokoh Masyarakat" 
                                   class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        </div>

                        <div>
                            <label for="instansiUndangan" class="block text-xs font-semibold text-tinta mb-1.5">
                                Instansi / Banjar (Opsional)
                            </label>
                            <input wire:model="instansi" 
                                   type="text" 
                                   id="instansiUndangan" 
                                   placeholder="contoh: Banjar Belega Kangin" 
                                   class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        </div>
                    </div>

                    <!-- No HP & Sumber -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="hpUndangan" class="block text-xs font-semibold text-tinta mb-1.5">
                                No. HP / WhatsApp (Opsional)
                            </label>
                            <input wire:model="hp" 
                                   type="text" 
                                   id="hpUndangan" 
                                   placeholder="08123456789" 
                                   class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition" />
                        </div>

                        <div>
                            <label for="sumberUndangan" class="block text-xs font-semibold text-tinta mb-1.5">
                                Klasifikasi Sumber
                            </label>
                            <select wire:model="sumber" 
                                    id="sumberUndangan" 
                                    class="w-full px-3.5 py-2.5 bg-kertas/50 border border-garis focus:border-daun rounded-xl text-sm text-tinta focus:outline-none focus:ring-1 transition">
                                <option value="tambahan">Undangan Tambahan (Hadir Langsung)</option>
                                <option value="import">Daftar Undangan Resmi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="pt-3 flex items-center justify-end gap-3 border-t border-garis">
                        <button wire:click="tutup" 
                                type="button" 
                                class="px-4 py-2.5 rounded-xl border border-garis text-xs font-medium text-tinta/80 hover:bg-kertas transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-daun hover:bg-daun-tua text-white text-xs font-semibold shadow-sm shadow-daun/20 focus:outline-none focus:ring-2 focus:ring-daun focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>Simpan Undangan</span>
                            <span wire:loading>Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
